@extends('layouts.app')
@section('title', 'Commandes fournisseurs')

@section('content')

<div class="flex-between mb-2">
    <form method="GET" class="filters" style="margin-bottom:0">
        <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}" onchange="this.form.submit()">
        <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()">
        <select name="statut" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="en_attente" {{ request('statut')==='en_attente'?'selected':'' }}>En attente</option>
            <option value="planifiee" {{ request('statut')==='planifiee'?'selected':'' }}>Planifiée</option>
            <option value="recue" {{ request('statut')==='recue'?'selected':'' }}>Reçue</option>
            <option value="validee" {{ request('statut')==='validee'?'selected':'' }}>Validée</option>
            <option value="anomalie" {{ request('statut')==='anomalie'?'selected':'' }}>Anomalie</option>
        </select>
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('modal-nouvelle').classList.add('open')">+ Nouvelle commande</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>N°</th><th>Fournisseur</th><th>Date prévue</th><th>Créneau</th><th>Articles</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($commandes as $c)
            @php
                $map = ['en_attente'=>['attente','En attente'],'planifiee'=>['planif','Planifiée'],'recue'=>['recue','Reçue'],'validee'=>['validee','Validée'],'anomalie'=>['anomalie','Anomalie']];
                [$cls, $txt] = $map[$c->statut] ?? ['attente', $c->statut];
            @endphp
            <tr>
                <td><strong>{{ $c->numero }}</strong></td>
                <td>{{ $c->fournisseur->nom }}</td>
                <td>{{ $c->date_prevue->format('d/m/Y') }}</td>
                <td>{{ $c->creneau_debut ? \Carbon\Carbon::parse($c->creneau_debut)->format('H:i').' → '.\Carbon\Carbon::parse($c->creneau_fin)->format('H:i') : '—' }}</td>
                <td class="text-center">{{ $c->nb_articles }}</td>
                <td><span class="badge badge-{{ $cls }}">{{ $txt }}</span></td>
                <td>
                    <div class="flex-gap">
                        @if(in_array($c->statut, ['planifiee','en_attente']))
                        <a href="{{ route('reception.index', ['commande_id'=>$c->id]) }}" class="btn btn-jaune btn-sm">Réceptionner</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucune commande trouvée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="modal-nouvelle">
    <div class="modal">
        <div class="modal-header">
            <h3>Nouvelle commande fournisseur</h3>
            <button class="modal-close" onclick="document.getElementById('modal-nouvelle').classList.remove('open')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('commandes.store') }}" id="form-commande">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>Fournisseur *</label>
                        <select name="fournisseur_id" required>
                            <option value="">-- Choisir --</option>
                            @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}">{{ $f->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date de livraison *</label>
                        <input type="date" name="date_prevue" required min="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Créneau — début</label><input type="time" name="creneau_debut"></div>
                    <div class="form-group"><label>Créneau — fin</label><input type="time" name="creneau_fin"></div>
                </div>
                <div class="form-group"><label>Notes</label><textarea name="notes"></textarea></div>

                <div style="border-top:1px solid var(--gris-bord);padding-top:1rem">
                    <div class="flex-between mb-1">
                        <strong style="font-size:0.9rem">Articles commandés</strong>
                        <button type="button" class="btn btn-outline btn-sm" onclick="ajouterLigne()">+ Ajouter un article</button>
                    </div>
                    <div id="lignes-container"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="document.getElementById('modal-nouvelle').classList.remove('open')">Annuler</button>
            <button class="btn btn-primary" onclick="document.getElementById('form-commande').submit()">Créer la commande</button>
        </div>
    </div>
</div>

<script>
const produits = @json($produits);
function ajouterLigne() {
    const c = document.getElementById('lignes-container');
    const opts = produits.map(p => `<option value="${p.id}">${p.nom} (${p.unite})</option>`).join('');
    c.insertAdjacentHTML('beforeend', `
      <div class="flex-gap mb-1">
        <select name="produit_id[]" style="flex:2;padding:0.45rem;border:1px solid var(--gris-bord);border-radius:var(--radius)">
          <option value="">-- Produit --</option>${opts}
        </select>
        <input type="number" name="quantite[]" placeholder="Qté" min="0" step="0.01" style="width:80px;padding:0.45rem;border:1px solid var(--gris-bord);border-radius:var(--radius)">
        <input type="number" name="prix_unitaire[]" placeholder="Prix (MAD)" min="0" step="0.01" style="width:120px;padding:0.45rem;border:1px solid var(--gris-bord);border-radius:var(--radius)">
        <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--rouge);font-size:1.2rem">&times;</button>
      </div>`);
}
document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
</script>

@endsection