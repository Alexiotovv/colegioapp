<?php
// app/Http/Controllers/AulaController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Aula;
use App\Models\Nivel;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\AnioAcademico;
use App\Models\Docente;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    public function index(Request $request)
    {
        $query = Aula::with(['nivel', 'grado', 'seccion', 'anioAcademico', 'docente']);
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('anio_academico_id')) {
            $query->where('anio_academico_id', $request->anio_academico_id);
        }
        
        if ($request->filled('nivel_id')) {
            $query->where('nivel_id', $request->nivel_id);
        }
        
        if ($request->filled('turno')) {
            $query->where('turno', $request->turno);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'LIKE', "%{$request->search}%")
                  ->orWhere('codigo', 'LIKE', "%{$request->search}%");
            });
        }
        
        $aulas = $query->orderBy('anio_academico_id', 'desc')
                       ->orderBy('nivel_id')
                       ->orderBy('grado_id')
                       ->paginate(15)
                       ->withQueryString();
        
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $turnos = Aula::TURNOS;
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('aulas.index', compact('aulas', 'anios', 'niveles', 'turnos', 'anioActivo'));
    }
    
    public function create()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $grados = Grado::where('activo', true)->orderBy('orden')->get();
        $secciones = Seccion::where('activo', true)->orderBy('nombre')->get();
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        
        // 🔥 Obtener solo usuarios con rol docente
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)->orderBy('name')->get();
        
        $turnos = Aula::TURNOS;
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('aulas.create', compact('niveles', 'grados', 'secciones', 'anios', 'docentes', 'turnos', 'anioActivo'));
    }
    
    public function store(Request $request)
    {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'nivel_id' => 'required|exists:niveles,id',
                'grado_id' => 'required|exists:grados,id',
                'seccion_id' => 'required|exists:secciones,id',
                'anio_academico_id' => 'required|exists:anio_academicos,id',
                'turno' => 'required|in:' . implode(',', array_keys(Aula::TURNOS)),
                'capacidad' => 'nullable|integer|min:1|max:100',
                'docente_id' => 'nullable|exists:users,id',
            ]);
        
        // Verificar que no exista un aula con el mismo grado, sección y año
        $existe = Aula::where('grado_id', $request->grado_id)
            ->where('seccion_id', $request->seccion_id)
            ->where('anio_academico_id', $request->anio_academico_id)
            ->exists();
            
        if ($existe) {
            return back()->with('error', 'Ya existe un aula para este grado, sección y año académico')
                ->withInput();
        }
        
        $codigo = Aula::generarCodigo($request->grado_id, $request->seccion_id, $request->anio_academico_id);
        
        Aula::create([
            'codigo' => $codigo,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'turno' => $request->turno,
            'capacidad' => $request->capacidad ?? 30,
            'ubicacion' => $request->ubicacion,
            'activo' => true,
            'nivel_id' => $request->nivel_id,
            'grado_id' => $request->grado_id,
            'seccion_id' => $request->seccion_id,
            'anio_academico_id' => $request->anio_academico_id,
            'docente_id' => $request->docente_id,
        ]);
        
        return redirect()->route('admin.aulas.index')
            ->with('success', 'Aula creada exitosamente');
    }
    
    public function show(Aula $aula)
    {
        $aula->load(['nivel', 'grado', 'seccion', 'anioAcademico', 'docente']);
        return view('aulas.show', compact('aula'));
    }
    

    public function edit(Aula $aula)
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $grados = Grado::where('activo', true)->orderBy('orden')->get();
        $secciones = Seccion::where('activo', true)->orderBy('nombre')->get();
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        
        // 🔥 Obtener solo usuarios con rol docente
        $docentes = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)->orderBy('name')->get();
        
        $turnos = Aula::TURNOS;
        
        return view('aulas.edit', compact('aula', 'niveles', 'grados', 'secciones', 'anios', 'docentes', 'turnos'));
    }

    public function update(Request $request, Aula $aula)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nivel_id' => 'required|exists:niveles,id',
            'grado_id' => 'required|exists:grados,id',
            'seccion_id' => 'required|exists:secciones,id',
            'anio_academico_id' => 'required|exists:anio_academicos,id',
            'turno' => 'required|in:' . implode(',', array_keys(Aula::TURNOS)),
            'capacidad' => 'nullable|integer|min:1|max:100',
            'docente_id' => 'nullable|exists:users,id',
        ]);
        // Verificar duplicado excluyendo el actual
        $existe = Aula::where('grado_id', $request->grado_id)
            ->where('seccion_id', $request->seccion_id)
            ->where('anio_academico_id', $request->anio_academico_id)
            ->where('id', '!=', $aula->id)
            ->exists();
            
        if ($existe) {
            return back()->with('error', 'Ya existe un aula para este grado, sección y año académico')
                ->withInput();
        }
        
        $aula->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'turno' => $request->turno,
            'capacidad' => $request->capacidad ?? 30,
            'ubicacion' => $request->ubicacion,
            'activo' => $request->has('activo'),
            'nivel_id' => $request->nivel_id,
            'grado_id' => $request->grado_id,
            'seccion_id' => $request->seccion_id,
            'anio_academico_id' => $request->anio_academico_id,
            'docente_id' => $request->docente_id,
        ]);
        
        return redirect()->route('admin.aulas.index')
            ->with('success', 'Aula actualizada exitosamente');
    }
    
    public function destroy(Aula $aula)
    {
        $aula->delete();
        
        return redirect()->route('admin.aulas.index')
            ->with('success', 'Aula eliminada exitosamente');
    }
    
    public function toggleActive(Aula $aula)
    {
        $aula->update(['activo' => !$aula->activo]);
        return back()->with('success', 'Estado del aula actualizado');
    }
    
    // Método para obtener grados por nivel (AJAX)
    public function getGradosByNivel(Request $request)
    {
        $grados = Grado::where('nivel_id', $request->nivel_id)
                       ->where('activo', true)
                       ->orderBy('orden')
                       ->get(['id', 'nombre']);
        
        return response()->json($grados);
    }
}