<?php
// app/Http/Controllers/CompetenciaTransversalJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\CompetenciaTransversal;
use Illuminate\Http\Request;

class CompetenciaTransversalJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $data = $this->getHierarchyData();
        
        return view('competencias-transversales-jerarquico.index', compact('niveles', 'data'));
    }
    
    public function getHierarchyData()
    {
        $niveles = Nivel::where('activo', true)->with(['competenciasTransversales' => function($query) {
            $query->where('activo', true)->orderBy('orden');
        }])->orderBy('orden')->get();
        
        return $niveles;
    }
    
    public function storeCompetencia(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'nivel_id' => 'required|exists:niveles,id',
            'orden' => 'nullable|integer',
        ]);
        
        $competencia = CompetenciaTransversal::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'competencia' => $competencia, 'message' => 'Competencia transversal creada exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Competencia transversal creada exitosamente');
    }
    
    public function updateCompetencia(Request $request, CompetenciaTransversal $competenciasTransversale)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'orden' => 'nullable|integer',
        ]);
        
        $competenciasTransversale->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Competencia transversal actualizada exitosamente']);
    }
    
    public function destroyCompetencia(CompetenciaTransversal $competenciasTransversale)
    {
        $competenciasTransversale->delete();
        return response()->json(['success' => true, 'message' => 'Competencia transversal eliminada exitosamente']);
    }
    
    public function toggleActive(CompetenciaTransversal $competenciasTransversale)
    {
        $competenciasTransversale->update(['activo' => !$competenciasTransversale->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $competenciasTransversale->activo]);
    }
    
    public function getCompetencia(CompetenciaTransversal $competenciasTransversale)
    {
        return response()->json($competenciasTransversale);
    }
}