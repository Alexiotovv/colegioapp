<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Models\TipoOrdenMerito;
use Illuminate\Http\Request;

class TipoOrdenMeritoJerarquicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::where('activo', true)->orderBy('orden')->get();
        $data = $this->getHierarchyData();

        return view('tipos-orden-merito-jerarquico.index', compact('niveles', 'data'));
    }

    public function getHierarchyData()
    {
        return Nivel::where('activo', true)
            ->with(['tiposOrdenMerito' => function ($query) {
                $query->where('activo', true)->orderBy('orden');
            }])
            ->orderBy('orden')
            ->get();
    }

    public function storeTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nivel_id' => 'required|exists:niveles,id',
            'orden' => 'nullable|integer',
        ]);

        $tipo = TipoOrdenMerito::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'nivel_id' => $request->nivel_id,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'tipo' => $tipo,
            'message' => 'Tipo de orden de mérito creado exitosamente',
        ]);
    }

    public function updateTipo(Request $request, TipoOrdenMerito $tipoOrdenMerito)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'nullable|integer',
        ]);

        $tipoOrdenMerito->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de orden de mérito actualizado exitosamente',
        ]);
    }

    public function destroyTipo(TipoOrdenMerito $tipoOrdenMerito)
    {
        $tipoOrdenMerito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de orden de mérito eliminado exitosamente',
        ]);
    }

    public function toggleActive(TipoOrdenMerito $tipoOrdenMerito)
    {
        $tipoOrdenMerito->update(['activo' => !$tipoOrdenMerito->activo]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'activo' => $tipoOrdenMerito->activo,
        ]);
    }

    public function getTipo(TipoOrdenMerito $tipoOrdenMerito)
    {
        return response()->json($tipoOrdenMerito);
    }
}
