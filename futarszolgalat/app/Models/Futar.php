<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Futar extends Model
{
    protected $table = 'futar';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = ['nev', 'telszam', 'jarmu', 'ember', 'allapot', 'hatosugar_km'];

    protected function casts(): array
    {
        return ['ember' => 'boolean', 'hatosugar_km' => 'integer'];
    }
}
