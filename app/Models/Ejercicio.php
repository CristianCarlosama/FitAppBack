<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejercicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'rutina_id',
        'nombre',
        'descripcion',
        'clase',
        'series',
        'repeticiones',
        'descanso',
        'video_url',
        'foto_1',
        'foto_2',
        'foto_3',
        'editable', 
    ];

    public function rutina() {
        return $this->belongsTo(Rutina::class);
    }

    public function calificaciones() {
        return $this->hasMany(CalificacionEjercicio::class);
    }
}