@extends('layouts.app')
@section('title', 'Gestion des utilisateurs')

@section('content')

<div class="flex-between mb-2">
    <form method="GET" class="filters" style="margin-bottom:0">
        <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}" onchange="this.form.submit()">
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('modal-ajouter').classList.add('open')">+ Nouveau compte</button>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody>
            @php $labels=['admin'=>'Administrateur','responsable'=>'Responsable','magasinier'=>'Magasinier']; $badges=['admin'=>'badge-anomalie','responsable'=>'badge-planif','magasinier'=>'badge-validee']; @endphp
            @forelse($utilisateurs as $u)
            <tr>
                <td><strong>{{ $u->prenom }} {{ $u->nom }}</strong> @if($u->id==auth()->id())<span style="color:var(--texte-clair);font-size:0.75rem">(vous)</span>@endif</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge {{ $badges[$u->role] }}">{{ $labels[$u->role] }}</span></td>
                <td><span class="badge {{ $u->actif?'badge-validee':'badge-anomalie' }}">{{ $u->actif?'Actif':'Inactif' }}</span></td>
                <td>{{ $u->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="flex-gap">
                        <button class="btn btn-outline btn-sm" onclick='ouvrirEdition(@json($u))'>Modifier</button>
                        @if($u->id != auth()->id())
                        <form method="POST" action="{{ route('utilisateurs.destroy', $u) }}" style="display:inline" onsubmit="return confirm('Changer le statut ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm {{ $u->actif?'btn-danger':'btn-success' }}">{{ $u->actif?'Désactiver':'Réactiver' }}</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--texte-clair)">Aucun utilisateur trouvé.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="modal-ajouter">
    <div class="modal">
        <div class="modal-header"><h3>Nouveau compte</h3><button class="modal-close" onclick="document.getElementById('modal-ajouter').classList.remove('open')">&times;</button></div>
        <form method="POST" action="{{ route('utilisateurs.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" required></div>
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" required></div>
                </div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Rôle *</label>
                        <select name="role"><option value="magasinier">Magasinier</option><option value="responsable">Responsable</option><option value="admin">Administrateur</option></select>
                    </div>
                    <div class="form-group"><label>Mot de passe *</label><input type="text" name="mot_de_passe" required minlength="6"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-ajouter').classList.remove('open')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer le compte</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modal-modifier">
    <div class="modal">
        <div class="modal-header"><h3>Modifier le compte</h3><button class="modal-close" onclick="document.getElementById('modal-modifier').classList.remove('open')">&times;</button></div>
        <form method="POST" id="form-edit-user">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" id="edit-prenom" required></div>
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" id="edit-nom" required></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" id="edit-email" disabled style="background:var(--gris-pale)"></div>
                <div class="form-group"><label>Rôle *</label>
                    <select name="role" id="edit-role"><option value="magasinier">Magasinier</option><option value="responsable">Responsable</option><option value="admin">Administrateur</option></select>
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
function ouvrirEdition(u) {
    document.getElementById('form-edit-user').action = `/utilisateurs/${u.id}`;
    document.getElementById('edit-prenom').value = u.prenom;
    document.getElementById('edit-nom').value = u.nom;
    document.getElementById('edit-email').value = u.email;
    document.getElementById('edit-role').value = u.role;
    document.getElementById('modal-modifier').classList.add('open');
}
document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
</script>

@endsection