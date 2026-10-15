<?php

namespace App\Services;

use App\Exceptions\UzletiSzabalyException;
use App\Models\Futar;
use App\Models\FutarPozicio;
use App\Models\Rendeles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FutarService
{
    /**
     * New orders the courier may pick up, priority orders first (BL 3).
     *
     * @return Builder<Rendeles>
     */
    public function felveheto(Futar $futar): Builder
    {
        return Rendeles::query()
            ->where('statusz', Rendeles::STATUSZ_UJ)
            ->whereNull('futar_id')
            ->where('km', '<=', $futar->hatosugar_km)
            ->when($futar->isDron(), fn (Builder $query) => $query->where('fizetesi_mod', '!=', 'keszpenz'))
            ->orderByDesc('prioritas')
            ->orderBy('letrehozva')
            ->orderBy('id');
    }

    /**
     * Pick up an order (BL 3, 11): the courier check, the conditional order UPDATE
     * and the courier status change run in one transaction.
     */
    public function felvesz(Futar $futar, Rendeles $rendeles): Rendeles
    {
        return DB::transaction(function () use ($futar, $rendeles): Rendeles {
            $futar->refresh();
            $rendeles->refresh();

            if ($rendeles->km > $futar->hatosugar_km) {
                throw new UzletiSzabalyException('A rendeles a futar hatosugaran kivul esik.');
            }
            if ($futar->isDron() && $rendeles->fizetesi_mod === 'keszpenz') {
                throw new UzletiSzabalyException('Dron nem vihet keszpenzes rendelest.');
            }

            // Only an available courier can pick up, and this also guarantees one active order per courier.
            $futarValtozott = Futar::query()
                ->whereKey($futar->id)
                ->where('allapot', Futar::ALLAPOT_ELERHETO)
                ->update(['allapot' => Futar::ALLAPOT_UTON]);
            if ($futarValtozott === 0) {
                throw new UzletiSzabalyException('Csak elerheto, aktiv rendeles nelkuli futar vehet fel rendelest.');
            }

            // One conditional UPDATE so two couriers can never pick up the same order.
            $rendelesValtozott = Rendeles::query()
                ->whereKey($rendeles->id)
                ->whereNull('futar_id')
                ->where('statusz', Rendeles::STATUSZ_UJ)
                ->update(['futar_id' => $futar->id, 'statusz' => Rendeles::STATUSZ_FELVEVE]);
            if ($rendelesValtozott === 0) {
                throw new UzletiSzabalyException('A rendelest mar felvettek, vagy nem uj statuszu.');
            }

            return $rendeles->fresh();
        });
    }

    /** felvéve -> úton (BL 10). */
    public function atvesz(Futar $futar, Rendeles $rendeles): Rendeles
    {
        $modositott = Rendeles::query()
            ->whereKey($rendeles->id)
            ->where('futar_id', $futar->id)
            ->where('statusz', Rendeles::STATUSZ_FELVEVE)
            ->update(['statusz' => Rendeles::STATUSZ_UTON]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException('Csak a futarhoz rendelt, felvett rendeles veheto at.');
        }

        return $rendeles->fresh();
    }

    /** úton -> kiszállítva, and the courier becomes available again, in one transaction (BL 11). */
    public function kiszallit(Futar $futar, Rendeles $rendeles): Rendeles
    {
        return DB::transaction(function () use ($futar, $rendeles): Rendeles {
            $modositott = Rendeles::query()
                ->whereKey($rendeles->id)
                ->where('futar_id', $futar->id)
                ->where('statusz', Rendeles::STATUSZ_UTON)
                ->update(['statusz' => Rendeles::STATUSZ_KISZALLITVA]);

            if ($modositott === 0) {
                throw new UzletiSzabalyException('Csak a futarhoz rendelt, uton levo rendeles szallithato ki.');
            }

            Futar::query()->whereKey($futar->id)->update(['allapot' => Futar::ALLAPOT_ELERHETO]);

            return $rendeles->fresh();
        });
    }

    /**
     * Manual status changes: elerheto <-> szunet. The uton status is only set by
     * picking up and cleared by delivering an order (BL 10).
     */
    public function allapotValt(Futar $futar, string $ujAllapot): Futar
    {
        $honnan = match ($ujAllapot) {
            Futar::ALLAPOT_SZUNET => Futar::ALLAPOT_ELERHETO,
            Futar::ALLAPOT_ELERHETO => Futar::ALLAPOT_SZUNET,
            default => throw new UzletiSzabalyException('A futar allapota kezzel csak elerheto vagy szunet lehet.'),
        };

        $modositott = Futar::query()
            ->whereKey($futar->id)
            ->where('allapot', $honnan)
            ->update(['allapot' => $ujAllapot]);

        if ($modositott === 0) {
            throw new UzletiSzabalyException("Ez az allapotvaltas nem engedelyezett (csak {$honnan} -> {$ujAllapot}).");
        }

        return $futar->fresh();
    }

    /**
     * Only a courier on the road records positions (BL 4).
     *
     * @param  array{gps_x: float, gps_y: float, cim: string}  $adatok
     */
    public function pozicioRogzit(Futar $futar, array $adatok): FutarPozicio
    {
        if ($futar->fresh()->allapot !== Futar::ALLAPOT_UTON) {
            throw new UzletiSzabalyException('A poziciot csak uton levo futar frissitheti.');
        }

        return $futar->poziciok()->create($adatok)->fresh();
    }
}
