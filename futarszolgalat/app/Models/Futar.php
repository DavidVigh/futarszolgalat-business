<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Futar extends Model
{
    public const JARMUVEK = ['auto', 'dron', 'bicikli', 'motor'];

    public const ALLAPOTOK = ['elerheto', 'uton', 'szunet'];

    protected $table = 'futar';

    public $timestamps = false;

    protected $fillable = ['nev', 'telszam', 'jarmu', 'ember', 'allapot', 'hatosugar_km'];

    protected function casts(): array
    {
        return ['ember' => 'boolean', 'hatosugar_km' => 'integer'];
    }

    public function poziciok(): HasMany
    {
        return $this->hasMany(FutarPozicio::class, 'futarID');
    }

    public function utolsoPozicio(): HasOne
    {
        return $this->hasOne(FutarPozicio::class, 'futarID')->latestOfMany();
    }

    public function rendelesek(): HasMany
    {
        return $this->hasMany(Rendeles::class, 'futar_id');
    }
}
