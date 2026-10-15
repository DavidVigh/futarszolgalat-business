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

    public const JARMUVEK = ['auto', 'dron', 'bicikli', 'motor'];

    public const ALLAPOT_ELERHETO = 'elerheto';

    public const ALLAPOT_UTON = 'uton';

    public const ALLAPOT_SZUNET = 'szunet';

    protected $table = 'futar';

    public $timestamps = false;

    protected $fillable = ['nev', 'telszam', 'jarmu', 'ember', 'allapot', 'hatosugar_km'];

    protected function casts(): array
    {
        return ['ember' => 'boolean', 'hatosugar_km' => 'integer'];
    }

    public function isDron(): bool
    {
        return $this->jarmu === 'dron';
    }

    public function poziciok(): HasMany
    {
        return $this->hasMany(FutarPozicio::class, 'futarID');
    }

    /** The courier's current position is always the most recently recorded one. */
    public function aktualisPozicio(): HasOne
    {
        return $this->hasOne(FutarPozicio::class, 'futarID')->ofMany(['rogzitve' => 'max', 'id' => 'max']);
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
