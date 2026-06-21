<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $query = Fournisseur::query();

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%$s%")
                  ->orWhere('contact', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        $fournisseurs = $query->orderBy('nom')->get()->map(function ($f) {
            $f->taux = $f->tauxConformite();
            $f->total_cmds = $f->commandes()->count();
            return $f;
        });

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function store(Request $request)
    {
        $request->validate(['nom' => 'required|string']);

        Fournisseur::create($request->only(['nom', 'contact', 'telephone', 'email', 'adresse']));

        return back()->with('success', "Fournisseur {$request->nom} ajouté.");
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate(['nom' => 'required|string']);

        $fournisseur->update($request->only(['nom', 'contact', 'telephone', 'email', 'adresse']));

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->update(['actif' => !$fournisseur->actif]);

        return back()->with('success', 'Statut mis à jour.');
    }
}