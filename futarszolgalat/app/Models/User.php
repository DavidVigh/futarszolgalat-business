<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'felhasznalo';

    public $timestamps = false;

    protected $fillable = [
        'nev',
        'cim',
        'telszam',
        'email',
        'password',
        'isAdmin',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'isAdmin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->getAttribute('isAdmin') === true;
    }

    public function rendelesek(): HasMany
    {
        return $this->hasMany(Rendeles::class, 'felhasznalo_id');
    }
}
