<?php
// app/Http/Controllers/MatriculaController.php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\Aula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;

class MatriculaController extends Controller
{
    public function index(Request $request)
    {
        $query = Matricula::with(['alumno', 'aula.grado', 'aula.seccion', 'aula.anioAcademico', 'apoderado']);
        
        if ($request->filled('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }
        
        if ($request->filled('anio_academico_id')) {
            $query->whereHas('aula', function($q) use ($request) {
                $q->where('anio_academico_id', $request->anio_academico_id);
            });
        }
        
        if ($request->filled('search')) {
            $query->whereHas('alumno', function($q) use ($request) {
                $q->search($request->search);
            });
        }
        
        $matriculas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                    ->where('activo', true)
                    ->orderBy('anio_academico_id', 'desc')
                    ->orderBy('nivel_id')
                    ->orderBy('grado_id')
                    ->get();
        
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('matriculas.index', compact('matriculas', 'aulas', 'anios', 'anioActivo'));
    }
    
    public function create()
    {
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
                     ->where('activo', true)
                     ->orderBy('anio_academico_id', 'desc')
                     ->orderBy('nivel_id')
                     ->orderBy('grado_id')
                     ->orderBy('seccion_id')
                     ->get();
        
        $alumnos = Alumno::where('estado', 'activo')->orderBy('apellido_paterno')->get();
        $apoderados = Apoderado::orderBy('apellido_paterno')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('matriculas.create', compact('aulas', 'alumnos', 'apoderados', 'anioActivo'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'aula_id' => 'required|exists:aulas,id',
            'fecha_matricula' => 'required|date',
        ]);
        
        // Obtener el aula para verificar el año académico
        $aula = Aula::find($request->aula_id);
        
        if (!$aula) {
            return response()->json([
                'success' => false,
                'message' => 'El aula seleccionada no existe'
            ], 422);
        }
        
        // Verificar si el alumno ya está matriculado en el mismo año
        $existe = Matricula::where('alumno_id', $request->alumno_id)
            ->whereHas('aula', function($q) use ($aula) {
                $q->where('anio_academico_id', $aula->anio_academico_id);
            })
            ->where('estado', 'activa')
            ->exists();
            
        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'El alumno ya tiene una matrícula activa para este año académico'
            ], 422);
        }
        
        $matricula = Matricula::create([
            'alumno_id' => $request->alumno_id,
            'apoderado_id' => $request->apoderado_id,
            'aula_id' => $request->aula_id,
            'fecha_matricula' => $request->fecha_matricula,
            'estado' => 'activa',
            'observaciones' => $request->observaciones,
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Matrícula registrada exitosamente',
                'matricula' => $matricula->load(['alumno', 'aula.grado', 'aula.seccion', 'aula.anioAcademico'])
            ]);
        }
        
        return redirect()->route('admin.matriculas.index')
            ->with('success', 'Matrícula registrada exitosamente');
    }
    
    public function edit(Matricula $matricula)
    {
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
                     ->where('activo', true)
                     ->orderBy('anio_academico_id', 'desc')
                     ->orderBy('nivel_id')
                     ->orderBy('grado_id')
                     ->get();
        
        $apoderados = Apoderado::orderBy('apellido_paterno')->get();
        
        return view('matriculas.edit', compact('matricula', 'aulas', 'apoderados'));
    }
    
    public function update(Request $request, Matricula $matricula)
    {
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
            'estado' => 'required|in:activa,retirada,culminada',
        ]);
        
        $matricula->update([
            'aula_id' => $request->aula_id,
            'apoderado_id' => $request->apoderado_id,
            'fecha_matricula' => $request->fecha_matricula,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Matrícula actualizada exitosamente',
                'matricula' => $matricula
            ]);
        }
        
        return redirect()->route('admin.matriculas.index')
            ->with('success', 'Matrícula actualizada exitosamente');
    }
    
    public function destroy(Matricula $matricula)
    {
        if ($matricula->notas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la matrícula porque tiene notas registradas');
        }
        
        $matricula->delete();
        
        return redirect()->route('admin.matriculas.index')
            ->with('success', 'Matrícula eliminada exitosamente');
    }
    
    public function show(Matricula $matricula)
    {
        $matricula->load(['alumno', 'apoderado', 'aula.grado.nivel', 'aula.seccion', 'aula.anioAcademico', 'aula.docente']);
        return view('matriculas.show', compact('matricula'));
    }
    
    // Método para obtener aulas por filtros (AJAX)
    public function getAulasByFilters(Request $request)
    {
        $query = Aula::with(['grado.nivel', 'seccion', 'anioAcademico']);
        
        if ($request->filled('nivel_id')) {
            $query->where('nivel_id', $request->nivel_id);
        }
        
        if ($request->filled('grado_id')) {
            $query->where('grado_id', $request->grado_id);
        }
        
        if ($request->filled('anio_academico_id')) {
            $query->where('anio_academico_id', $request->anio_academico_id);
        }
        
        $aulas = $query->where('activo', true)->orderBy('nombre')->get();
        
        return response()->json($aulas);
    }
}