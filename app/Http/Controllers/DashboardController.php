<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Anomalie;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $stats = [
            'total_commandes' => Commande::duJour()->count(),
            'en_attente'      => Commande::duJour()->enAttente()->count(),
            'validees'        => Commande::duJour()->where('statut', 'validee')->count(),
            'avec_anomalie'   => Commande::duJour()->where('statut', 'anomalie')->count(),
        ];

        $nbAnomaliesOuvertes = Anomalie::where('statut', 'ouverte')->count();
        $nbFournisseurs = Commande::duJour()->distinct('fournisseur_id')->count('fournisseur_id');

        $livraisonsJour = Commande::with(['fournisseur', 'responsable'])
            ->duJour()
            ->orderBy('creneau_debut')
            ->get();

        $prochaines = Commande::with('fournisseur')
            ->whereBetween('date_prevue', [$today->copy()->addDay(), $today->copy()->addDays(3)])
            ->orderBy('date_prevue')->orderBy('creneau_debut')
            ->limit(5)->get();

        $anomaliesRecentes = Anomalie::with(['reception.commande.fournisseur', 'reception.magasinier'])
            ->where('statut', '!=', 'resolue')
            ->latest()
            ->limit(5)->get();

        // Données graphiques - évolution 7 jours
        $joursLabels = []; $joursTotal = []; $joursValidees = []; $joursAnomalies = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $joursLabels[] = $date->format('d/m');
            $cmds = Commande::whereDate('date_prevue', $date);
            $joursTotal[] = (clone $cmds)->count();
            $joursValidees[] = (clone $cmds)->where('statut', 'validee')->count();
            $joursAnomalies[] = (clone $cmds)->where('statut', 'anomalie')->count();
        }

        $repartition = Commande::selectRaw('statut, count(*) as total')
            ->groupBy('statut')->pluck('total', 'statut');

        $topFournisseurs = \App\Models\Fournisseur::withCount([
                'commandes as total_commandes' => fn($q) => $q->whereIn('statut', ['validee', 'anomalie', 'recue']),
                'commandes as validees' => fn($q) => $q->where('statut', 'validee'),
            ])
            ->having('total_commandes', '>', 0)
            ->get()
            ->map(fn($f) => [
                'nom' => $f->nom,
                'taux' => $f->total_commandes > 0 ? round(100 * $f->validees / $f->total_commandes) : 0,
            ])
            ->sortByDesc('taux')
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'stats', 'nbAnomaliesOuvertes', 'nbFournisseurs',
            'livraisonsJour', 'prochaines', 'anomaliesRecentes',
            'joursLabels', 'joursTotal', 'joursValidees', 'joursAnomalies',
            'repartition', 'topFournisseurs'
        ));
    }
}