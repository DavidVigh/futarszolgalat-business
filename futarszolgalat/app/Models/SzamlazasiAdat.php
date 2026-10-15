<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Billing details of an order that asked for an invoice (rendeles.szamla = 1). */
class SzamlazasiAdat extends Model
{
    protected $table = 'szamlazasi_adat';

    protected $primaryKey = 'rendeles_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['rendeles_id', 'nev', 'cim', 'adoszam'];

    public function rendeles(): BelongsTo
    {
        return $this->belongsTo(Rendeles::class, 'rendeles_id');
    }
}
