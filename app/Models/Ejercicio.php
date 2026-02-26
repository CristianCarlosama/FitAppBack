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
    ];

    public function musculos() {
        return $this->belongsToMany(Musculo::class)
                    ->withPivot('intensidad', 'es_principal')
                    ->withTimestamps();
    }

    protected $appends = ['clase', 'musculos_secundarios'];

    public function getClaseAttribute() {
        $principal = $this->musculos->where('pivot.es_principal', true)->first();
        return $principal ? $principal->nombre : 'Sin definir';
    }

    public function getMusculosSecundariosAttribute() {
        return $this->musculos->where('pivot.es_principal', false)->map(function($m) {
            return [
                'nombre' => $m->nombre,
                'intensidad' => $m->pivot->intensidad
            ];
        })->values();
    }

    public function rutina() {
        return $this->belongsTo(Rutina::class);
    }

    public function calificaciones() {
        return $this->hasMany(CalificacionEjercicio::class);
    }
}