<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 
        'descripcion', 
        'dificultad', 
        'duracion', 
        'es_publica', 
        'user_id'];

    public function ejercicios() {
        return $this->belongsToMany(Ejercicio::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function calificaciones() {
        return $this->hasMany(CalificacionRutina::class);
    }
}