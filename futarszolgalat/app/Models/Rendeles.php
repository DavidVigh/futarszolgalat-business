<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rendeles extends Model
{
    public const STATUSZOK = ['új', 'elfogadva', 'készül', 'úton', 'kiszállítva', 'lemondva'];

    /** Statuses in which the order is still waiting for the courier to pick it up. */
    public const FELVETELRE_VAR = ['új', 'elfogadva', 'készül'];

    public const FIZETESI_MODOK = ['keszpenz', 'kartya'];

    protected $table = 'rendeles';

    public $timestamps = false;

    protected $fillable = ['felhasznalo_id', 'futar_id', 'cim', 'prioritas', 'fizetesi_mod', 'jatt', 'szamla', 'km', 'statusz', 'ertekeles'];

    protected function casts(): array
    {
        return [
            'felhasznalo_id' => 'integer',
            'futar_id' => 'integer',
            'prioritas' => 'boolean',
            'szamla' => 'boolean',
            'jatt' => 'integer',
            'km' => 'integer',
            'ertekeles' => 'integer',
        ];
    }

    public function felhasznalo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'felhasznalo_id');
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'futar_id');
    }

    public function termekek(): BelongsToMany
    {
        return $this->belongsToMany(Termek::class, 'rendeles_tetel', 'rendeles_id', 'termek_id')
            ->withPivot('db');
    }
}
