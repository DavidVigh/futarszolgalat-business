<?php

namespace App\Models;

use Database\Factories\TermekFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Termek extends Model
{
    /** @use HasFactory<TermekFactory> */
    use HasFactory;

    public const TIPUSOK = ['etel', 'ital'];

    protected $table = 'termek';

    public $timestamps = false;

    protected $fillable = ['tipus', 'nev', 'fogas', 'leiras', 'ar', 'elkeszitesi_ido', 'prioritas', 'aktiv', 'allergen_ellenorizve'];

    protected function casts(): array
    {
        return [
            'ar' => 'integer',
            'elkeszitesi_ido' => 'integer',
            'prioritas' => 'integer',
            'aktiv' => 'boolean',
            'allergen_ellenorizve' => 'boolean',
        ];
    }

    /** @param  Builder<Termek>  $query */
    #[Scope]
    protected function aktivak(Builder $query): void
    {
        $query->where('aktiv', true);
    }

    /** The course must exist in the fogas table with the same tipus (fk_termek_fogas). */
    public function fogasa(): BelongsTo
    {
        return $this->belongsTo(Fogas::class, 'fogas', 'kod');
    }

    public function allergenek(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'termek_allergen', 'termek_id', 'allergen_kod');
    }

    public function rendelesek(): BelongsToMany
    {
        return $this->belongsToMany(Rendeles::class, 'rendeles_tetel', 'termek_id', 'rendeles_id')
            ->withPivot('db', 'egysegar');
    }
}
