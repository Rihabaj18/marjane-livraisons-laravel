<?php

namespace App\Http\Controllers;

use App\Models\Anomalie;
use Illuminate\Http\Request;

class AnomalieController extends Controller
{
    public function index(Request $request)
    {
        $query = Anomalie::with(['reception.commande.fournisseur', 'reception.magasinier']);

        if ($request->statut) $query->where('statut', $request->statut);
        if ($request->type) $query->where('type', $request->type);
        if ($request->gravite) $query->where('gravite', $request->gravite);

        $anomalies = $query->orderByRaw("FIELD(statut,'ouverte','en_cours','resolue')")
            ->orderByRaw("FIELD(gravite,'elevee','moyenne','faible')")
            ->latest()
            ->get();

        $compteurs = [
            'ouverte' => Anomalie::where('statut', 'ouverte')->count(),
            'en_cours' => Anomalie::where('statut', 'en_cours')->count(),
            'resolue' => Anomalie::where('statut', 'resolue')->count(),
        ];

        return view('anomalies.index', compact('anomalies', 'compteurs'));
    }

    public function updateStatut(Request $request, Anomalie $anomalie)
    {
        $request->validate(['statut' => 'required|in:ouverte,en_cours,resolue']);

        $data = ['statut' => $request->statut];
        if ($request->statut === 'resolue') {
            $data['resolu_par'] = auth()->id();
            $data['resolu_le'] = now();
        }

        $anomalie->update($data);

        return back()->with('success', 'Statut mis à jour.');
    }
}