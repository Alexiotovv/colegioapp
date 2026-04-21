<?php
// app/Http/Controllers/AnioAcademicoController.php

namespace App\Http\Controllers;

use App\Models\AnioAcademico;
use Illuminate\Http\Request;

class AnioAcademicoController extends Controller
{

    public function index()
    {
        $anios = AnioAcademico::orderBy('anio', 'desc')->paginate(10);
        return view('anios_academicos.index', compact('anios'));
    }
    
    public function create()
    {
        return view('anios_academicos.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|string|size:4|unique:anio_academicos,anio',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);
        
        // Si este nuevo año será activo, desactivar los demás
        if ($request->activo) {
            AnioAcademico::where('activo', true)->update(['activo' => false]);
        }
        
        AnioAcademico::create([
            'anio' => $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.anios.index')
            ->with('success', 'Año académico creado exitosamente');
    }
    
    public function edit(AnioAcademico $anio)
    {
        return view('anios_academicos.edit', compact('anio'));
    }
    
    public function update(Request $request, AnioAcademico $anio)
    {
        $request->validate([
            'anio' => 'required|string|size:4|unique:anio_academicos,anio,' . $anio->id,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);
        
        // Si este año será activo, desactivar los demás
        if ($request->activo && !$anio->activo) {
            AnioAcademico::where('activo', true)->update(['activo' => false]);
        }
        
        $anio->update([
            'anio' => $request->anio,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'activo' => $request->activo ?? false,
        ]);
        
        return redirect()->route('admin.anios.index')
            ->with('success', 'Año académico actualizado exitosamente');
    }
    
    public function destroy(AnioAcademico $anio)
    {
        // No permitir eliminar el año activo
        if ($anio->activo) {
            return back()->with('error', 'No se puede eliminar el año académico activo');
        }
        
        $anio->delete();
        
        return redirect()->route('admin.anios.index')
            ->with('success', 'Año académico eliminado exitosamente');
    }
    
    public function setActivo(AnioAcademico $anio)
    {
        // Desactivar todos los años
        AnioAcademico::where('activo', true)->update(['activo' => false]);
        
        // Activar el año seleccionado
        $anio->update(['activo' => true]);
        
        return redirect()->route('admin.anios.index')
            ->with('success', 'Año académico activado correctamente');
    }
}