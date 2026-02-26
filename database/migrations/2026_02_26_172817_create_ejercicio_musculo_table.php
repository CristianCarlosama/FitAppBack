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
        Schema::create('ejercicio_musculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->onDelete('cascade');
            $table->foreignId('musculo_id')->constrained('musculos')->onDelete('cascade');
            
            // Atributos adicionales de la relación
            $table->enum('intensidad', ['Alto', 'Medio', 'Bajo'])->default('Medio');
            $table->boolean('es_principal')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejercicio_musculo');
    }
};
