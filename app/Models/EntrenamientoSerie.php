<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Entrenamiento; 
use App\Models\Ejercicio;

class EntrenamientoSerie extends Model
{
    protected $table = 'entrenamiento_series';

    protected $fillable = [
        'entrenamiento_id', 
        'ejercicio_id', 
        'numero_serie', 
        'peso', 
        'reps', 
        'rpe'
    ];

    public function entrenamiento(): BelongsTo
    {
        return $this->belongsTo(Entrenamiento::class);
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class);
    }
}