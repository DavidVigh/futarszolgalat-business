<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FutarPozicio extends Model
{
    protected $table = 'futar_pozicio';

    public $timestamps = false;

    protected $fillable = ['futarID', 'gps_x', 'gps_y', 'cim'];

    protected function casts(): array
    {
        return [
            'gps_x' => 'float',
            'gps_y' => 'float',
        ];
    }
}
