<?php
// app/Http/Controllers/TipoInasistenciaJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\TipoInasistencia;
use Illuminate\Http\Request;

class TipoInasistenciaJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $data = $this->getHierarchyData();
        
        return view('tipos-inasistencia-jerarquico.index', compact('niveles', 'data'));
    }
    
    public function getHierarchyData()
    {
        $niveles = Nivel::where('activo', true)->with(['tiposInasistencia' => function($query) {
            $query->where('activo', true)->orderBy('orden');
        }])->orderBy('orden')->get();
        
        return $niveles;
    }
    
    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nivel_id' => 'required|exists:niveles,id',
            'orden' => 'nullable|integer',
        ]);
        
        $tipo = TipoInasistencia::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'tipo' => $tipo, 'message' => 'Tipo de inasistencia creado exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Tipo de inasistencia creado exitosamente');
    }
    
    public function updateTipo(Request $request, TipoInasistencia $tipoInasistencia)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'nullable|integer',
        ]);
        
        $tipoInasistencia->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json(['success' => true, 'message' => 'Tipo de inasistencia actualizado exitosamente']);
    }
    
    public function destroyTipo(TipoInasistencia $tipoInasistencia)
    {
        $tipoInasistencia->delete();
        return response()->json(['success' => true, 'message' => 'Tipo de inasistencia eliminado exitosamente']);
    }
    
    public function toggleActive(TipoInasistencia $tipoInasistencia)
    {
        $tipoInasistencia->update(['activo' => !$tipoInasistencia->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $tipoInasistencia->activo]);
    }
    
    public function getTipo(TipoInasistencia $tipoInasistencia)
    {
        return response()->json($tipoInasistencia);
    }
}