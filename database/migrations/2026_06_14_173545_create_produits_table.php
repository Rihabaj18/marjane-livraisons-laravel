<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('nom');
            $table->string('unite')->default('unité');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('produits');
    }
};