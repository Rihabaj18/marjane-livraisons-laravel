@extends('layouts.app')
@section('title', 'Fournisseurs')

@section('content')

<div class="flex-between mb-2">
    <form method="GET" class="filters" style="margin-bottom:0">
        <input type="text" name="search" placeholder="Rechercher un fournisseur..." value="{{ request('search') }}" onchange="this.form.submit()">
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('modal-ajouter').classList.add('open')">+ Nouveau fournisseur</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Nom</th><th>Contact</th><th>Téléphone</th><th>Email</th><th>Commandes</th><th>Taux conformité</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($fournisseurs as $f)
            <tr>
                <td><strong>{{ $f->nom }}</strong></td>
                <td>{{ $f->contact ?: '—' }}</td>
                <td>{{ $f->telephone ?: '—' }}</td>
                <td>{{ $f->email ?: '—' }}</td>
                <td class="text-center">{{ $f->total_cmds }}</td>
                <td class="text-center">
                    @if($f->taux !== null)
                        <strong style="color:{{ $f->taux >= 80 ? 'var(--vert)' : ($f->taux >= 50 ? 'var(--orange)' : 'var(--rouge)') }}">{{ $f->taux }}%</strong>
                    @else
                        <span style="color:var(--texte-clair)">—</span>
                    @endif
                </td>
                <td><span class="badge {{ $f->actif ? 'badge-validee' : 'badge-anomalie' }}">{{ $f->actif ? 'Actif' : 'Inactif' }}</span></td>
                <td>
                    <div class="flex-gap">
                        <button class="btn btn-outline btn-sm" onclick='ouvrirEdition(@json($f))'>Modifier</button>
                        <form method="POST" action="{{ route('fournisseurs.destroy', $f) }}" style="display:inline" onsubmit="return confirm('Changer le statut ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm {{ $f->actif ? 'btn-danger' : 'btn-success' }}">{{ $f->actif ? 'Désactiver' : 'Réactiver' }}</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucun fournisseur trouvé.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="modal-ajouter">
    <div class="modal">
        <div class="modal-header"><h3>Nouveau fournisseur</h3><button class="modal-close" onclick="document.getElementById('modal-ajouter').classList.remove('open')">&times;</button></div>
        <form method="POST" action="{{ route('fournisseurs.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label>Nom de la société *</label><input type="text" name="nom" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Contact</label><input type="text" name="contact"></div>
                    <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone"></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group"><label>Adresse</label><textarea name="adresse" rows="2"></textarea></div>
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
        <div class="modal-header"><h3>Modifier le fournisseur</h3><button class="modal-close" onclick="document.getElementById('modal-modifier').classList.remove('open')">&times;</button></div>
        <form method="POST" id="form-edit-fournisseur">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label>Nom *</label><input type="text" name="nom" id="edit-nom" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Contact</label><input type="text" name="contact" id="edit-contact"></div>
                    <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" id="edit-telephone"></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="edit-email"></div>
                <div class="form-group"><label>Adresse</label><textarea name="adresse" id="edit-adresse" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-modifier').classList.remove('open')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function ouvrirEdition(f) {
    document.getElementById('form-edit-fournisseur').action = `/fournisseurs/${f.id}`;
    document.getElementById('edit-nom').value = f.nom || '';
    document.getElementById('edit-contact').value = f.contact || '';
    document.getElementById('edit-telephone').value = f.telephone || '';
    document.getElementById('edit-email').value = f.email || '';
    document.getElementById('edit-adresse').value = f.adresse || '';
    document.getElementById('modal-modifier').classList.add('open');
}
document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
</script>

@endsection