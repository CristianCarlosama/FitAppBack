<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedidaCorporal extends Model
{
    use HasFactory;

    protected $table = 'medidas_corporales';

    protected $fillable = [
        'usuario_id',
        'peso',
        'pecho',
        'cintura',
        'brazo',
        'pierna'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}