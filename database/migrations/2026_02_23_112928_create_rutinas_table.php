<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('dificultad')->default('media');
            $table->integer('duracion')->nullable(); // minutos
            $table->integer('descanso')->nullable(); // segundos
            $table->boolean('es_publica')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rutinas');
    }
};