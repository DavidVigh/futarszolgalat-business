<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendelesTetel extends Model
{
    protected $table = 'rendeles_tetel';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['rendeles_id', 'termek_id', 'db'];

    protected function casts(): array
    {
        return ['db' => 'integer'];
    }
}
