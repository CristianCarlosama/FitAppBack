<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entrenamiento_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrenamiento_id')->constrained()->onDelete('cascade');
            $table->foreignId('ejercicio_id')->constrained()->onDelete('cascade');
            
            $table->integer('numero_serie'); // 1, 2, 3...
            $table->decimal('peso', 8, 2);   // El peso de ESA serie
            $table->integer('reps');         // Las reps de ESA serie
            $table->integer('rpe')->nullable(); // Esfuerzo percibido (1-10)
            
            $table->boolean('es_personal_record')->default(false); // Para celebrar si rompió récord
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrenamiento_series');
    }
};
