<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MusculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $musculos = ['Pecho', 'Espalda', 'Piernas', 'Hombros', 'Brazos', 'Abdomen', 'Pantorrilla'];
        foreach ($musculos as $m) {
            \App\Models\Musculo::create(['nombre' => $m]);
        }
    }
}
