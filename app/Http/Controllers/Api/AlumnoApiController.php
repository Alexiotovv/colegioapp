<?php
// app/Http/Controllers/Api/AlumnoApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Apoderado;
use Illuminate\Http\Request;

class AlumnoApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:alumnos,dni',
            'nombres' => 'required|string|max:60',
            'apellido_paterno' => 'required|string|max:60',
            'apellido_materno' => 'required|string|max:60',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F',
        ]);
        
        $alumno = Alumno::create([
            'codigo_estudiante' => Alumno::generarCodigoEstudiante(),
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
        ]);
        
        // Asignar apoderados si se enviaron
        if ($request->has('apoderados')) {
            $alumno->apoderados()->sync($request->apoderados);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Alumno registrado exitosamente',
            'alumno' => $alumno->load('apoderados'),
            'alumno_nombre' => $alumno->nombre_completo
        ]);
    }
    
    public function search(Request $request)
    {
        $search = $request->get('q');
        $alumnos = Alumno::where('estado', 'activo')
            ->where(function($query) use ($search) {
                $query->where('nombres', 'LIKE', "%{$search}%")
                    ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
                    ->orWhere('apellido_materno', 'LIKE', "%{$search}%")
                    ->orWhere('dni', 'LIKE', "%{$search}%")
                    ->orWhere('codigo_estudiante', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();
        
        return response()->json($alumnos);
    }
}