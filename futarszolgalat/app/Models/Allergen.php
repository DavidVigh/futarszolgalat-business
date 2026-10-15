<?php

namespace App\Models;

use Database\Factories\AllergenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    /** @use HasFactory<AllergenFactory> */
    use HasFactory;

    protected $table = 'allergen';

    protected $primaryKey = 'kod';

    public $timestamps = false;

    protected $fillable = ['nev'];

    public function termekek(): BelongsToMany
    {
        return $this->belongsToMany(Termek::class, 'termek_allergen', 'allergen_kod', 'termek_id');
    }
}
