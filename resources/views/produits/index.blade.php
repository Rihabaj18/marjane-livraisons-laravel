@extends('layouts.app')
@section('title', 'Catalogue produits')

@section('content')

<div class="flex-between mb-2">
    <form method="GET" class="filters" style="margin-bottom:0">
        <input type="text" name="search" placeholder="Rechercher par nom ou référence..." value="{{ request('search') }}" onchange="this.form.submit()">
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('modal-ajouter').classList.add('open')">+ Nouveau produit</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Référence</th><th>Nom du produit</th><th>Unité</th><th>Commandes</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($produits as $p)
            <tr>
                <td><span style="font-family:monospace;background:var(--gris-pale);padding:2px 8px;border-radius:4px;font-size:0.85rem">{{ $p->reference }}</span></td>
                <td><strong>{{ $p->nom }}</strong></td>
                <td>{{ $p->unite }}</td>
                <td class="text-center">
                    @if($p->nb_commandes > 0)
                        <span class="badge badge-planif">{{ $p->nb_commandes }}</span>
                    @else
                        <span style="color:var(--texte-clair)">0</span>
                    @endif
                </td>
                <td>
                    <div class="flex-gap">
                        <button class="btn btn-outline btn-sm" onclick='ouvrirEdition(@json($p))'>Modifier</button>
                        @if($p->nb_commandes == 0)
                        <form method="POST" action="{{ route('produits.destroy', $p) }}" style="display:inline" onsubmit="return confirm('Supprimer ce produit ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucun produit trouvé.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="modal-ajouter">
    <div class="modal">
        <div class="modal-header"><h3>Nouveau produit</h3><button class="modal-close" onclick="document.getElementById('modal-ajouter').classList.remove('open')">&times;</button></div>
        <form method="POST" action="{{ route('produits.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label>Référence *</label><input type="text" name="reference" required style="text-transform:uppercase"></div>
                    <div class="form-group"><label>Unité</label>
                        <select name="unite">@foreach($unites as $u)<option value="{{ $u }}">{{ ucfirst($u) }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="form-group"><label>Nom du produit *</label><input type="text" name="nom" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-ajouter').classList.remove('open')">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modal-modifier">
    <div class="modal">
        <div class="modal-header"><h3>Modifier le produit</h3><button class="modal-close" onclick="document.getElementById('modal-modifier').classList.remove('open')">&times;</button></div>
        <form method="POST" id="form-edit-produit">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label>Référence</label><input type="text" id="edit-ref" disabled style="background:var(--gris-pale)"></div>
                <div class="form-row">
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" id="edit-nom" required></div>
                    <div class="form-group"><label>Unité</label>
                        <select name="unite" id="edit-unite">@foreach($unites as $u)<option value="{{ $u }}">{{ ucfirst($u) }}</option>@endforeach</select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-modifier').classList.remove('open')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function ouvrirEdition(p) {
    document.getElementById('form-edit-produit').action = `/produits/${p.id}`;
    document.getElementById('edit-ref').value = p.reference;
    document.getElementById('edit-nom').value = p.nom;
    document.getElementById('edit-unite').value = p.unite;
    document.getElementById('modal-modifier').classList.add('open');
}
document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
</script>

@endsection