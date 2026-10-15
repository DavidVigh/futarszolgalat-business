<?php

namespace App\Services;

use App\Exceptions\UzletiSzabalyException;
use App\Models\Rendeles;
use App\Models\RendelesTetel;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RendelesService
{
    /**
     * Create an order, its items and its billing details in one transaction (BL 5, 11).
     * A tip can only be given after delivery (DB: ck_rendeles_jatt), see jattot().
     *
     * @param  array{cim?: string|null, km: int, prioritas?: bool, fizetesi_mod: string, szamla?: bool, szamlazasi_adat?: array{nev?: string, cim?: string, adoszam?: string|null}, tetelek: list<array{termek_id: int, db: int}>}  $adatok
     */
    public function letrehoz(User $felhasznalo, array $adatok): Rendeles
    {
        return DB::transaction(function () use ($felhasznalo, $adatok): Rendeles {
            $rendeles = Rendeles::create([
                'felhasznalo_id' => $felhasznalo->id,
                'futar_id' => null,
                'cim' => $adatok['cim'] ?? $felhasznalo->cim,
                'prioritas' => $adatok['prioritas'] ?? false,
                'fizetesi_mod' => $adatok['fizetesi_mod'],
                'jatt' => null,
                'szamla' => $adatok['szamla'] ?? false,
                'km' => $adatok['km'],
                'statusz' => Rendeles::STATUSZ_UJ,
            ]);

            // The same product added twice becomes one item with the summed quantity (BL 6).
            $mennyisegek = collect($adatok['tetelek'])
                ->groupBy('termek_id')
                ->map(fn ($tetelek) => $tetelek->sum('db'));

            $termekek = Termek::query()->aktivak()->whereKey($mennyisegek->keys())->get()->keyBy('id');
            if ($termekek->count() !== $mennyisegek->count()) {
                throw new UzletiSzabalyException('Inaktiv vagy nem letezo termek nem rendelheto.');
            }

            foreach ($mennyisegek as $termekId => $db) {
                RendelesTetel::create([
                    'rendeles_id' => $rendeles->id,
                    'termek_id' => $termekId,
                    'db' => $db,
                    'egysegar' => $termekek[$termekId]->ar,
                ]);
            }

            if ($rendeles->szamla) {
                // Billing name and address default to the customer's own
                $rendeles->szamlazasiAdat()->create([
                    'nev' => $adatok['szamlazasi_adat']['nev'] ?? $felhasznalo->nev,
                    'cim' => $adatok['szamlazasi_adat']['cim'] ?? $felhasznalo->cim,
                    'adoszam' => $adatok['szamlazasi_adat']['adoszam'] ?? null,
                ]);
            }

            return $rendeles->fresh(['tetelek.termek', 'szamlazasiAdat']);
        });
    }

    /** Add a product to an order; an existing item's quantity grows instead (BL 6). */
    public function tetelHozzaad(Rendeles $rendeles, int $termekId, int $db): Rendeles
    {
        return DB::transaction(function () use ($rendeles, $termekId, $db): Rendeles {
            $this->ujStatuszuKell($rendeles, 'A tetelek csak uj statuszban modosithatok.');

            $termek = Termek::query()->aktivak()->find($termekId);
            if ($termek === null) {
                throw new UzletiSzabalyException('Inaktiv vagy nem letezo termek nem rendelheto.');
            }

            $meglevo = $this->tetelQuery($rendeles, $termekId);
            if ($meglevo->exists()) {
                $meglevo->increment('db', $db);
            } else {
                RendelesTetel::create([
                    'rendeles_id' => $rendeles->id,
                    'termek_id' => $termek->id,
                    'db' => $db,
                    'egysegar' => $termek->ar,
                ]);
            }

            return $rendeles->fresh(['tetelek.termek']);
        });
    }

    public function tetelModosit(Rendeles $rendeles, int $termekId, int $db): Rendeles
    {
        return DB::transaction(function () use ($rendeles, $termekId, $db): Rendeles {
            $this->ujStatuszuKell($rendeles, 'A tetelek csak uj statuszban modosithatok.');

            $modositott = $this->tetelQuery($rendeles, $termekId)->update(['db' => $db]);
            if ($modositott === 0) {
                throw new UzletiSzabalyException('A termek nem szerepel a rendelesben.');
            }

            return $rendeles->fresh(['tetelek.termek']);
        });
    }

    /** The last item cannot be removed; the order has to be cancelled instead (BL 6). */
    public function tetelTorol(Rendeles $rendeles, int $termekId): Rendeles
    {
        return DB::transaction(function () use ($rendeles, $termekId): Rendeles {
            $this->ujStatuszuKell($rendeles, 'A tetelek csak uj statuszban modosithatok.');

            if (! $this->tetelQuery($rendeles, $termekId)->exists()) {
                throw new UzletiSzabalyException('A termek nem szerepel a rendelesben.');
            }
            if ($rendeles->tetelek()->count() === 1) {
                throw new UzletiSzabalyException('A rendeles utolso tetele nem torolheto, helyette mondd le a rendelest.');
            }

            $this->tetelQuery($rendeles, $termekId)->delete();

            return $rendeles->fresh(['tetelek.termek']);
        });
    }

    /** Only a new order can be cancelled (BL 5, 10). */
    public function lemond(Rendeles $rendeles): Rendeles
    {
        $modositott = Rendeles::query()
            ->whereKey($rendeles->id)
            ->where('statusz', Rendeles::STATUSZ_UJ)
            ->update(['statusz' => Rendeles::STATUSZ_LEMONDVA]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException('Csak uj statuszu rendeles mondhato le.');
        }

        return $rendeles->fresh();
    }

    /** A delivered order can be rated once (BL 5). */
    public function ertekel(Rendeles $rendeles, int $ertekeles): Rendeles
    {
        $modositott = Rendeles::query()
            ->whereKey($rendeles->id)
            ->where('statusz', Rendeles::STATUSZ_KISZALLITVA)
            ->whereNull('ertekeles')
            ->update(['ertekeles' => $ertekeles]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException('Csak kiszallitott, meg nem ertekelt rendeles ertekelheto.');
        }

        return $rendeles->fresh();
    }

    /** A delivered, card-paid order can get one tip (BL 5, DB: ck_rendeles_jatt). */
    public function jattot(Rendeles $rendeles, int $jatt): Rendeles
    {
        $modositott = Rendeles::query()
            ->whereKey($rendeles->id)
            ->where('statusz', Rendeles::STATUSZ_KISZALLITVA)
            ->where('fizetesi_mod', 'kartya')
            ->whereNull('jatt')
            ->update(['jatt' => $jatt]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException('Jatt csak kiszallitott, kartyaval fizetett rendeleshez adhato, egyszer.');
        }

        return $rendeles->fresh();
    }

    /** The kitchen starts preparing an order a courier has accepted: elfogadva -> készül. */
    public function keszul(Rendeles $rendeles): Rendeles
    {
        $modositott = Rendeles::query()
            ->whereKey($rendeles->id)
            ->where('statusz', Rendeles::STATUSZ_ELFOGADVA)
            ->update(['statusz' => Rendeles::STATUSZ_KESZUL]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException('Csak elfogadott rendeles keszitheto.');
        }

        return $rendeles->fresh();
    }

    private function ujStatuszuKell(Rendeles $rendeles, string $uzenet): void
    {
        $statusz = Rendeles::query()->whereKey($rendeles->id)->value('statusz');
        if ($statusz !== Rendeles::STATUSZ_UJ) {
            throw new UzletiSzabalyException($uzenet);
        }
    }

    /** @return Builder<RendelesTetel> */
    private function tetelQuery(Rendeles $rendeles, int $termekId)
    {
        return RendelesTetel::query()->where('rendeles_id', $rendeles->id)->where('termek_id', $termekId);
    }
}
