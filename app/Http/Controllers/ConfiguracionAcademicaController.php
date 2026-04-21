<?php
// app/Http/Controllers/ConfiguracionAcademicaController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Http\Request;

class ConfiguracionAcademicaController extends Controller
{
    public function index()
    {
        $niveles = Nivel::orderBy('orden')->get();
        $grados = Grado::with('nivel')->orderBy('nivel_id')->orderBy('orden')->get();
        $secciones = Seccion::orderBy('nombre')->get();
        
        return view('configuracion-academica.index', compact('niveles', 'grados', 'secciones'));
    }
    
    // ==================== NIVELES ====================
    public function getNiveles()
    {
        $niveles = Nivel::orderBy('orden')->get();
        return response()->json($niveles);
    }
    
    public function storeNivel(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:niveles,nombre',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);
        
        $nivel = Nivel::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Nivel creado exitosamente', 'data' => $nivel]);
    }
    
    public function updateNivel(Request $request, Nivel $nivel)
    {
        $request->validate([
            'nombre' => 'required|string|max:60|unique:niveles,nombre,' . $nivel->id,
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
        ]);
        
        $nivel->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Nivel actualizado exitosamente', 'data' => $nivel]);
    }
    
    public function deleteNivel(Nivel $nivel)
    {
        if ($nivel->grados()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el nivel porque tiene grados asociados'], 422);
        }
        
        $nivel->delete();
        return response()->json(['success' => true, 'message' => 'Nivel eliminado exitosamente']);
    }
    
    public function toggleNivel(Nivel $nivel)
    {
        $nivel->update(['activo' => !$nivel->activo]);
        return response()->json(['success' => true, 'message' => 'Estado del nivel actualizado', 'activo' => $nivel->activo]);
    }
    
    // ==================== GRADOS ====================
    public function getGrados()
    {
        $grados = Grado::with('nivel')->orderBy('nivel_id')->orderBy('orden')->get();
        return response()->json($grados);
    }
    
    public function getGradosByNivel($nivelId)
    {
        $grados = Grado::where('nivel_id', $nivelId)->orderBy('orden')->get();
        return response()->json($grados);
    }
    
    public function storeGrado(Request $request)
    {
        $request->validate([
            'nivel_id' => 'required|exists:niveles,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'nullable|integer',
        ]);
        
        $exists = Grado::where('nivel_id', $request->nivel_id)
                       ->where('nombre', $request->nombre)
                       ->exists();
        
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Ya existe este grado en el nivel seleccionado'], 422);
        }
        
        $grado = Grado::create([
            'nivel_id' => $request->nivel_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        $grado->load('nivel');
        
        return response()->json(['success' => true, 'message' => 'Grado creado exitosamente', 'data' => $grado]);
    }
    
    public function updateGrado(Request $request, Grado $grado)
    {
        $request->validate([
            'nivel_id' => 'required|exists:niveles,id',
            'nombre' => 'required|string|max:20',
            'orden' => 'nullable|integer',
        ]);
        
        $exists = Grado::where('nivel_id', $request->nivel_id)
                       ->where('nombre', $request->nombre)
                       ->where('id', '!=', $grado->id)
                       ->exists();
        
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Ya existe este grado en el nivel seleccionado'], 422);
        }
        
        $grado->update([
            'nivel_id' => $request->nivel_id,
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        $grado->load('nivel');
        
        return response()->json(['success' => true, 'message' => 'Grado actualizado exitosamente', 'data' => $grado]);
    }
    
    public function deleteGrado(Grado $grado)
    {
        if ($grado->cursos()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar el grado porque tiene cursos asociados'], 422);
        }
        
        $grado->delete();
        return response()->json(['success' => true, 'message' => 'Grado eliminado exitosamente']);
    }
    
    public function toggleGrado(Grado $grado)
    {
        $grado->update(['activo' => !$grado->activo]);
        return response()->json(['success' => true, 'message' => 'Estado del grado actualizado', 'activo' => $grado->activo]);
    }
    
    // ==================== SECCIONES ====================
    public function getSecciones()
    {
        $secciones = Seccion::orderBy('nombre')->get();
        return response()->json($secciones);
    }
    
    public function storeSeccion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:2|unique:secciones,nombre',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);
        
        $seccion = Seccion::create([
            'nombre' => strtoupper($request->nombre),
            'turno' => $request->turno,
            'activo' => true,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Sección creada exitosamente', 'data' => $seccion]);
    }
    
    public function updateSeccion(Request $request, Seccion $seccion)
    {
        $request->validate([
            'nombre' => 'required|string|max:2|unique:secciones,nombre,' . $seccion->id,
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);
        
        $seccion->update([
            'nombre' => strtoupper($request->nombre),
            'turno' => $request->turno,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Sección actualizada exitosamente', 'data' => $seccion]);
    }
    
    public function deleteSeccion(Seccion $seccion)
    {
        if ($seccion->matriculas()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la sección porque tiene matrículas asociadas'], 422);
        }
        
        $seccion->delete();
        return response()->json(['success' => true, 'message' => 'Sección eliminada exitosamente']);
    }
    
    public function toggleSeccion(Seccion $seccion)
    {
        $seccion->update(['activo' => !$seccion->activo]);
        return response()->json(['success' => true, 'message' => 'Estado de la sección actualizado', 'activo' => $seccion->activo]);
    }
    public function getNivel(Nivel $nivel)
    {
        return response()->json($nivel);
    }

    public function getGrado(Grado $grado)
    {
        return response()->json($grado);
    }

    public function getSeccion(Seccion $seccion)
    {
        return response()->json($seccion);
    }
}