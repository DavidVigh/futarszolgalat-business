<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Termek extends Model
{
    public const TIPUSOK = ['etel', 'ital'];

    public const FOGASOK = ['leves', 'eloetel', 'foetel', 'koret', 'salata', 'teszta', 'pizza', 'burger', 'edesseg', 'hideg_ital', 'meleg_ital'];

    protected $table = 'termek';

    public $timestamps = false;

    protected $fillable = ['tipus', 'nev', 'fogas', 'leiras', 'ar', 'elkeszitesi_ido', 'prioritas'];

    protected function casts(): array
    {
        return ['ar' => 'integer', 'elkeszitesi_ido' => 'integer', 'prioritas' => 'integer'];
    }

    public function allergenek(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'termek_allergen', 'termek_id', 'allergen_kod');
    }

    public function rendelesek(): BelongsToMany
    {
        return $this->belongsToMany(Rendeles::class, 'rendeles_tetel', 'termek_id', 'rendeles_id')
            ->withPivot('db');
    }
}
