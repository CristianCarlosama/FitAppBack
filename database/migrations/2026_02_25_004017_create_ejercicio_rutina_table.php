<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ejercicio_rutina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rutina_id')->constrained()->onDelete('cascade');
            $table->foreignId('ejercicio_id')->constrained()->onDelete('cascade');
            
            // CAMPOS CLAVE: Aquí es donde vivirá el 4x12 o 3x10
            $table->integer('series')->default(0);
            $table->integer('repeticiones')->default(0);
            $table->integer('descanso')->default(60); // en segundos
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejercicio_rutina');
    }
};
