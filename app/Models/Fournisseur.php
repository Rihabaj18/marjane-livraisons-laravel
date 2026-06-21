<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'contact', 'telephone', 'email', 'adresse', 'actif',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function tauxConformite(): ?int
    {
        $total = $this->commandes()->whereIn('statut', ['validee', 'anomalie', 'recue'])->count();
        if ($total === 0) return null;
        $validees = $this->commandes()->where('statut', 'validee')->count();
        return (int) round(100 * $validees / $total);
    }
}