<?php

namespace App\Models;

use Database\Factories\RendelesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rendeles extends Model
{
    /** @use HasFactory<RendelesFactory> */
    use HasFactory;

    /** Not yet placed; the API does not create carts, but the DB allows them. */
    public const STATUSZ_KOSAR = 'kosar';

    public const STATUSZ_UJ = 'új';

    /** A courier picked up the order. */
    public const STATUSZ_ELFOGADVA = 'elfogadva';

    /** The kitchen is preparing the order. */
    public const STATUSZ_KESZUL = 'készül';

    public const STATUSZ_UTON = 'úton';

    public const STATUSZ_KISZALLITVA = 'kiszállítva';

    public const STATUSZ_LEMONDVA = 'lemondva';

    /** Every value allowed by the rendeles.statusz CHECK constraint. */
    public const STATUSZOK = [
        self::STATUSZ_KOSAR, self::STATUSZ_UJ, self::STATUSZ_ELFOGADVA, self::STATUSZ_KESZUL,
        self::STATUSZ_UTON, self::STATUSZ_KISZALLITVA, self::STATUSZ_LEMONDVA,
    ];

    /** Statuses in which the order is being handled by a courier. */
    public const AKTIV_STATUSZOK = [self::STATUSZ_ELFOGADVA, self::STATUSZ_KESZUL, self::STATUSZ_UTON];

    public const FIZETESI_MODOK = ['keszpenz', 'kartya'];

    protected $table = 'rendeles';

    public $timestamps = false;

    protected $fillable = ['felhasznalo_id', 'futar_id', 'cim', 'prioritas', 'fizetesi_mod', 'jatt', 'szamla', 'km', 'szallitasi_dij', 'statusz', 'ertekeles'];

    protected function casts(): array
    {
        return [
            'felhasznalo_id' => 'integer',
            'futar_id' => 'integer',
            'prioritas' => 'boolean',
            'szamla' => 'boolean',
            'jatt' => 'integer',
            'km' => 'integer',
            'szallitasi_dij' => 'integer',
            'ertekeles' => 'integer',
            'letrehozva' => 'datetime',
            'kiszallitva_ido' => 'datetime',
        ];
    }

    public function felhasznalo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'felhasznalo_id');
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'futar_id');
    }

    public function tetelek(): HasMany
    {
        return $this->hasMany(RendelesTetel::class, 'rendeles_id');
    }

    public function szamlazasiAdat(): HasOne
    {
        return $this->hasOne(SzamlazasiAdat::class, 'rendeles_id');
    }

    public function termekek(): BelongsToMany
    {
        return $this->belongsToMany(Termek::class, 'rendeles_tetel', 'rendeles_id', 'termek_id')
            ->withPivot('db', 'egysegar');
    }
}
