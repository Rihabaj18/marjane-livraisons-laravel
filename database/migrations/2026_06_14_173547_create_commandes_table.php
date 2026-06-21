<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->foreignId('responsable_id')->constrained('users');
            $table->date('date_prevue');
            $table->time('creneau_debut')->nullable();
            $table->time('creneau_fin')->nullable();
            $table->enum('statut', ['en_attente','planifiee','recue','validee','anomalie'])
                  ->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('commandes');
    }
};