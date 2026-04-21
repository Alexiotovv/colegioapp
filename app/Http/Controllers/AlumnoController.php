<?php
// app/Http/Controllers/AlumnoController.php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Apoderado;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumno::query();
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        $alumnos = $query->orderBy('apellido_paterno')
                         ->orderBy('apellido_materno')
                         ->orderBy('nombres')
                         ->paginate(15)
                         ->withQueryString();
        
        $estados = Alumno::ESTADOS;
        
        return view('alumnos.index', compact('alumnos', 'estados'));
    }
    
    public function create()
    {
        $apoderados = Apoderado::orderBy('apellido_paterno')->get();
        $codigoGenerado = Alumno::generarCodigoEstudiante();
        return view('alumnos.create', compact('apoderados', 'codigoGenerado'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:alumnos,dni',
            'nombres' => 'required|string|max:60',
            'apellido_paterno' => 'required|string|max:60',
            'apellido_materno' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'apoderados' => 'array',
            'apoderados.*' => 'exists:apoderados,id',
        ]);
        
        $alumno = Alumno::create([
            'codigo_estudiante' => $request->codigo_estudiante ?? Alumno::generarCodigoEstudiante(),
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estado' => 'activo',
            'observaciones' => $request->observaciones,
        ]);
        
        // Asignar apoderados
        if ($request->has('apoderados')) {
            $alumno->apoderados()->sync($request->apoderados);
        }
        
        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno registrado exitosamente');
    }
    
    public function show(Alumno $alumno)
    {
        // 🔥 CORREGIDO - Cargar las relaciones correctas
        $alumno->load([
            'apoderados', 
            'matriculas.aula.grado', 
            'matriculas.aula.seccion', 
            'matriculas.aula.anioAcademico'
        ]);
        
        return view('alumnos.show', compact('alumno'));
    }
    
    public function edit(Alumno $alumno)
    {
        $apoderados = Apoderado::orderBy('apellido_paterno')
                            ->orderBy('apellido_materno')
                            ->get();
        $apoderadosSeleccionados = $alumno->apoderados->pluck('id')->toArray();
        
        return view('alumnos.edit', compact('alumno', 'apoderados', 'apoderadosSeleccionados'));
    }
    
    public function update(Request $request, Alumno $alumno)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:alumnos,dni,' . $alumno->id,
            'nombres' => 'required|string|max:60',
            'apellido_paterno' => 'required|string|max:60',
            'apellido_materno' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
            'estado' => 'required|in:activo,inactivo,retirado,egresado',
            'apoderados' => 'array',
            'apoderados.*' => 'exists:apoderados,id',
        ]);
        
        $alumno->update([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);
        
        // Actualizar apoderados
        if ($request->has('apoderados')) {
            $alumno->apoderados()->sync($request->apoderados);
        } else {
            $alumno->apoderados()->sync([]);
        }
        
        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno actualizado exitosamente');
    }
    
    public function destroy(Alumno $alumno)
    {
        // Verificar si tiene matrículas
        if ($alumno->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el alumno porque tiene matrículas asociadas');
        }
        
        $alumno->delete();
        
        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno eliminado exitosamente');
    }
    
    public function changeStatus(Alumno $alumno, $estado)
    {
        if (!array_key_exists($estado, Alumno::ESTADOS)) {
            return back()->with('error', 'Estado no válido');
        }
        
        $alumno->update(['estado' => $estado]);
        
        return back()->with('success', 'Estado del alumno actualizado a ' . Alumno::ESTADOS[$estado]);
    }
}