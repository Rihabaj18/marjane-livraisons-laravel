@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bleu"><i class="ri-truck-line"></i></div>
        <div>
            <div class="stat-value">{{ $stats['total_commandes'] }}</div>
            <div class="stat-label">Livraisons aujourd'hui</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon jaune"><i class="ri-time-line"></i></div>
        <div>
            <div class="stat-value">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">En attente / planifiées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon vert"><i class="ri-checkbox-circle-line"></i></div>
        <div>
            <div class="stat-value">{{ $stats['validees'] }}</div>
            <div class="stat-label">Validées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rouge"><i class="ri-alert-line"></i></div>
        <div>
            <div class="stat-value">{{ $nbAnomaliesOuvertes }}</div>
            <div class="stat-label">Anomalies ouvertes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="ri-store-2-line"></i></div>
        <div>
            <div class="stat-value">{{ $nbFournisseurs }}</div>
            <div class="stat-label">Fournisseurs attendus</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title"><i class="ri-calendar-line"></i> Livraisons du {{ now()->format('d/m/Y') }}</span>
            <a href="{{ route('commandes.index') }}" class="btn btn-outline btn-sm">Voir tout</a>
        </div>
        @if($livraisonsJour->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="ri-calendar-line"></i></div>
                <p>Aucune livraison prévue aujourd'hui.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Créneau</th><th>Fournisseur</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @foreach($livraisonsJour as $l)
                        <tr>
                            <td>{{ $l->creneau_debut ? \Carbon\Carbon::parse($l->creneau_debut)->format('H:i') . ' - ' . \Carbon\Carbon::parse($l->creneau_fin)->format('H:i') : '—' }}</td>
                            <td><strong>{{ $l->fournisseur->nom }}</strong><br><small>{{ $l->numero }}</small></td>
                            <td>
                                @php
                                    $map = ['en_attente'=>['attente','En attente'],'planifiee'=>['planif','Planifiée'],'recue'=>['recue','Reçue'],'validee'=>['validee','Validée'],'anomalie'=>['anomalie','Anomalie']];
                                    [$cls, $txt] = $map[$l->statut] ?? ['attente', $l->statut];
                                @endphp
                                <span class="badge badge-{{ $cls }}">{{ $txt }}</span>
                            </td>
                            <td>
                                @if(in_array($l->statut, ['planifiee','en_attente']))
                                    <a href="{{ route('reception.index', ['commande_id' => $l->id]) }}" class="btn btn-jaune btn-sm">Réceptionner</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title"><i class="ri-time-line"></i> Prochaines livraisons</span>
        </div>
        @if($prochaines->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="ri-calendar-line"></i></div>
                <p>Rien de prévu les 3 prochains jours.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Fournisseur</th><th>Créneau</th></tr></thead>
                    <tbody>
                    @foreach($prochaines as $p)
                        <tr>
                            <td>{{ $p->date_prevue->format('d/m') }}</td>
                            <td>{{ $p->fournisseur->nom }}</td>
                            <td>{{ $p->creneau_debut ? \Carbon\Carbon::parse($p->creneau_debut)->format('H:i') : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="ri-alert-line"></i> Anomalies en cours</span>
        <a href="{{ route('anomalies.index') }}" class="btn btn-outline btn-sm">Gérer</a>
    </div>
    @if($anomaliesRecentes->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="ri-checkbox-circle-line"></i></div>
            <p>Aucune anomalie ouverte. Tout est en ordre !</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Fournisseur</th><th>Type</th><th>Gravité</th><th>Statut</th></tr></thead>
                <tbody>
                @foreach($anomaliesRecentes as $a)
                    <tr>
                        <td>{{ $a->created_at->format('d/m H:i') }}</td>
                        <td>{{ $a->reception->commande->fournisseur->nom }}</td>
                        <td>{{ ucfirst($a->type) }}</td>
                        <td><span class="badge badge-{{ $a->gravite }}">{{ ucfirst($a->gravite) }}</span></td>
                        <td><span class="badge badge-{{ str_replace('_','-',$a->statut) }}">{{ ucfirst(str_replace('_',' ',$a->statut)) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;margin-top:1.25rem">
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title"><i class="ri-line-chart-line"></i> Évolution sur 7 jours</span></div>
        <div style="position:relative;height:260px"><canvas id="chartEvolution"></canvas></div>
    </div>
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title"><i class="ri-pie-chart-line"></i> Répartition globale</span></div>
        <div style="position:relative;height:260px"><canvas id="chartRepartition"></canvas></div>
    </div>
</div>

<div class="card mt-2">
    <div class="card-header"><span class="card-title"><i class="ri-trophy-line"></i> Top fournisseurs — taux de conformité</span></div>
    @if($topFournisseurs->isEmpty())
        <div class="empty-state"><p>Pas encore assez de données.</p></div>
    @else
        <div style="position:relative;height:{{ max(150, count($topFournisseurs)*55) }}px"><canvas id="chartFournisseurs"></canvas></div>
    @endif
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const COLOR_BLEU='#1B3A6B',COLOR_VERT='#28a745',COLOR_ROUGE='#dc3545',COLOR_GRIS='#dee2e6',COLOR_ORANGE='#fd7e14';

new Chart(document.getElementById('chartEvolution'),{type:'line',data:{labels:@json($joursLabels),datasets:[
    {label:'Total',data:@json($joursTotal),borderColor:COLOR_BLEU,backgroundColor:COLOR_BLEU+'20',tension:0.35,fill:true,borderWidth:2},
    {label:'Validées',data:@json($joursValidees),borderColor:COLOR_VERT,tension:0.35,fill:false,borderWidth:2},
    {label:'Anomalies',data:@json($joursAnomalies),borderColor:COLOR_ROUGE,tension:0.35,fill:false,borderWidth:2}
]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});

const repartition = @json($repartition);
const labelsMap = {en_attente:'En attente',planifiee:'Planifiée',recue:'Reçue',validee:'Validée',anomalie:'Anomalie'};
const colorsMap = {en_attente:'#FFB800',planifiee:COLOR_BLEU,recue:'#9b6dd6',validee:COLOR_VERT,anomalie:COLOR_ROUGE};
new Chart(document.getElementById('chartRepartition'),{type:'doughnut',data:{
    labels:Object.keys(repartition).map(k=>labelsMap[k]||k),
    datasets:[{data:Object.values(repartition),backgroundColor:Object.keys(repartition).map(k=>colorsMap[k]||COLOR_GRIS),borderWidth:2,borderColor:'#fff'}]
},options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{position:'bottom'}}}});

@if($topFournisseurs->isNotEmpty())
const fournData = @json($topFournisseurs);
new Chart(document.getElementById('chartFournisseurs'),{type:'bar',data:{
    labels:fournData.map(f=>f.nom),
    datasets:[{label:'Taux (%)',data:fournData.map(f=>f.taux),backgroundColor:fournData.map(f=>f.taux>=80?COLOR_VERT:(f.taux>=50?COLOR_ORANGE:COLOR_ROUGE)),borderRadius:6}]
},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,max:100}}}});
@endif
</script>

@endsection