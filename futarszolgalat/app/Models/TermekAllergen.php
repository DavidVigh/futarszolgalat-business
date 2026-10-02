<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermekAllergen extends Model
{
    protected $table = 'termek_allergen';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['termek_id', 'allergen_kod'];
}
