<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ModuloMiddleware
{
    public function handle(Request $request, Closure $next, string $moduloCodigo)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // LOG TEMPORAL PARA DEPURAR
        \Log::info('=== VERIFICANDO ACCESO A MÓDULO ===');
        \Log::info('Usuario: ' . $user->email);
        \Log::info('Rol: ' . $user->role->nombre);
        \Log::info('Módulo solicitado: ' . $moduloCodigo);
        
        // Obtener módulos permitidos
        $modulosPermitidos = $user->getModulosPermitidos();
        $codigosPermitidos = $modulosPermitidos->pluck('codigo')->toArray();
        
        \Log::info('Módulos permitidos: ' . json_encode($codigosPermitidos));
        
        $tienePermiso = $user->puedeAccederModulo($moduloCodigo);
        \Log::info('¿Tiene permiso? ' . ($tienePermiso ? 'SÍ' : 'NO'));
        
        if (!$tienePermiso) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}