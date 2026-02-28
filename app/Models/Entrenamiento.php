<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; 
use App\Models\EntrenamientoSerie; 
use App\Models\Ejercicio; 

class Entrenamiento extends Model
{
    protected $fillable = [
        'user_id',
        'rutina_id',
        'fecha_inicio',
        'fecha_fin',
        'notas_sesion'
    ];
    // ----------------------------

    public function usuario() {
        return $this->belongsTo(User::class);
    }
    
    public function getDuracionAttribute() {
        return $this->fecha_inicio->diffInMinutes($this->fecha_fin);
    }
    
    public function series() {
        return $this->hasMany(EntrenamientoSerie::class, 'entrenamiento_id');
    }

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function ejercicios()
    {
        return $this->belongsToMany(Ejercicio::class, 'entrenamiento_series')
                    ->withPivot('peso', 'reps', 'numero_serie', 'rpe')
                    ->withTimestamps();
    }
}