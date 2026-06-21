<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero', 'fournisseur_id', 'responsable_id', 'date_prevue',
        'creneau_debut', 'creneau_fin', 'statut', 'notes',
    ];

    protected function casts(): array
    {
        return ['date_prevue' => 'date'];
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function receptions()
    {
        return $this->hasMany(Reception::class);
    }

    // Scopes utiles
    public function scopeDuJour($query, $date = null)
    {
        return $query->whereDate('date_prevue', $date ?? today());
    }

    public function scopeEnAttente($query)
    {
        return $query->whereIn('statut', ['en_attente', 'planifiee']);
    }
}