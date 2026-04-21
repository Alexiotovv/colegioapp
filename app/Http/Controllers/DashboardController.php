<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Matricula;
use App\Models\Nota;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_alumnos' => \App\Models\Alumno::count(),
            'total_docentes' => \App\Models\Docente::count(),
            'total_matriculas' => Matricula::where('estado', 'activa')->count(),
            'total_notas' => Nota::count(),
        ];
        
        $notas_recientes = Nota::with(['matricula.alumno', 'competencia.curso'])
            ->latest()
            ->limit(10)
            ->get();
            
        $matriculas_recientes = Matricula::with(['alumno'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('dashboard.index', compact('stats', 'notas_recientes', 'matriculas_recientes'));
    }
}