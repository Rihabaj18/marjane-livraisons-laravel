<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marjane — @yield('title', 'Suivi des livraisons')</title>

    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-circle">M</div>
        <div>
            <strong>Marjane</strong>
            <small>Livraisons</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ri-dashboard-line"></i> Tableau de bord
        </a>
        <a href="{{ route('reception.index') }}" class="nav-item {{ request()->routeIs('reception.*') ? 'active' : '' }}">
            <i class="ri-archive-line"></i> Réception quai
        </a>
        <a href="{{ route('anomalies.index') }}" class="nav-item {{ request()->routeIs('anomalies.*') ? 'active' : '' }}">
            <i class="ri-alert-line"></i> Anomalies
        </a>

        @canany(['gerer-commandes'])
        <div class="nav-separator">Gestion</div>
        <a href="{{ route('commandes.index') }}" class="nav-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <i class="ri-file-list-3-line"></i> Commandes
        </a>
        <a href="{{ route('fournisseurs.index') }}" class="nav-item {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}">
            <i class="ri-store-2-line"></i> Fournisseurs
        </a>
        <a href="{{ route('produits.index') }}" class="nav-item {{ request()->routeIs('produits.*') ? 'active' : '' }}">
            <i class="ri-box-3-line"></i> Produits
        </a>
        <a href="{{ route('rapports.index') }}" class="nav-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
            <i class="ri-bar-chart-line"></i> Rapports
        </a>
        @endcanany

        @can('gerer-utilisateurs')
        <a href="{{ route('utilisateurs.index') }}" class="nav-item {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
            <i class="ri-team-line"></i> Utilisateurs
        </a>
        @endcan
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->prenom, 0, 1)) }}</div>
        <div class="user-info">
            <strong>{{ auth()->user()->nomComplet() }}</strong>
            <small>{{ ucfirst(auth()->user()->role) }}</small>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout" title="Déconnexion">
                <i class="ri-logout-box-r-line"></i>
            </button>
        </form>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="ri-menu-line"></i>
        </button>
        <h1 class="page-title">@yield('title', 'Tableau de bord')</h1>
    </header>

    <div class="page-body">
        @if (session('success'))
            <div class="alert alert-success"><i class="ri-checkbox-circle-line"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>
</main>
<!-- ===== BOTTOM NAV MOBILE ===== -->
<nav class="bottom-nav" role="navigation" aria-label="Navigation mobile">
    <div class="bottom-nav-inner">

        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="bn-icon"><i class="ri-dashboard-line"></i></span>
            <span>Accueil</span>
        </a>

        <a href="{{ route('reception.index') }}" class="bottom-nav-item {{ request()->routeIs('reception.*') ? 'active' : '' }}">
            <span class="bn-icon"><i class="ri-archive-line"></i></span>
            <span>Réception</span>
        </a>

        <a href="{{ route('anomalies.index') }}" class="bottom-nav-item {{ request()->routeIs('anomalies.*') ? 'active' : '' }}">
            <span class="bn-icon"><i class="ri-alert-line"></i></span>
            @php $nbOpen = \App\Models\Anomalie::where('statut', 'ouverte')->count(); @endphp
            @if($nbOpen > 0)
                <span class="bn-badge">{{ $nbOpen }}</span>
            @endif
            <span>Anomalies</span>
        </a>

        @can('gerer-commandes')
        <a href="{{ route('commandes.index') }}" class="bottom-nav-item {{ request()->routeIs('commandes.*') ? 'active' : '' }}">
            <span class="bn-icon"><i class="ri-file-list-3-line"></i></span>
            <span>Commandes</span>
        </a>

        <a href="{{ route('rapports.index') }}" class="bottom-nav-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
            <span class="bn-icon"><i class="ri-bar-chart-line"></i></span>
            <span>Rapports</span>
        </a>
        @else
        <form method="POST" action="{{ route('logout') }}" class="bottom-nav-item" style="border:none;background:none;padding:0">
            @csrf
            <button type="submit" style="background:none;border:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:3px;padding:0.55rem 0.25rem;width:100%;font-size:0.65rem;cursor:pointer">
                <span class="bn-icon"><i class="ri-logout-box-r-line"></i></span>
                <span>Quitter</span>
            </button>
        </form>
        @endcan

    </div>
</nav>
<!-- ===== FIN BOTTOM NAV ===== -->
</body>
</html>