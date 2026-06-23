<?php
// app/Http/Controllers/AvanceLibretaController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibreta;
use App\Models\AnioAcademico;
use App\Models\PagoImportado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AvanceLibretaController extends Controller
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
        
        return view('avance-libretas.index', compact('aulas', 'periodos', 'anioActivo'));
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

    public function indexAlumno()
    {
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();

        $anioActivo = AnioAcademico::where('activo', true)->first();

        return view('avance-libretas.index-alumno', compact('periodos', 'anioActivo'));
    }

    public function buscarAlumnos(Request $request)
    {
        $termino = trim((string) $request->input('q', ''));

        if (mb_strlen($termino) < 2) {
            return response()->json([]);
        }

        $anioActivo = AnioAcademico::where('activo', true)->first();

        $query = Matricula::with([
            'alumno:id,codigo_estudiante,dni,nombres,apellido_paterno,apellido_materno',
            'aula:id,grado_id,seccion_id,anio_academico_id,turno',
            'aula.grado:id,nombre,nivel_id',
            'aula.grado.nivel:id,nombre',
            'aula.seccion:id,nombre',
            'aula.anioAcademico:id,anio',
        ])
            ->select('matriculas.*')
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->where(function ($q) use ($termino) {
                $q->where('alumnos.dni', 'LIKE', "%{$termino}%")
                    ->orWhere('alumnos.apellido_paterno', 'LIKE', "%{$termino}%")
                    ->orWhere('alumnos.apellido_materno', 'LIKE', "%{$termino}%")
                    ->orWhere('alumnos.nombres', 'LIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(alumnos.apellido_paterno, ' ', alumnos.apellido_materno) LIKE ?", ["%{$termino}%"])
                    ->orWhereRaw("CONCAT(alumnos.apellido_paterno, ' ', alumnos.apellido_materno, ' ', alumnos.nombres) LIKE ?", ["%{$termino}%"]);
            });

        if ($anioActivo) {
            $query->whereHas('aula', function ($q) use ($anioActivo) {
                $q->where('anio_academico_id', $anioActivo->id);
            });
        }

        $matriculas = $query
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->limit(50)
            ->get();

        $resultado = $matriculas->map(function ($matricula) {
            $alumno = $matricula->alumno;
            $aula = $matricula->aula;

            return [
                'matricula_id' => $matricula->id,
                'codigo_estudiante' => $alumno->codigo_estudiante ?? '',
                'dni' => $alumno->dni ?? '',
                'apellido_paterno' => $alumno->apellido_paterno ?? '',
                'apellido_materno' => $alumno->apellido_materno ?? '',
                'nombres' => $alumno->nombres ?? '',
                'aula_texto' => sprintf(
                    '%s - %s "%s" (%s) - %s',
                    $aula->grado->nivel->nombre ?? '',
                    $aula->grado->nombre ?? '',
                    $aula->seccion->nombre ?? '',
                    $aula->turno_nombre ?? '',
                    $aula->anioAcademico->anio ?? ''
                ),
            ];
        })->values();

        return response()->json($resultado);
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
        
        $pdf = Pdf::loadView('avance-libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('avance_libreta_aula_' . $aula->nombre . '.pdf');
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
        
        $pdf = Pdf::loadView('avance-libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $nombreAlumno = str_replace(' ', '_', $matricula->alumno->nombre_completo);
        return $pdf->download('avance_libreta_' . $nombreAlumno . '.pdf');
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
        
        $periodoSeleccionado = $periodoId ? Periodo::with('anioAcademico')->find($periodoId) : null;
        $nombrePeriodoSeleccionado = $periodoSeleccionado ? ($periodoSeleccionado->nombre_completo ?? $periodoSeleccionado->nombre) : null;

        return view('avance-libretas.previsualizar', compact('aula', 'matricula', 'matriculas', 'periodos', 'configInstitucion', 'configLibreta', 'periodoSeleccionado', 'nombrePeriodoSeleccionado'));
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

        $periodo = $periodoId ? Periodo::with('anioAcademico')->find($periodoId) : null;
        $anioEmision = $aula->anioAcademico->anio
            ?? ($periodo->anioAcademico->anio ?? null)
            ?? now()->year;
        
        // Filtrar por pagos al día si está habilitado
        if ($soloPagosAlDia && $mesLimite) {
            $matriculas = $this->filtrarPorPagosAlDia($matriculas, $mesLimite, (int) $anioEmision);
        }
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        $periodoSeleccionado = $periodoId ? Periodo::with('anioAcademico')->find($periodoId) : null;
        $nombrePeriodoSeleccionado = $periodo ? ($periodo->nombre_completo ?? $periodo->nombre) : null;
        
        return view('avance-libretas.previsualizar-aula', compact('aula', 'matriculas', 'periodos', 'periodoSeleccionado', 'configInstitucion', 'configLibreta', 'nombrePeriodoSeleccionado'));
    }
    
    /**
     * Filtra las matrículas según los pagos al día en la tabla pagos_importados
     * Valida doc_facturacion_dni vs dni_est
     */
    private function filtrarPorPagosAlDia($matriculas, $mesLimite, int $anioEmision)
    {
        $mesesOrdenados = ['marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
        $mesSeleccionado = strtolower((string) $mesLimite);
        
        if (!in_array($mesSeleccionado, $mesesOrdenados, true)) {
            return $matriculas;
        }
        
        // Obtener todos los pagos importados de una vez
        $pagosImportados = PagoImportado::where('anio_emision', $anioEmision)->get();
        
        // Crear un mapa de DNI => lista de pagos para búsqueda rápida
        $pagosMap = [];
        foreach ($pagosImportados as $pago) {
            $dniPago = '';
            
            // Validar doc_facturacion_dni
            if (strlen((string) $pago->doc_facturacion_dni) > 8) {
                // Si supera 8 caracteres, obtener dni_est
                $dniPago = preg_replace('/\D+/', '', (string) $pago->dni_est);
            } else {
                // Si no, usar doc_facturacion_dni
                $dniPago = preg_replace('/\D+/', '', (string) $pago->doc_facturacion_dni);
            }
            
            if ($dniPago) {
                $pagosMap[(string) $dniPago][] = $pago;
            }
        }
        
        // Filtrar las matrículas
        return $matriculas->filter(function ($matricula) use ($mesSeleccionado, $pagosMap) {
            $dni = preg_replace('/\D+/', '', (string) ($matricula->alumno->dni ?? ''));

            if ($dni === '') {
                return false;
            }
            
            // Buscar si existe un pago para este DNI
            if (!isset($pagosMap[$dni])) {
                return false;
            }

            foreach ($pagosMap[$dni] as $pago) {
                // Validar solo el mes seleccionado.
                $monto = (float) ($pago->{$mesSeleccionado} ?? 0);
                if ($monto > 0) {
                    return true;
                }
            }

            return false;
        })->values();
    }
}