<?php
// app/Http/Controllers/ConfiguracionNotasController.php

namespace App\Http\Controllers;

use App\Models\TipoNota;
use App\Models\ModuloRegistro;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class ConfiguracionNotasController extends Controller
{
    public function index()
    {
        $tiposNotas = TipoNota::ordered()->get();
        $modulos = ModuloRegistro::activo()->get();
        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);
        
        return view('configuracion-notas.index', compact('tiposNotas', 'modulos', 'requiereConclusionBCPrimaria', 'requiereConclusionBSecundaria'));
    }
    
    public function getTiposNotasByModulo(Request $request)
    {
        $modulo = ModuloRegistro::where('codigo', $request->modulo_codigo)->first();
        
        if (!$modulo) {
            return response()->json([]);
        }
        
        // Obtener los tipos de nota asignados con todos sus datos
        $tiposNotasAsignados = $modulo->tiposNotas()
            ->wherePivot('activo', true)
            ->orderBy('orden')
            ->get(['tipos_notas.id', 'tipos_notas.codigo', 'tipos_notas.nombre', 'tipos_notas.tipo_dato']);
        
        return response()->json([
            'tipos_notas' => $tiposNotasAsignados
        ]);
    }
    
    public function storeTipoNota(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:tipos_notas,codigo',
            'nombre' => 'required|string|max:50',
            'tipo_dato' => 'required|in:NUMERICO,LITERAL',
            'orden' => 'nullable|integer',
        ]);
        
        $tipoNota = TipoNota::create([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_dato' => $request->tipo_dato,
            'valor_numerico' => $request->valor_numerico,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        // Devolver el objeto creado
        return response()->json([
            'success' => true,
            'message' => 'Tipo de nota creado exitosamente',
            'tipo_nota' => $tipoNota
        ]);
    }

    public function updateTipoNota(Request $request, TipoNota $tiposNota)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:tipos_notas,codigo,' . $tiposNota->id,
            'nombre' => 'required|string|max:50',
            'tipo_dato' => 'required|in:NUMERICO,LITERAL',
            'orden' => 'nullable|integer',
        ]);
        
        $tiposNota->update([
            'codigo' => strtoupper($request->codigo),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_dato' => $request->tipo_dato,
            'valor_numerico' => $request->valor_numerico,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Tipo de nota actualizado exitosamente'
        ]);
    }
    
    public function destroyTipoNota(TipoNota $tiposNota)
    {
        $tiposNota->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Tipo de nota eliminado exitosamente'
        ]);
    }
    
    public function toggleTipoNota(TipoNota $tiposNota)
    {
        $tiposNota->update(['activo' => !$tiposNota->activo]);
        
        return response()->json([
            'success' => true,
            'message' => $tiposNota->activo ? 'Tipo de nota activado' : 'Tipo de nota desactivado',
            'activo' => $tiposNota->activo
        ]);
    }
    
    public function asignarNotasModulo(Request $request)
    {
        $request->validate([
            'modulo_id' => 'required|exists:modulos_registro,id',
            'tipos_notas' => 'array',
            'tipos_notas.*' => 'exists:tipos_notas,id',
        ]);
        
        $modulo = ModuloRegistro::find($request->modulo_id);
        
        $tiposNotas = $request->tipos_notas ?? [];

        $syncData = [];
        foreach ($tiposNotas as $tipoNotaId) {
            $syncData[$tipoNotaId] = ['activo' => true];
        }

        $modulo->tiposNotas()->sync($syncData);
        
        return response()->json([
            'success' => true,
            'message' => 'Tipos de nota asignados correctamente'
        ]);
    }

    public function getAllTiposNotas()
    {
        return TipoNota::where('activo', true)
            ->orderBy('orden')
            ->get(['id','codigo','nombre','tipo_dato']);
    }

    public function getReglaConclusionBCPrimaria()
    {
        return response()->json([
            'valor' => (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false)
        ]);
    }

    public function guardarReglaConclusionBCPrimaria(Request $request)
    {
        $request->validate([
            'valor' => 'required|boolean',
        ]);

        Configuracion::setValor(
            'notas_requiere_conclusion_bc_primaria',
            $request->valor ? 1 : 0,
            'Requerir conclusión descriptiva para notas B/C en Primaria',
            'numero'
        );

        return response()->json([
            'success' => true,
            'message' => 'Regla guardada correctamente',
            'valor' => (bool) $request->valor
        ]);
    }

    public function guardarReglaConclusionBSecundaria(Request $request)
    {
        $request->validate([
            'valor' => 'required|boolean',
        ]);

        Configuracion::setValor(
            'notas_requiere_conclusion_b_secundaria',
            $request->valor ? 1 : 0,
            'Requerir conclusión descriptiva para nota B en Secundaria',
            'numero'
        );

        return response()->json([
            'success' => true,
            'message' => 'Regla guardada correctamente',
            'valor' => (bool) $request->valor
        ]);
    }
    public function getTipoNota(TipoNota $tiposNota)
    {
        return response()->json($tiposNota);
    }

    // Agrega este método para obtener tipos de nota específicos para actitudinal
    public function getTiposNotasByModuloActitudinal(Request $request)
    {
        $modulo = ModuloRegistro::where('codigo', 'registro-evaluaciones-actitudinales')->first();
        
        if (!$modulo) {
            return response()->json([]);
        }
        
        $tiposNotasAsignados = $modulo->tiposNotas()
            ->wherePivot('activo', true)
            ->orderBy('orden')
            ->get(['tipos_notas.id', 'tipos_notas.codigo', 'tipos_notas.nombre', 'tipos_notas.tipo_dato']);
        
        return response()->json([
            'tipos_notas' => $tiposNotasAsignados
        ]);
    }

    /**
     * Obtener todas las rutas disponibles del sistema
     */
    public function getRutasDisponibles()
    {
        try {
            $routes = Route::getRoutes();
            $routeList = [];
            
            foreach ($routes as $route) {
                $routeName = $route->getName();
                
                // Solo incluir rutas con nombre
                if ($routeName) {
                    // Filtrar rutas no deseadas
                    $excluded = ['debugbar', 'ignition', 'sanctum', 'telescope', 'horizon', 'livewire'];
                    $excludedPattern = '/^(' . implode('|', $excluded) . ')/';
                    
                    if (!preg_match($excludedPattern, $routeName)) {
                        // Solo rutas GET
                        $methods = $route->methods();
                        if (in_array('GET', $methods)) {
                            $routeList[] = [
                                'name' => $routeName,
                                'uri' => $route->uri(),
                                'methods' => implode('|', $methods)
                            ];
                        }
                    }
                }
            }
            
            // Ordenar por nombre de ruta
            usort($routeList, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
            // Limitar a las primeras 200 rutas para evitar sobrecarga
            $routeList = array_slice($routeList, 0, 200);
            
            return response()->json($routeList);
            
        } catch (\Exception $e) {
            \Log::error('Error en getRutasDisponibles: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Almacenar un nuevo módulo de registro
     */
    public function storeModulo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:modulos_registro,codigo',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'ruta' => 'required|string|max:100',
            'activo' => 'nullable|boolean',
        ]);
        
        $modulo = ModuloRegistro::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'ruta' => $request->ruta,
            'activo' => $request->has('activo'),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Módulo creado exitosamente',
            'modulo' => [
                'id' => $modulo->id,
                'codigo' => $modulo->codigo,
                'nombre' => $modulo->nombre,
                'descripcion' => $modulo->descripcion,
                'ruta' => $modulo->ruta,
                'activo' => $modulo->activo
            ]
        ]);
    }

    
}