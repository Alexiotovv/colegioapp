<?php
// app/Http/Controllers/ModuloController.php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 
class ModuloController extends Controller
{
    public function index()
    {
        $modulos = Modulo::ordered()->get();
        return view('modulos.index', compact('modulos'));
    }
    
    public function create()
    {
        $modulos = Modulo::ordered()->get();
        
        // Obtener todas las rutas registradas en Laravel
        $rutasDisponibles = $this->getAllRouteNames();
        
        return view('modulos.create', compact('modulos', 'rutasDisponibles'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:modulos,codigo',
            'nombre' => 'required|string|max:100',
            'ruta' => 'nullable|string|max:100',
            'icono' => 'nullable|string|max:50',
            'orden' => 'nullable|integer',
        ]);
        
        Modulo::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'ruta' => $request->ruta,
            'icono' => $request->icono,
            'padre_id' => $request->padre_id,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.modulos.index')
            ->with('success', 'Módulo creado exitosamente');
    }
    
    public function edit(Modulo $modulo)
    {
        $modulos = Modulo::ordered()->get();
        // Obtener todas las rutas registradas en Laravel
        $rutasDisponibles = $this->getAllRouteNames();
        return view('modulos.edit', compact('modulo', 'modulos', 'rutasDisponibles'));
    }
    
    public function update(Request $request, Modulo $modulo)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:modulos,codigo,' . $modulo->id,
            'nombre' => 'required|string|max:100',
            'ruta' => 'nullable|string|max:100',
            'icono' => 'nullable|string|max:50',
            'orden' => 'nullable|integer',
        ]);
        
        $modulo->update([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'ruta' => $request->ruta,
            'icono' => $request->icono,
            'padre_id' => $request->padre_id,
            'orden' => $request->orden ?? 0,
            'activo' => $request->has('activo'),
        ]);
        
        return redirect()->route('admin.modulos.index')
            ->with('success', 'Módulo actualizado exitosamente');
    }
    
    public function destroy(Modulo $modulo)
    {
        $modulo->delete();
        return redirect()->route('admin.modulos.index')
            ->with('success', 'Módulo eliminado exitosamente');
    }
    
    public function toggleActive(Modulo $modulo)
    {
        $modulo->update(['activo' => !$modulo->activo]);
        return back()->with('success', 'Estado del módulo actualizado');
    }

    private function getAllRouteNames()
    {
        $routes = Route::getRoutes();
        $routeList = [];
        
        foreach ($routes as $route) {
            $routeName = $route->getName();
            if ($routeName && !str_starts_with($routeName, 'debugbar') && !str_starts_with($routeName, 'ignition')) {
                $routeList[] = [
                    'name' => $routeName,
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods())
                ];
            }
        }
        
        // Ordenar por nombre de ruta
        usort($routeList, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $routeList;
    }
}