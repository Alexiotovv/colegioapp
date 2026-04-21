<?php
// app/Http/Controllers/PeriodoController.php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('anio_academico_id', 'desc')
            ->orderBy('orden')
            ->paginate(15);
        return view('periodos.index', compact('periodos'));
    }
    
    public function create()
    {
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        return view('periodos.create', compact('anios'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'anio_academico_id' => 'required|exists:anio_academicos,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'required|integer|min:1|max:4',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);
        
        // Verificar que no exista un periodo con el mismo orden en el mismo año
        $exists = Periodo::where('anio_academico_id', $request->anio_academico_id)
            ->where('orden', $request->orden)
            ->exists();
            
        if ($exists) {
            return back()->with('error', 'Ya existe un periodo con el orden ' . $request->orden . ' para este año académico')
                ->withInput();
        }
        
        Periodo::create([
            'anio_academico_id' => $request->anio_academico_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.periodos.index')
            ->with('success', 'Periodo académico creado exitosamente');
    }
    
    public function edit(Periodo $periodo)
    {
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        return view('periodos.edit', compact('periodo', 'anios'));
    }
    
    public function update(Request $request, Periodo $periodo)
    {
        $request->validate([
            'anio_academico_id' => 'required|exists:anio_academicos,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'required|integer|min:1|max:4',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);
        
        // Verificar que no exista otro periodo con el mismo orden
        $exists = Periodo::where('anio_academico_id', $request->anio_academico_id)
            ->where('orden', $request->orden)
            ->where('id', '!=', $periodo->id)
            ->exists();
            
        if ($exists) {
            return back()->with('error', 'Ya existe un periodo con el orden ' . $request->orden . ' para este año académico')
                ->withInput();
        }
        
        $periodo->update([
            'anio_academico_id' => $request->anio_academico_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.periodos.index')
            ->with('success', 'Periodo académico actualizado exitosamente');
    }
    
    public function destroy(Periodo $periodo)
    {
        // Verificar si tiene notas asociadas
        if ($periodo->notas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el periodo porque tiene notas registradas');
        }
        
        $periodo->delete();
        
        return redirect()->route('admin.periodos.index')
            ->with('success', 'Periodo académico eliminado exitosamente');
    }
    
    public function toggleActive(Periodo $periodo)
    {
        $periodo->update(['activo' => !$periodo->activo]);
        return back()->with('success', 'Estado del periodo actualizado');
    }
}