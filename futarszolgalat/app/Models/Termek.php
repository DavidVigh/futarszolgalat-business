<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Termek extends Model
{
    protected $table = 'termek';

    public $timestamps = false;

    protected $fillable = ['tipus', 'nev', 'fogas', 'leiras', 'ar', 'elkeszitesi_ido', 'prioritas'];
}
