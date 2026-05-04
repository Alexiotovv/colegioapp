<?php
// app/Http/Controllers/LibretaController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Nota;
use App\Models\Apreciacion;
use App\Models\RegistroEvaluacion;
use App\Models\RegistroAsistencia;
use App\Models\RegistroOtraEvaluacion;
use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibreta;
use App\Models\AnioAcademico;
use App\Models\PagoImportado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LibretaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('libretas.index', compact('aulas', 'periodos', 'anioActivo'));
    }
    
    public function getAlumnosByAula(Request $request)
    {
        $aulaId = $request->aula_id;
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        return response()->json($matriculas);
    }
    
    public function exportarAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
            ->find($aulaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        $data = [
            'aula' => $aula,
            'periodos' => $periodos,
            'matriculas' => $matriculas,
            'configInstitucion' => $configInstitucion,
            'configLibreta' => $configLibreta,
            'tipo' => 'aula'
        ];
        
        $pdf = Pdf::loadView('libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('libreta_aula_' . $aula->nombre . '.pdf');
    }
    
    public function exportarAlumno(Request $request)
    {
        $matriculaId = $request->matricula_id;
        $periodoId = $request->periodo_id;
        
        $matricula = Matricula::with(['alumno', 'aula.grado.nivel', 'aula.seccion', 'aula.anioAcademico', 'aula.docente'])
            ->find($matriculaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        $data = [
            'matricula' => $matricula,
            'periodos' => $periodos,
            'configInstitucion' => $configInstitucion,
            'configLibreta' => $configLibreta,
            'tipo' => 'alumno'
        ];
        
        $pdf = Pdf::loadView('libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $nombreAlumno = str_replace(' ', '_', $matricula->alumno->nombre_completo);
        return $pdf->download('libreta_' . $nombreAlumno . '.pdf');
    }
    
    public function previsualizar(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        $matriculaId = $request->matricula_id;
        
        $aula = null;
        $matricula = null;
        
        if ($matriculaId) {
            $matricula = Matricula::with(['alumno', 'aula.grado.nivel', 'aula.seccion', 'aula.anioAcademico', 'aula.docente'])
                ->find($matriculaId);
        } else {
            $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
                ->find($aulaId);
        }
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = null;
        if ($aulaId && !$matriculaId) {
            $matriculas = Matricula::with(['alumno'])
                ->select('matriculas.*')
                ->where('matriculas.aula_id', $aulaId)
                ->where('matriculas.estado', 'activa')
                ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
                ->orderBy('alumnos.apellido_paterno', 'ASC')
                ->orderBy('alumnos.apellido_materno', 'ASC')
                ->orderBy('alumnos.nombres', 'ASC')
                ->get();
        }
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        return view('libretas.previsualizar', compact('aula', 'matricula', 'matriculas', 'periodos', 'configInstitucion', 'configLibreta'));
    }

    public function previsualizarAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        $soloPagosAlDia = $request->boolean('solo_pagos_al_dia', false);
        $mesLimite = $request->input('mes_limite', null);
        
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
            ->find($aulaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        // Filtrar por pagos al día si está habilitado
        if ($soloPagosAlDia && $mesLimite) {
            $matriculas = $this->filtrarPorPagosAlDia($matriculas, $mesLimite);
        }
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        return view('libretas.previsualizar-aula', compact('aula', 'matriculas', 'periodos', 'configInstitucion', 'configLibreta'));
    }
    
    /**
     * Filtra las matrículas según los pagos al día en la tabla pagos_importados
     * Valida doc_facturacion_dni vs dni_est
     */
    private function filtrarPorPagosAlDia($matriculas, $mesLimite)
    {
        $mesesOrdenados = ['marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
        $mesIndex = array_search($mesLimite, $mesesOrdenados);
        
        if ($mesIndex === false) {
            return $matriculas;
        }
        
        $mesesRequeridos = array_slice($mesesOrdenados, 0, $mesIndex + 1);
        
        // Obtener todos los pagos importados de una vez
        $pagosImportados = PagoImportado::where('anio_emision', 2026)->get();
        
        // Crear un mapa de DNI => pagos para búsqueda rápida
        $pagosMap = [];
        foreach ($pagosImportados as $pago) {
            $dniPago = null;
            
            // Validar doc_facturacion_dni
            if (strlen($pago->doc_facturacion_dni) > 8) {
                // Si supera 8 caracteres, obtener dni_est
                $dniPago = $pago->dni_est;
            } else {
                // Si no, usar doc_facturacion_dni
                $dniPago = $pago->doc_facturacion_dni;
            }
            
            if ($dniPago) {
                // Convertir a string para asegurar consistencia
                $pagosMap[(string) $dniPago] = $pago;
            }
        }
        
        // Filtrar las matrículas
        return $matriculas->filter(function ($matricula) use ($mesesRequeridos, $pagosMap) {
            $dni = (string) $matricula->alumno->dni;
            
            // Buscar si existe un pago para este DNI
            if (!isset($pagosMap[$dni])) {
                return false;
            }
            
            $pago = $pagosMap[$dni];
            
            // Verificar si el alumno tiene pagos hasta el mes límite
            foreach ($mesesRequeridos as $mes) {
                $monto = (float) ($pago->{$mes} ?? 0);
                if ($monto <= 0) {
                    return false;
                }
            }
            
            return true;
        })->values();
    }
}