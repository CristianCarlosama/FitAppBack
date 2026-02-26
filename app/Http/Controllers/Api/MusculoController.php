<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Musculo;
use Illuminate\Http\Request;

class MusculoController extends Controller
{
    public function index() {
        return Musculo::orderBy('nombre', 'asc')->get();
    }
}