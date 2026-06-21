<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    use HasFactory;

    protected $table = 'lignes_commande';

    protected $fillable = [
        'commande_id', 'produit_id', 'quantite_prevue', 'prix_unitaire',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function ligneReception()
    {
        return $this->hasOne(LigneReception::class);
    }
}