<?php

namespace App\Models;

use Database\Factories\RendelesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rendeles extends Model
{
    /** @use HasFactory<RendelesFactory> */
    use HasFactory;

    public const STATUSZ_UJ = 'új';

    public const STATUSZ_FELVEVE = 'felvéve';

    public const STATUSZ_UTON = 'úton';

    public const STATUSZ_KISZALLITVA = 'kiszállítva';

    public const STATUSZ_LEMONDVA = 'lemondva';

    /** Statuses in which the order is being handled by a courier. */
    public const AKTIV_STATUSZOK = [self::STATUSZ_FELVEVE, self::STATUSZ_UTON];

    public const FIZETESI_MODOK = ['keszpenz', 'kartya'];

    protected $table = 'rendeles';

    public $timestamps = false;

    protected $fillable = ['felhasznalo_id', 'futar_id', 'cim', 'prioritas', 'fizetesi_mod', 'jatt', 'szamla', 'km', 'statusz', 'ertekeles'];

    protected function casts(): array
    {
        return [
            'felhasznalo_id' => 'integer',
            'futar_id' => 'integer',
            'prioritas' => 'boolean',
            'szamla' => 'boolean',
            'jatt' => 'integer',
            'km' => 'integer',
            'ertekeles' => 'integer',
            'letrehozva' => 'datetime',
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

    public function termekek(): BelongsToMany
    {
        return $this->belongsToMany(Termek::class, 'rendeles_tetel', 'rendeles_id', 'termek_id')
            ->withPivot('db', 'egysegar');
    }
}
