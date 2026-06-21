@extends('layouts.app')
@section('title', 'Réception au quai')

@section('content')

@if(!$commande)
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="ri-truck-line"></i> Choisir une livraison à réceptionner</span>
    </div>
    @if($commandesDispo->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="ri-checkbox-circle-line"></i></div>
            <p>Toutes les livraisons ont été traitées.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>N° commande</th><th>Fournisseur</th><th>Date prévue</th><th>Créneau</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                @foreach($commandesDispo as $c)
                <tr>
                    <td><strong>{{ $c->numero }}</strong></td>
                    <td>{{ $c->fournisseur->nom }}</td>
                    <td>{{ $c->date_prevue->format('d/m/Y') }}</td>
                    <td>{{ $c->creneau_debut ? \Carbon\Carbon::parse($c->creneau_debut)->format('H:i').' → '.\Carbon\Carbon::parse($c->creneau_fin)->format('H:i') : '—' }}</td>
                    <td><span class="badge badge-{{ $c->statut === 'planifiee' ? 'planif' : 'attente' }}">{{ $c->statut === 'planifiee' ? 'Planifiée' : 'En attente' }}</span></td>
                    <td><a href="{{ route('reception.index', ['commande_id' => $c->id]) }}" class="btn btn-jaune btn-sm"><i class="ri-checkbox-circle-line"></i> Réceptionner</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@else
<div class="card">
    <div class="card-header">
        <div>
            <span class="card-title">Réception — {{ $commande->numero }}</span>
            <div style="font-size:0.85rem;color:var(--texte-clair);margin-top:0.2rem">
                {{ $commande->fournisseur->nom }} &middot; {{ $commande->date_prevue->format('d/m/Y') }}
                @if($commande->creneau_debut)
                    &middot; {{ \Carbon\Carbon::parse($commande->creneau_debut)->format('H:i') }} → {{ \Carbon\Carbon::parse($commande->creneau_fin)->format('H:i') }}
                @endif
            </div>
        </div>
        <a href="{{ route('reception.index') }}" class="btn btn-outline btn-sm">&larr; Retour</a>
    </div>

    <div style="margin-bottom:1.25rem">
        <div class="flex-between mb-1" style="font-size:0.85rem;color:var(--texte-clair)">
            <span>Progression</span>
            <span id="progress-txt">0 / {{ $lignes->count() }} articles saisis</span>
        </div>
        <div class="progress"><div class="progress-bar vert" id="progress-bar" style="width:0%"></div></div>
    </div>

    <form method="POST" action="{{ route('reception.store') }}" enctype="multipart/form-data" id="form-reception">
        @csrf
        <input type="hidden" name="commande_id" value="{{ $commande->id }}">

        @foreach($lignes as $l)
        <div class="reception-ligne" id="ligne-{{ $l->id }}">
            <div class="rl-produit">
                <strong>{{ $l->produit->nom }}</strong>
                <small>Réf: {{ $l->produit->reference }} &middot; Attendu : <strong>{{ $l->quantite_prevue }} {{ $l->produit->unite }}</strong></small>
            </div>
            <div class="rl-qte">
                <label style="font-size:0.75rem;color:var(--texte-clair);display:block;text-align:center;margin-bottom:2px">Qté reçue</label>
                <input type="number" name="quantite_recue[{{ $l->id }}]" id="qte-{{ $l->id }}"
                       value="{{ $l->quantite_prevue }}" min="0" step="0.01"
                       onchange="verifierLigne({{ $l->id }}, {{ $l->quantite_prevue }}, this.value)"
                       oninput="mettreAJourProgression()">
            </div>
            <div class="rl-status" id="status-{{ $l->id }}">—</div>
        </div>

        <div id="anomalie-{{ $l->id }}" style="display:none;background:var(--rouge-pale);border:1px solid var(--rouge);border-radius:var(--radius);padding:0.75rem;margin:-0.4rem 0 0.6rem;border-top:none">
            <div style="font-size:0.85rem;font-weight:600;color:#842029;margin-bottom:0.5rem">
                <i class="ri-alert-line"></i> Signalement d'anomalie pour "{{ $l->produit->nom }}"
            </div>
            <div class="form-row" style="margin-bottom:0.5rem">
                <div class="form-group" style="margin:0">
                    <label>Type d'anomalie</label>
                    <select name="anomalie[{{ $l->id }}][type]">
                        <option value="quantite">Quantité incorrecte</option>
                        <option value="qualite">Qualité / produit abîmé</option>
                        <option value="emballage">Emballage endommagé</option>
                        <option value="retard">Retard de livraison</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Gravité</label>
                    <select name="anomalie[{{ $l->id }}][gravite]">
                        <option value="faible">Faible</option>
                        <option value="moyenne" selected>Moyenne</option>
                        <option value="elevee">Élevée</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin:0">
                <label>Description</label>
                <textarea name="anomalie[{{ $l->id }}][description]" rows="2" placeholder="Décrivez le problème..."></textarea>
            </div>
            <div class="form-group" style="margin:0.5rem 0 0">
                <label><i class="ri-camera-line"></i> Photo (optionnel)</label>
                <input type="file" name="anomalie[{{ $l->id }}][photo]" accept="image/*" capture="environment">
            </div>
        </div>
        @endforeach

        <div class="form-group mt-2">
            <label>Observations générales</label>
            <textarea name="observations" rows="3" placeholder="Remarques globales..."></textarea>
        </div>

        <div class="flex-gap" style="justify-content:flex-end">
            <a href="{{ route('reception.index') }}" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-success"><i class="ri-checkbox-circle-line"></i> Enregistrer la réception</button>
        </div>
    </form>
</div>

<script>
const totalLignes = {{ $lignes->count() }};

function verifierLigne(id, prevue, recue) {
    const ligne = document.getElementById('ligne-'+id);
    const status = document.getElementById('status-'+id);
    const anom = document.getElementById('anomalie-'+id);
    recue = parseFloat(recue);
    if (isNaN(recue)) return;
    if (recue >= prevue) {
        ligne.className = 'reception-ligne conforme-ligne';
        status.innerHTML = '<i class="ri-checkbox-circle-line" style="color:var(--vert)"></i>';
        anom.style.display = 'none';
    } else if (recue > 0) {
        ligne.className = 'reception-ligne anomalie-ligne';
        status.innerHTML = '<i class="ri-alert-line" style="color:var(--orange)"></i>';
        anom.style.display = 'block';
    } else {
        ligne.className = 'reception-ligne anomalie-ligne';
        status.innerHTML = '<i class="ri-close-circle-line" style="color:var(--rouge)"></i>';
        anom.style.display = 'block';
    }
    mettreAJourProgression();
}

function mettreAJourProgression() {
    const inputs = document.querySelectorAll('[name^="quantite_recue"]');
    let remplis = 0;
    inputs.forEach(i => { if (i.value !== '' && parseFloat(i.value) >= 0) remplis++; });
    const pct = totalLignes ? Math.round(remplis / totalLignes * 100) : 0;
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-txt').textContent = remplis + ' / ' + totalLignes + ' articles saisis';
}
</script>
@endif

@endsection