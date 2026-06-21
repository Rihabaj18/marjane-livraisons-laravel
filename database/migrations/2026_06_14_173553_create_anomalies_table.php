<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained('receptions');
            $table->enum('type', ['quantite','qualite','emballage','retard','autre']);
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->enum('gravite', ['faible','moyenne','elevee'])->default('moyenne');
            $table->enum('statut', ['ouverte','en_cours','resolue'])->default('ouverte');
            $table->foreignId('resolu_par')->nullable()->constrained('users');
            $table->timestamp('resolu_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('anomalies');
    }
};