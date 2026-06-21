<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'role', 'actif',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    // Relations
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'responsable_id');
    }

    public function receptions()
    {
        return $this->hasMany(Reception::class, 'magasinier_id');
    }

    // Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isResponsable(): bool
    {
        return $this->role === 'responsable';
    }

    public function isMagasinier(): bool
    {
        return $this->role === 'magasinier';
    }

    public function nomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}