<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\LigneCommande;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $query = Commande::with(['fournisseur', 'responsable'])
            ->withCount('lignes as nb_articles');

        if ($request->statut) $query->where('statut', $request->statut);
        if ($request->date) $query->whereDate('date_prevue', $request->date);
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('numero', 'like', "%$s%")
                  ->orWhereHas('fournisseur', fn($q2) => $q2->where('nom', 'like', "%$s%"));
            });
        }

        $commandes = $query->orderByDesc('date_prevue')->orderBy('creneau_debut')->get();
        $fournisseurs = Fournisseur::where('actif', true)->orderBy('nom')->get();
        $produits = Produit::orderBy('nom')->get();

        return view('commandes.index', compact('commandes', 'fournisseurs', 'produits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_prevue' => 'required|date',
        ]);

        $count = Commande::whereYear('created_at', now()->year)->count();
        $numero = 'CMD-' . now()->year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $commande = Commande::create([
            'numero' => $numero,
            'fournisseur_id' => $request->fournisseur_id,
            'responsable_id' => auth()->id(),
            'date_prevue' => $request->date_prevue,
            'creneau_debut' => $request->creneau_debut,
            'creneau_fin' => $request->creneau_fin,
            'notes' => $request->notes,
        ]);

        $produitIds = $request->input('produit_id', []);
        $quantites = $request->input('quantite', []);
        $prix = $request->input('prix_unitaire', []);

        foreach ($produitIds as $i => $pid) {
            if ($pid && $quantites[$i] > 0) {
                LigneCommande::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $pid,
                    'quantite_prevue' => $quantites[$i],
                    'prix_unitaire' => $prix[$i] ?: null,
                ]);
            }
        }

        return redirect()->route('commandes.index')->with('success', "Commande {$numero} créée avec succès.");
    }

    public function show(Commande $commande)
    {
        $commande->load(['fournisseur', 'lignes.produit']);
        return response()->json($commande);
    }
}