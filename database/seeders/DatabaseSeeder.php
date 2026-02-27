<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Aquí es donde debes llamar a tus otros seeders (los que sí te sirven)
        $this->call([
            MusculoSeeder::class,
            // Agrega aquí el de Ejercicios o Rutinas si los tienes aparte
        ]);
    }
}
