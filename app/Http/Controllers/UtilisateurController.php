<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nom','like',"%$s%")->orWhere('prenom','like',"%$s%")->orWhere('email','like',"%$s%"));
        }
        $utilisateurs = $query->orderBy('role')->orderBy('nom')->get();

        return view('utilisateurs.index', compact('utilisateurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required', 'prenom' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:magasinier,responsable,admin',
            'mot_de_passe' => 'required|min:6',
        ]);

        User::create([
            'nom' => $request->nom, 'prenom' => $request->prenom,
            'email' => $request->email, 'role' => $request->role,
            'password' => Hash::make($request->mot_de_passe),
        ]);

        return back()->with('success', "Compte créé pour {$request->prenom} {$request->nom}.");
    }

    public function update(Request $request, User $utilisateur)
    {
        $request->validate(['nom' => 'required', 'prenom' => 'required', 'role' => 'required|in:magasinier,responsable,admin']);

        if ($utilisateur->id == auth()->id() && $request->role !== 'admin') {
            $nbAdmins = User::where('role', 'admin')->where('actif', true)->count();
            if ($nbAdmins <= 1) {
                return back()->with('error', 'Vous êtes le seul administrateur actif.');
            }
        }

        $utilisateur->update(['nom' => $request->nom, 'prenom' => $request->prenom, 'role' => $request->role]);

        return back()->with('success', 'Compte mis à jour.');
    }

    public function destroy(User $utilisateur)
    {
        if ($utilisateur->id == auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        $utilisateur->update(['actif' => !$utilisateur->actif]);

        return back()->with('success', 'Statut mis à jour.');
    }
}