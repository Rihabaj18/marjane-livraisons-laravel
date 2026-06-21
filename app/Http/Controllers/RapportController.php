<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Anomalie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $dateDebut = $request->debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin = $request->fin ?? now()->format('Y-m-d');
        $fournisseurId = $request->fournisseur_id;

        $fournisseurs = Fournisseur::where('actif', true)->orderBy('nom')->get();

        $query = Commande::whereBetween('date_prevue', [$dateDebut, $dateFin]);
        if ($fournisseurId) $query->where('fournisseur_id', $fournisseurId);

        $kpis = [
            'total_commandes' => (clone $query)->count(),
            'validees' => (clone $query)->where('statut', 'validee')->count(),
            'avec_anomalie' => (clone $query)->where('statut', 'anomalie')->count(),
            'nb_fournisseurs' => (clone $query)->distinct('fournisseur_id')->count('fournisseur_id'),
        ];
        $kpis['taux_conformite'] = $kpis['total_commandes'] > 0
            ? round(100 * $kpis['validees'] / $kpis['total_commandes'], 1) : 0;

        $parFournisseurQuery = Commande::whereBetween('date_prevue', [$dateDebut, $dateFin]);
        if ($fournisseurId) $parFournisseurQuery->where('fournisseur_id', $fournisseurId);

        $parFournisseur = $parFournisseurQuery
            ->selectRaw('fournisseur_id, count(*) as total, sum(statut="validee") as validees')
            ->groupBy('fournisseur_id')
            ->with('fournisseur')
            ->get()
            ->map(fn($row) => [
                'nom' => $row->fournisseur->nom,
                'total' => $row->total,
                'validees' => $row->validees,
                'taux' => $row->total > 0 ? round(100 * $row->validees / $row->total) : 0,
            ])
            ->sortByDesc('taux');

        $typesAnomalies = Anomalie::whereHas('reception.commande', function ($q) use ($dateDebut, $dateFin, $fournisseurId) {
                $q->whereBetween('date_prevue', [$dateDebut, $dateFin]);
                if ($fournisseurId) $q->where('fournisseur_id', $fournisseurId);
            })
            ->selectRaw('type, count(*) as n')
            ->groupBy('type')
            ->orderByDesc('n')
            ->get();

        $historique = Commande::with(['fournisseur', 'receptions.magasinier'])
            ->whereBetween('date_prevue', [$dateDebut, $dateFin])
            ->when($fournisseurId, fn($q) => $q->where('fournisseur_id', $fournisseurId))
            ->orderByDesc('date_prevue')
            ->get();

        return view('rapports.index', compact(
            'dateDebut', 'dateFin', 'fournisseurId', 'fournisseurs',
            'kpis', 'parFournisseur', 'typesAnomalies', 'historique'
        ));
    }

    public function export(Request $request)
    {
        $dateDebut = $request->debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin = $request->fin ?? now()->format('Y-m-d');
        $fournisseurId = $request->fournisseur_id;

        $commandes = Commande::with(['fournisseur', 'receptions.magasinier', 'receptions.anomalies'])
            ->whereBetween('date_prevue', [$dateDebut, $dateFin])
            ->when($fournisseurId, fn($q) => $q->where('fournisseur_id', $fournisseurId))
            ->orderByDesc('date_prevue')
            ->get();

        $filename = 'rapport_livraisons_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($commandes) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['N° Commande','Fournisseur','Date prévue','Date réception','Statut','Anomalies','Magasinier'], ';');

            foreach ($commandes as $c) {
                $reception = $c->receptions->first();
                fputcsv($out, [
                    $c->numero,
                    $c->fournisseur->nom,
                    $c->date_prevue->format('Y-m-d'),
                    $reception ? $reception->created_at->format('Y-m-d H:i') : 'Non reçue',
                    $c->statut,
                    $reception ? $reception->anomalies->count() : 0,
                    $reception ? $reception->magasinier->nomComplet() : '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}