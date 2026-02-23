<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rutina_id')->nullable()->constrained('rutinas')->onDelete('cascade'); // nullable: puede ser suelto
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('clase'); // Pierna, Pecho, etc.
            $table->index('clase');  // índice para acelerar búsquedas
            $table->integer('series')->nullable();
            $table->integer('repeticiones')->nullable();
            $table->integer('descanso')->nullable(); // segundos
            $table->string('video_url')->nullable();
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->boolean('editable')->default(false); // false = solo app puede cambiarlo
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ejercicios');
    }
};