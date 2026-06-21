<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anomalie extends Model
{
    use HasFactory;

    protected $fillable = [
        'reception_id', 'type', 'description', 'photo_path',
        'gravite', 'statut', 'resolu_par', 'resolu_le',
    ];

    protected function casts(): array
    {
        return ['resolu_le' => 'datetime'];
    }

    public function reception()
    {
        return $this->belongsTo(Reception::class);
    }

    public function resoluPar()
    {
        return $this->belongsTo(User::class, 'resolu_par');
    }
}