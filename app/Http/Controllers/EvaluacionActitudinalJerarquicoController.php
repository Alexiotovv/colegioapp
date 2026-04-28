<?php
// app/Http/Controllers/EvaluacionActitudinalJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\EvaluacionActitudinal;
use Illuminate\Http\Request;

class EvaluacionActitudinalJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        
        $data = $this->getHierarchyData();
        
        return view('evaluaciones-actitudinales-jerarquico.index', compact('niveles', 'data'));
    }
    
    public function getHierarchyData()
    {
        $niveles = Nivel::where('activo', true)->with(['evaluacionesActitudinales' => function($query) {
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
        
        $evaluacion = EvaluacionActitudinal::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'evaluacion' => $evaluacion, 'message' => 'Evaluación actitudinal creada exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Evaluación actitudinal creada exitosamente');
    }
    
    public function updateEvaluacion(Request $request, EvaluacionActitudinal $evaluacionActitudinal)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'orden' => 'nullable|integer',
        ]);
        
        $evaluacionActitudinal->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Evaluación actitudinal actualizada exitosamente']);
    }
    
    public function destroyEvaluacion(EvaluacionActitudinal $evaluacionActitudinal)
    {
        $evaluacionActitudinal->delete();
        return response()->json(['success' => true, 'message' => 'Evaluación actitudinal eliminada exitosamente']);
    }
    
    public function toggleActive(EvaluacionActitudinal $evaluacionActitudinal)
    {
        $evaluacionActitudinal->update(['activo' => !$evaluacionActitudinal->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $evaluacionActitudinal->activo]);
    }
    
    public function getEvaluacion(EvaluacionActitudinal $evaluacionActitudinal)
    {
        return response()->json($evaluacionActitudinal);
    }
}