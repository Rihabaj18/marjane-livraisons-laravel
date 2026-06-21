<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes');
            $table->foreignId('magasinier_id')->constrained('users');
            $table->enum('statut', ['conforme','anomalie','partielle'])->default('conforme');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('receptions');
    }
};