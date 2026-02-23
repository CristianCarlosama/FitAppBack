<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medidas_corporales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->float('peso')->nullable();
            $table->float('pecho')->nullable();
            $table->float('cintura')->nullable();
            $table->float('brazo')->nullable();
            $table->float('pierna')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('medidas_corporales');
    }
};