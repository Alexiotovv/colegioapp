<?php
// app/Http/Controllers/EvaluacionJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\Evaluacion;
use Illuminate\Http\Request;

class EvaluacionJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        
        // Obtener datos jerárquicos
        $data = $this->getHierarchyData();
        
        return view('evaluaciones-jerarquico.index', compact('niveles', 'data'));
    }
    
    public function getHierarchyData()
    {
        $niveles = Nivel::where('activo', true)->with(['evaluaciones' => function($query) {
            $query->where('activo', true)->orderBy('orden');
        }])->orderBy('orden')->get();
        
        return $niveles;
    }
    
    public function storeEvaluacion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'nivel_id' => 'required|exists:niveles,id',
            'orden' => 'nullable|integer',
        ]);
        
        $evaluacion = Evaluacion::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'evaluacion' => $evaluacion, 'message' => 'Evaluación creada exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Evaluación creada exitosamente');
    }
    
    public function updateEvaluacion(Request $request, Evaluacion $evaluacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'orden' => 'nullable|integer',
        ]);
        
        $evaluacion->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Evaluación actualizada exitosamente']);
    }
    
    public function destroyEvaluacion(Evaluacion $evaluacion)
    {
        $evaluacion->delete();
        return response()->json(['success' => true, 'message' => 'Evaluación eliminada exitosamente']);
    }
    
    public function toggleActive(Evaluacion $evaluacion)
    {
        $evaluacion->update(['activo' => !$evaluacion->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $evaluacion->activo]);
    }
    
    public function getEvaluacion(Evaluacion $evaluacion)
    {
        return response()->json($evaluacion);
    }
    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }
}