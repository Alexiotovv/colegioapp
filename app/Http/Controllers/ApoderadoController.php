<?php
// app/Http/Controllers/ApoderadoController.php

namespace App\Http\Controllers;

use App\Models\Apoderado;
use App\Models\Alumno;
use Illuminate\Http\Request;

class ApoderadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Apoderado::query();
        
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        if ($request->filled('parentesco')) {
            $query->where('parentesco', $request->parentesco);
        }
        
        $apoderados = $query->orderBy('apellido_paterno')
                            ->orderBy('apellido_materno')
                            ->paginate(15)
                            ->withQueryString();
        
        $parentescos = Apoderado::PARENTESCOS;
        
        return view('apoderados.index', compact('apoderados', 'parentescos'));
    }
    
    public function create()
    {
        $alumnos = Alumno::where('estado', 'activo')->orderBy('apellido_paterno')->get();
        return view('apoderados.create', compact('alumnos'));
    }
    
    // app/Http/Controllers/ApoderadoController.php

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:apoderados,dni',
            'nombres' => 'required|string|max:60',
            'apellido_paterno' => 'required|string|max:60',
            'apellido_materno' => 'required|string|max:60',
            'sexo' => 'required|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:apoderados,email',
            'direccion' => 'nullable|string|max:100',
            'parentesco' => 'required|string|in:' . implode(',', array_keys(Apoderado::PARENTESCOS)),
        ]);
        
        $apoderado = Apoderado::create([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'direccion' => $request->direccion,
            'sexo' => $request->sexo,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'parentesco' => $request->parentesco,
            'recibe_notificaciones' => $request->has('recibe_notificaciones'),
        ]);
        
        if ($request->has('alumnos')) {
            $apoderado->alumnos()->sync($request->alumnos);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Apoderado registrado exitosamente',
            'apoderado' => $apoderado
        ]);
    }

    public function update(Request $request, Apoderado $apoderado)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:apoderados,dni,' . $apoderado->id,
            'nombres' => 'required|string|max:60',
            'apellido_paterno' => 'required|string|max:60',
            'apellido_materno' => 'required|string|max:60',
            'sexo' => 'required|in:M,F',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:apoderados,email,' . $apoderado->id,
            'direccion' => 'nullable|string|max:100',
            'parentesco' => 'required|string|in:' . implode(',', array_keys(Apoderado::PARENTESCOS)),
        ]);
        
        $apoderado->update([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'direccion' => $request->direccion,
            'sexo' => $request->sexo,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'parentesco' => $request->parentesco,
            'recibe_notificaciones' => $request->has('recibe_notificaciones'),
        ]);
        
        if ($request->has('alumnos')) {
            $apoderado->alumnos()->sync($request->alumnos);
        } else {
            $apoderado->alumnos()->sync([]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Apoderado actualizado exitosamente',
            'apoderado' => $apoderado
        ]);
    }
    
    public function show(Apoderado $apoderado)
    {
        $apoderado->load(['alumnos' => function($query) {
            $query->with(['matriculas' => function($q) {
                $q->where('estado', 'activa')->with(['grado', 'seccion', 'anioAcademico']);
            }]);
        }]);
        
        return view('apoderados.show', compact('apoderado'));
    }
    
    public function edit(Apoderado $apoderado)
    {
        $alumnos = Alumno::where('estado', 'activo')->orderBy('apellido_paterno')->get();
        $alumnosSeleccionados = $apoderado->alumnos->pluck('id')->toArray();
        
        return view('apoderados.edit', compact('apoderado', 'alumnos', 'alumnosSeleccionados'));
    }
    
    
    
    public function destroy(Apoderado $apoderado)
    {
        // Verificar si tiene alumnos asociados
        if ($apoderado->alumnos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el apoderado porque tiene alumnos asociados');
        }
        
        // Verificar si tiene matrículas asociadas
        if ($apoderado->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el apoderado porque tiene matrículas asociadas');
        }
        
        $apoderado->delete();
        
        return redirect()->route('admin.apoderados.index')
            ->with('success', 'Apoderado eliminado exitosamente');
    }
    
    public function toggleNotifications(Apoderado $apoderado)
    {
        $apoderado->update(['recibe_notificaciones' => !$apoderado->recibe_notificaciones]);
        
        $estado = $apoderado->recibe_notificaciones ? 'activadas' : 'desactivadas';
        return back()->with('success', "Notificaciones {$estado} correctamente");
    }

    // Agregar estos métodos:

    public function verificarDni(Request $request)
    {
        $dni = $request->get('dni');
        $excludeId = $request->get('exclude_id');
        
        $query = Apoderado::where('dni', $dni);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return response()->json([
            'exists' => $query->exists()
        ]);
    }

    public function verificarEmail(Request $request)
    {
        $email = $request->get('email');
        $excludeId = $request->get('exclude_id');
        
        if (empty($email)) {
            return response()->json(['exists' => false]);
        }
        
        $query = Apoderado::where('email', $email);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return response()->json([
            'exists' => $query->exists()
        ]);
    }


}