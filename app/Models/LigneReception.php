<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneReception extends Model
{
    use HasFactory;

    protected $table = 'lignes_reception';

    protected $fillable = [
        'reception_id', 'ligne_commande_id', 'quantite_recue', 'conforme',
    ];

    protected function casts(): array
    {
        return ['conforme' => 'boolean'];
    }

    public function reception()
    {
        return $this->belongsTo(Reception::class);
    }

    public function ligneCommande()
    {
        return $this->belongsTo(LigneCommande::class);
    }
}