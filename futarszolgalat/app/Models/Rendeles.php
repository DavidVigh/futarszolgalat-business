<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendeles extends Model
{
    protected $table = 'rendeles';

    public $timestamps = false;

    protected $fillable = ['felhasznalo_id', 'futar_id', 'cim', 'prioritas', 'fizetesi_mod', 'jatt', 'szamla', 'km', 'statusz', 'ertekeles'];

    protected function casts(): array
    {
        return [
            'prioritas' => 'integer',
            'jatt' => 'float',
            'km' => 'float',
            'ertekeles' => 'integer',
        ];
    }
}
