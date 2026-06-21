<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::withCount('lignesCommande as nb_commandes');

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nom', 'like', "%$s%")->orWhere('reference', 'like', "%$s%"));
        }

        $produits = $query->orderBy('nom')->get();
        $unites = ['unité', 'carton', 'sac', 'kg', 'tonne', 'litre', 'palette', 'boite', 'pack'];

        return view('produits.index', compact('produits', 'unites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|unique:produits,reference',
            'nom' => 'required|string',
        ]);

        Produit::create([
            'reference' => strtoupper($request->reference),
            'nom' => $request->nom,
            'unite' => $request->unite ?: 'unité',
        ]);

        return back()->with('success', "Produit {$request->nom} ajouté.");
    }

    public function update(Request $request, Produit $produit)
    {
        $request->validate(['nom' => 'required|string']);

        $produit->update(['nom' => $request->nom, 'unite' => $request->unite ?: 'unité']);

        return back()->with('success', 'Produit mis à jour.');
    }

    public function destroy(Produit $produit)
    {
        if ($produit->lignesCommande()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : ce produit est utilisé dans des commandes.');
        }

        $produit->delete();

        return back()->with('success', 'Produit supprimé.');
    }
}