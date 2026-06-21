<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lignes_reception', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained('receptions')->cascadeOnDelete();
            $table->foreignId('ligne_commande_id')->constrained('lignes_commande');
            $table->decimal('quantite_recue', 10, 2);
            $table->boolean('conforme')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lignes_reception');
    }
};