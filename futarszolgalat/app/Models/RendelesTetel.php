<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (rendeles_id, termek_id): update and delete rows through
 * the query builder, not through model instances.
 */
class RendelesTetel extends Model
{
    protected $table = 'rendeles_tetel';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['rendeles_id', 'termek_id', 'db', 'egysegar'];

    protected function casts(): array
    {
        return ['db' => 'integer', 'egysegar' => 'integer'];
    }

    public function rendeles(): BelongsTo
    {
        return $this->belongsTo(Rendeles::class, 'rendeles_id');
    }

    public function termek(): BelongsTo
    {
        return $this->belongsTo(Termek::class, 'termek_id');
    }
}
