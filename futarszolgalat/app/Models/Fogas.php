<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Lookup table of courses; a product's (fogas, tipus) pair must match a row here. */
class Fogas extends Model
{
    protected $table = 'fogas';

    protected $primaryKey = 'kod';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['kod', 'tipus'];

    public function termekek(): HasMany
    {
        return $this->hasMany(Termek::class, 'fogas', 'kod');
    }
}
