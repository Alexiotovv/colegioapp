<?php
// app/Http/Controllers/TipoOtraEvaluacionJerarquicoController.php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\TipoOtraEvaluacion;
use Illuminate\Http\Request;

class TipoOtraEvaluacionJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $data = $this->getHierarchyData();
        $tiposDato = TipoOtraEvaluacion::TIPOS_DATO;
        
        return view('tipos-otras-evaluaciones-jerarquico.index', compact('niveles', 'data', 'tiposDato'));
    }
    
    public function getHierarchyData()
    {
        $niveles = Nivel::where('activo', true)->with(['tiposOtrasEvaluaciones' => function($query) {
            $query->where('activo', true)->orderBy('orden');
        }])->orderBy('orden')->get();
        
        return $niveles;
    }
    
    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nivel_id' => 'required|exists:niveles,id',
            'tipo_dato' => 'required|in:NUMERICO,LITERAL',
            'orden' => 'nullable|integer',
        ]);
        
        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_dato' => $request->tipo_dato,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ];
        
        if ($request->tipo_dato === 'NUMERICO') {
            $data['min_valor'] = $request->min_valor ?? 1;
            $data['max_valor'] = $request->max_valor ?? 40;
            $data['opciones_literales'] = null;
        } else {
            $opciones = explode(',', $request->opciones_literales);
            $opciones = array_map('trim', $opciones);
            $data['opciones_literales'] = $opciones;
            $data['min_valor'] = null;
            $data['max_valor'] = null;
        }
        
        $tipo = TipoOtraEvaluacion::create($data);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'tipo' => $tipo, 'message' => 'Tipo de evaluación creado exitosamente']);
        }
        
        return redirect()->back()->with('success', 'Tipo de evaluación creado exitosamente');
    }
    
    public function updateTipo(Request $request, TipoOtraEvaluacion $tiposOtrasEvaluacione)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo_dato' => 'required|in:NUMERICO,LITERAL',
            'orden' => 'nullable|integer',
        ]);
        
        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_dato' => $request->tipo_dato,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ];
        
        if ($request->tipo_dato === 'NUMERICO') {
            $data['min_valor'] = $request->min_valor ?? 1;
            $data['max_valor'] = $request->max_valor ?? 40;
            $data['opciones_literales'] = null;
        } else {
            $opciones = explode(',', $request->opciones_literales);
            $opciones = array_map('trim', $opciones);
            $data['opciones_literales'] = $opciones;
            $data['min_valor'] = null;
            $data['max_valor'] = null;
        }
        
        $tiposOtrasEvaluacione->update($data);
        
        return response()->json(['success' => true, 'message' => 'Tipo de evaluación actualizado exitosamente']);
    }
    
    public function destroyTipo(TipoOtraEvaluacion $tiposOtrasEvaluacione)
    {
        $tiposOtrasEvaluacione->delete();
        return response()->json(['success' => true, 'message' => 'Tipo de evaluación eliminado exitosamente']);
    }
    
    public function toggleActive(TipoOtraEvaluacion $tiposOtrasEvaluacione)
    {
        $tiposOtrasEvaluacione->update(['activo' => !$tiposOtrasEvaluacione->activo]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado', 'activo' => $tiposOtrasEvaluacione->activo]);
    }
    
    public function getTipo(TipoOtraEvaluacion $tiposOtrasEvaluacione)
    {
        return response()->json($tiposOtrasEvaluacione);
    }
}