<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Reception;
use App\Models\LigneReception;
use App\Models\Anomalie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    public function index(Request $request)
    {
        $commandeId = $request->query('commande_id');
        $commande = null;
        $lignes = collect();

        if ($commandeId) {
            $commande = Commande::with('fournisseur')->findOrFail($commandeId);
            $lignes = $commande->lignes()->with('produit')->get();
        }

        $commandesDispo = Commande::with('fournisseur')
            ->whereIn('statut', ['en_attente', 'planifiee'])
            ->orderBy('date_prevue')->orderBy('creneau_debut')
            ->get();

        return view('reception.index', compact('commande', 'lignes', 'commandesDispo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'quantite_recue' => 'required|array',
        ]);

        $commande = Commande::with('fournisseur')->findOrFail($request->commande_id);
        $anomaliesInput = $request->input('anomalie', []);
        $aAnomalie = !empty($anomaliesInput);
        $toutesOk = true;

        foreach ($request->quantite_recue as $lcId => $qteRecue) {
            $ligneCommande = \App\Models\LigneCommande::find($lcId);
            if ((float)$qteRecue < $ligneCommande->quantite_prevue) {
                $toutesOk = false;
            }
        }

        $statutRec = $aAnomalie ? 'anomalie' : ($toutesOk ? 'conforme' : 'partielle');

        DB::transaction(function () use ($request, $commande, $anomaliesInput, $aAnomalie, $toutesOk, $statutRec, &$reception) {
            $reception = Reception::create([
                'commande_id' => $commande->id,
                'magasinier_id' => auth()->id(),
                'statut' => $statutRec,
                'observations' => $request->observations,
            ]);

            foreach ($request->quantite_recue as $lcId => $qteRecue) {
                $ligneCommande = \App\Models\LigneCommande::find($lcId);
                $conforme = ((float)$qteRecue >= $ligneCommande->quantite_prevue && !isset($anomaliesInput[$lcId]));

                LigneReception::create([
                    'reception_id' => $reception->id,
                    'ligne_commande_id' => $lcId,
                    'quantite_recue' => $qteRecue,
                    'conforme' => $conforme,
                ]);
            }

            if ($aAnomalie) {
                foreach ($anomaliesInput as $lcId => $anom) {
                    if (!empty($anom['type'])) {
                        $photoPath = null;
                        if (isset($anom['photo']) && $anom['photo']->isValid()) {
                            $photoPath = $anom['photo']->store('anomalies', 'public');
                        }
                        Anomalie::create([
                            'reception_id' => $reception->id,
                            'type' => $anom['type'],
                            'description' => $anom['description'] ?? '',
                            'gravite' => $anom['gravite'] ?? 'moyenne',
                            'photo_path' => $photoPath,
                        ]);
                    }
                }
            }

            $newStatut = $aAnomalie ? 'anomalie' : ($toutesOk ? 'validee' : 'recue');
            $commande->update(['statut' => $newStatut]);
        });

        return redirect()->route('reception.index')
            ->with('success', 'Réception enregistrée avec succès. Statut : ' . ucfirst($reception->commande->statut));
    }
}