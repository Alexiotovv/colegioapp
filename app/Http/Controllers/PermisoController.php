<?php
// app/Http/Controllers/PermisoController.php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Modulo;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    // Asignar módulos a roles
    public function asignarModulosRol()
    {
        $roles = Role::where('activo', true)->get();
        $modulos = Modulo::where('activo', true)->ordered()->get();
        $asignaciones = [];
        
        foreach ($roles as $rol) {
            $asignaciones[$rol->id] = $rol->modulos()->pluck('modulos.id')->toArray();
        }
        
        return view('permisos.asignar-roles', compact('roles', 'modulos', 'asignaciones'));
    }
    
    public function guardarAsignacionModulosRol(Request $request)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'modulos' => 'array',
        ]);
        
        $rol = Role::find($request->rol_id);
        $syncData = [];
        
        foreach ($request->modulos as $moduloId) {
            $syncData[$moduloId] = ['activo' => true];
        }
        
        $rol->modulos()->sync($syncData);
        
        return response()->json([
            'success' => true,
            'message' => 'Módulos asignados correctamente al rol'
        ]);
    }
    
    // Asignar módulos extras a usuarios
    public function asignarModulosUsuario()
    {
        $usuarios = User::with('role')->where('activo', true)->get();
        $modulos = Modulo::where('activo', true)->ordered()->get();
        $asignaciones = [];
        
        foreach ($usuarios as $usuario) {
            $asignaciones[$usuario->id] = $usuario->modulosExtra()->pluck('modulos.id')->toArray();
        }
        
        return view('permisos.asignar-usuarios', compact('usuarios', 'modulos', 'asignaciones'));
    }
    
    public function guardarAsignacionModulosUsuario(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'modulos' => 'array',
        ]);
        
        $usuario = User::find($request->usuario_id);
        $syncData = [];
        
        foreach ($request->modulos as $moduloId) {
            $syncData[$moduloId] = ['activo' => true];
        }
        
        $usuario->modulosExtra()->sync($syncData);
        
        return response()->json([
            'success' => true,
            'message' => 'Módulos extras asignados correctamente al usuario'
        ]);
    }
    
    // Obtener módulos de un rol (para AJAX)
    public function getModulosByRol(Role $role)
    {
        $modulos = $role->modulos()->get();
        return response()->json($modulos);
    }
    
    // Obtener módulos extras de un usuario (para AJAX)
    public function getModulosExtraByUser(User $user)
    {
        $modulos = $user->modulosExtra()->get();
        return response()->json($modulos);
    }
}