@extends('layouts.app')
@section('title', 'Rapports')

@section('content')

<form method="GET" action="{{ route('rapports.index') }}">
    <div class="filters mb-2">
        <div><label style="font-size:0.8rem;color:var(--texte-clair)">Du</label><input type="date" name="debut" value="{{ $dateDebut }}"></div>
        <div><label style="font-size:0.8rem;color:var(--texte-clair)">Au</label><input type="date" name="fin" value="{{ $dateFin }}"></div>
        <select name="fournisseur_id">
            <option value="">Tous les fournisseurs</option>
            @foreach($fournisseurs as $f)
            <option value="{{ $f->id }}" {{ $fournisseurId==$f->id?'selected':'' }}>{{ $f->nom }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Actualiser</button>
        <a href="{{ route('rapports.export', ['debut'=>$dateDebut,'fin'=>$dateFin,'fournisseur_id'=>$fournisseurId]) }}" class="btn btn-success">
            <i class="ri-download-line"></i> Export CSV
        </a>
    </div>
</form>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
    <div class="stat-card"><div class="stat-icon bleu"><i class="ri-truck-line"></i></div><div><div class="stat-value">{{ $kpis['total_commandes'] }}</div><div class="stat-label">Commandes</div></div></div>
    <div class="stat-card"><div class="stat-icon vert"><i class="ri-checkbox-circle-line"></i></div><div><div class="stat-value">{{ $kpis['taux_conformite'] }}%</div><div class="stat-label">Taux conformité</div></div></div>
    <div class="stat-card"><div class="stat-icon rouge"><i class="ri-alert-line"></i></div><div><div class="stat-value">{{ $kpis['avec_anomalie'] }}</div><div class="stat-label">Avec anomalie</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="ri-store-2-line"></i></div><div><div class="stat-value">{{ $kpis['nb_fournisseurs'] }}</div><div class="stat-label">Fournisseurs</div></div></div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;margin-bottom:1.25rem">
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Conformité par fournisseur</span></div>
        @forelse($parFournisseur as $pf)
        <div style="margin-bottom:1rem">
            <div class="flex-between mb-1" style="font-size:0.875rem">
                <strong>{{ $pf['nom'] }}</strong>
                <span>{{ $pf['validees'] }}/{{ $pf['total'] }} &middot;
                    <strong style="color:{{ $pf['taux']>=80?'var(--vert)':($pf['taux']>=50?'var(--orange)':'var(--rouge)') }}">{{ $pf['taux'] }}%</strong></span>
            </div>
            <div class="progress"><div class="progress-bar {{ $pf['taux']>=80?'vert':($pf['taux']>=50?'orange':'rouge') }}" style="width:{{ $pf['taux'] }}%"></div></div>
        </div>
        @empty
        <div class="empty-state"><p>Aucune donnée sur cette période.</p></div>
        @endforelse
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Types d'anomalies</span></div>
        @forelse($typesAnomalies as $t)
        @php $labels = ['quantite'=>'Quantité','qualite'=>'Qualité','emballage'=>'Emballage','retard'=>'Retard','autre'=>'Autre']; $max = $typesAnomalies->max('n'); @endphp
        <div style="margin-bottom:0.75rem">
            <div class="flex-between mb-1" style="font-size:0.85rem"><span>{{ $labels[$t->type] ?? $t->type }}</span><strong>{{ $t->n }}</strong></div>
            <div class="progress"><div class="progress-bar rouge" style="width:{{ round($t->n/$max*100) }}%"></div></div>
        </div>
        @empty
        <div class="empty-state"><div class="empty-icon"><i class="ri-checkbox-circle-line"></i></div><p>Aucune anomalie.</p></div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Historique détaillé</span><small style="color:var(--texte-clair)">{{ $historique->count() }} entrée(s)</small></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>N° commande</th><th>Fournisseur</th><th>Date prévue</th><th>Réceptionné le</th><th>Statut</th><th>Anomalies</th><th>Magasinier</th></tr></thead>
            <tbody>
            @forelse($historique as $h)
            @php
                $map = ['en_attente'=>['attente','En attente'],'planifiee'=>['planif','Planifiée'],'recue'=>['recue','Reçue'],'validee'=>['validee','Validée'],'anomalie'=>['anomalie','Anomalie']];
                [$bc, $bt] = $map[$h->statut] ?? ['attente', $h->statut];
                $reception = $h->receptions->first();
            @endphp
            <tr>
                <td>{{ $h->numero }}</td>
                <td>{{ $h->fournisseur->nom }}</td>
                <td>{{ $h->date_prevue->format('d/m/Y') }}</td>
                <td>{{ $reception ? $reception->created_at->format('d/m/Y H:i') : '—' }}</td>
                <td><span class="badge badge-{{ $bc }}">{{ $bt }}</span></td>
                <td class="text-center">
                    @if($reception && $reception->anomalies->count() > 0)
                        <span class="badge badge-anomalie">{{ $reception->anomalies->count() }}</span>
                    @else
                        <span style="color:var(--texte-clair)">0</span>
                    @endif
                </td>
                <td>{{ $reception ? $reception->magasinier->nomComplet() : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucune donnée sur cette période.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection