<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'jelszo',
        'adminE',
    ];

    protected $hidden = ['jelszo', 'remember_token'];

    public function getAuthPasswordName(): string
    {
        return 'jelszo';
    }

    protected function casts(): array
    {
        return [
            'jelszo' => 'hashed',
            'adminE' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->adminE === true;
    }
}
