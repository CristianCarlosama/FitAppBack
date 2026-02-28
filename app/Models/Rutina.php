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

    public function ejercicios()
    {
        return $this->belongsToMany(Ejercicio::class, 'ejercicio_rutina')
                    ->withPivot('series', 'repeticiones', 'descanso') // <--- ¡IMPORTANTE!
                    ->withTimestamps();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function accesos()
    {
        return $this->belongsToMany(User::class, 'rutina_user');
    }

    public function calificaciones() {
        return $this->hasMany(CalificacionRutina::class);
    }
}