<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id', 'magasinier_id', 'statut', 'observations',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function magasinier()
    {
        return $this->belongsTo(User::class, 'magasinier_id');
    }

    public function lignes()
    {
        return $this->hasMany(LigneReception::class);
    }

    public function anomalies()
    {
        return $this->hasMany(Anomalie::class);
    }
}