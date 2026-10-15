<?php

namespace App\Models;

use Database\Factories\FutarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Futar extends Model
{
    /** @use HasFactory<FutarFactory> */
    use HasFactory;

    /** There are no drone deliveries. */
    public const JARMUVEK = ['auto', 'bicikli', 'motor'];

    public const ALLAPOT_ELERHETO = 'elerheto';

    public const ALLAPOT_UTON = 'uton';

    public const ALLAPOT_SZUNET = 'szunet';

    protected $table = 'futar';

    /** Couriers are deactivated instead of deleted: orders keep them (ON DELETE RESTRICT). */
    protected $attributes = ['aktiv' => true];

    public $timestamps = false;

    protected $fillable = ['nev', 'telszam', 'jarmu', 'allapot', 'hatosugar_km', 'aktiv'];

    protected function casts(): array
    {
        return ['hatosugar_km' => 'integer', 'aktiv' => 'boolean'];
    }

    public function rendelesek(): HasMany
    {
        return $this->hasMany(Rendeles::class, 'futar_id');
    }

    public function aktivRendeles(): HasOne
    {
        return $this->hasOne(Rendeles::class, 'futar_id')->whereIn('statusz', Rendeles::AKTIV_STATUSZOK);
    }
}
