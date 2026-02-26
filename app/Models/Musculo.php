<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Musculo extends Model {
    protected $fillable = ['nombre'];

    public function ejercicios() {
        return $this->belongsToMany(Ejercicio::class)->withPivot('intensidad', 'es_principal');
    }
}