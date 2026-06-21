<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\AnomalieController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RapportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reception', [ReceptionController::class, 'index'])->name('reception.index');
    Route::post('/reception', [ReceptionController::class, 'store'])->name('reception.store');

    Route::get('/anomalies', [AnomalieController::class, 'index'])->name('anomalies.index');
    Route::post('/anomalies/{anomalie}/statut', [AnomalieController::class, 'updateStatut'])->name('anomalies.statut');

    Route::middleware('can:gerer-commandes')->group(function () {
        Route::resource('commandes', CommandeController::class)->only(['index', 'store', 'show']);
        Route::resource('fournisseurs', FournisseurController::class)->except(['create', 'edit', 'show']);
        Route::resource('produits', ProduitController::class)->except(['create', 'edit', 'show']);
        Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('/rapports/export', [RapportController::class, 'export'])->name('rapports.export');
    });

    Route::middleware('can:gerer-utilisateurs')->group(function () {
        Route::resource('utilisateurs', \App\Http\Controllers\UtilisateurController::class)->except(['create', 'edit', 'show']);
    });

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';