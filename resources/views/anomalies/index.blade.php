@extends('layouts.app')
@section('title', 'Anomalies')

@section('content')

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <div class="stat-icon rouge"><i class="ri-alert-line"></i></div>
        <div><div class="stat-value">{{ $compteurs['ouverte'] }}</div><div class="stat-label">Ouvertes</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-time-line"></i></div>
        <div><div class="stat-value">{{ $compteurs['en_cours'] }}</div><div class="stat-label">En cours</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon vert"><i class="ri-checkbox-circle-line"></i></div>
        <div><div class="stat-value">{{ $compteurs['resolue'] }}</div><div class="stat-label">Résolues</div></div>
    </div>
</div>

<form method="GET" class="filters">
    <select name="statut" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="ouverte" {{ request('statut')==='ouverte'?'selected':'' }}>Ouvertes</option>
        <option value="en_cours" {{ request('statut')==='en_cours'?'selected':'' }}>En cours</option>
        <option value="resolue" {{ request('statut')==='resolue'?'selected':'' }}>Résolues</option>
    </select>
    <select name="type" onchange="this.form.submit()">
        <option value="">Tous les types</option>
        <option value="quantite" {{ request('type')==='quantite'?'selected':'' }}>Quantité</option>
        <option value="qualite" {{ request('type')==='qualite'?'selected':'' }}>Qualité</option>
        <option value="emballage" {{ request('type')==='emballage'?'selected':'' }}>Emballage</option>
        <option value="retard" {{ request('type')==='retard'?'selected':'' }}>Retard</option>
        <option value="autre" {{ request('type')==='autre'?'selected':'' }}>Autre</option>
    </select>
    <select name="gravite" onchange="this.form.submit()">
        <option value="">Toutes les gravités</option>
        <option value="elevee" {{ request('gravite')==='elevee'?'selected':'' }}>Élevée</option>
        <option value="moyenne" {{ request('gravite')==='moyenne'?'selected':'' }}>Moyenne</option>
        <option value="faible" {{ request('gravite')==='faible'?'selected':'' }}>Faible</option>
    </select>
</form>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Fournisseur</th><th>Commande</th><th>Type</th><th>Gravité</th><th>Magasinier</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($anomalies as $a)
            <tr>
                <td>{{ $a->created_at->format('d/m/Y') }}<br><small style="color:var(--texte-clair)">{{ $a->created_at->format('H:i') }}</small></td>
                <td><strong>{{ $a->reception->commande->fournisseur->nom }}</strong></td>
                <td>{{ $a->reception->commande->numero }}</td>
                <td>{{ ucfirst($a->type) }}</td>
                <td><span class="badge badge-{{ $a->gravite }}">{{ ucfirst($a->gravite) }}</span></td>
                <td>{{ $a->reception->magasinier->nomComplet() }}</td>
                <td><span class="badge badge-{{ str_replace('_','-',$a->statut) }}">{{ ucfirst(str_replace('_',' ',$a->statut)) }}</span></td>
                <td>
                    @if($a->statut !== 'resolue' && auth()->user()->role !== 'magasinier')
                    <form method="POST" action="{{ route('anomalies.statut', $a) }}" style="display:inline">
                        @csrf
                        <select name="statut" onchange="this.form.submit()" style="padding:0.25rem 0.5rem;border:1px solid var(--gris-bord);border-radius:var(--radius);font-size:0.8rem">
                            <option value="">Changer statut...</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolue">Résoudre</option>
                        </select>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucune anomalie trouvée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection