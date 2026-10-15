<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FutarPozicio extends Model
{
    protected $table = 'futar_pozicio';

    public $timestamps = false;

    protected $fillable = ['futarID', 'gps_x', 'gps_y', 'cim', 'rogzitve'];

    protected function casts(): array
    {
        return [
            'gps_x' => 'float',
            'gps_y' => 'float',
            'rogzitve' => 'datetime',
        ];
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'futarID');
    }
}
