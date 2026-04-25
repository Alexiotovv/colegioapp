<?php
// app/Http/Controllers/AvanceNotasController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\Competencia;
use App\Models\Nota;
use App\Models\Nivel;
use App\Models\AnioAcademico;
use App\Models\Curso;
use App\Models\CargaHoraria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvanceNotasController extends Controller
{
    /**
     * Vista principal de avance de notas por niveles y aulas
     */
    public function index()
    {
        // Obtener el año académico activo
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        if (!$anioActivo) {
            return redirect()->back()->with('error', 'No hay un año académico activo configurado.');
        }
        
        // Obtener todos los periodos del año activo
        $periodos = Periodo::where('anio_academico_id', $anioActivo->id)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener todos los niveles con sus grados y aulas
        $niveles = Nivel::where('activo', true)
            ->with(['grados' => function($query) {
                $query->where('activo', true);
            }])
            ->orderBy('orden')
            ->get();
        
        return view('avance-notas.index', compact('niveles', 'periodos', 'anioActivo'));
    }
    
    /**
     * Obtener datos de avance por aula via AJAX
     */
    public function getAvanceByAula(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id'
        ]);
        
        $periodoId = $request->periodo_id;
        $aulaId = $request->aula_id;
        
        // Obtener el aula con sus relaciones
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
            ->findOrFail($aulaId);
        
        // Obtener los cursos asignados a esta aula (a través de carga_horaria)
        $cursosAsignados = CargaHoraria::where('aula_id', $aulaId)
            ->with(['curso' => function($q) {
                $q->with(['competencias' => function($cq) {
                    $cq->where('activo', true);
                }]);
            }])
            ->get();
        
        // Obtener las matrículas del aula
        $matriculas = Matricula::where('aula_id', $aulaId)
            ->with('alumno')
            ->whereHas('alumno', function($q) {
                $q->where('estado', 'activo');
            })
            ->get();
        
        $detalleAvance = [];
        $totalEsperadoGlobal = 0;
        $totalRegistradoGlobal = 0;
        $totalCursos = $cursosAsignados->count();
        
        foreach ($cursosAsignados as $carga) {
            $curso = $carga->curso;
            $competencias = $curso->competencias ?? collect();
            
            if ($competencias->isEmpty()) {
                continue;
            }
            
            $totalCompetencias = $competencias->count();
            $totalEsperadoCurso = $matriculas->count() * $totalCompetencias;
            $totalRegistradoCurso = 0;
            
            // Contar notas registradas para este curso
            foreach ($matriculas as $matricula) {
                foreach ($competencias as $competencia) {
                    $nota = Nota::where('matricula_id', $matricula->id)
                        ->where('competencia_id', $competencia->id)
                        ->where('periodo_id', $periodoId)
                        ->where('tipo_evaluacion', 'BIMESTRAL')
                        ->first();
                    
                    if ($nota && $nota->nota && $nota->nota !== '') {
                        $totalRegistradoCurso++;
                    }
                }
            }
            
            $porcentajeCurso = $totalEsperadoCurso > 0 
                ? round(($totalRegistradoCurso / $totalEsperadoCurso) * 100, 2) 
                : 0;
            
            $detalleAvance[] = [
                'curso_id' => $curso->id,
                'curso_nombre' => $curso->nombre,
                'curso_codigo' => $curso->codigo,
                'total_competencias' => $totalCompetencias,
                'total_estudiantes' => $matriculas->count(),
                'total_esperado' => $totalEsperadoCurso,
                'total_registrado' => $totalRegistradoCurso,
                'porcentaje' => $porcentajeCurso,
                'competencias' => $competencias->map(function($comp) {
                    return [
                        'id' => $comp->id,
                        'nombre' => $comp->nombre,
                        'ponderacion' => $comp->ponderacion
                    ];
                })
            ];
            
            $totalEsperadoGlobal += $totalEsperadoCurso;
            $totalRegistradoGlobal += $totalRegistradoCurso;
        }
        
        $porcentajeGlobal = $totalEsperadoGlobal > 0 
            ? round(($totalRegistradoGlobal / $totalEsperadoGlobal) * 100, 2) 
            : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'aula' => [
                    'id' => $aula->id,
                    'nombre' => $aula->nombre,
                    'codigo' => $aula->codigo,
                    'nivel' => $aula->grado->nivel->nombre ?? '',
                    'grado' => $aula->grado->nombre ?? '',
                    'seccion' => $aula->seccion->nombre ?? '',
                    'turno' => $aula->turno,
                    'docente' => $aula->docente ? $aula->docente->name : 'No asignado'
                ],
                'matriculas' => $matriculas->map(function($m) {
                    return [
                        'id' => $m->id,
                        'alumno_nombre' => $m->alumno->nombres . ' ' . $m->alumno->apellido_paterno,
                        'alumno_codigo' => $m->alumno->codigo_estudiante
                    ];
                }),
                'cursos' => $detalleAvance,
                'resumen' => [
                    'total_cursos' => $totalCursos,
                    'total_esperado' => $totalEsperadoGlobal,
                    'total_registrado' => $totalRegistradoGlobal,
                    'porcentaje_global' => $porcentajeGlobal,
                    'color' => $this->getColorByPercentage($porcentajeGlobal)
                ]
            ]
        ]);
    }
    
    /**
     * Obtener resumen de todas las aulas de un nivel/grado via AJAX
     */
    public function getResumenAulas(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'nivel_id' => 'nullable|exists:niveles,id',
            'grado_id' => 'nullable|exists:grados,id'
        ]);
        
        $periodoId = $request->periodo_id;
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        $query = Aula::where('anio_academico_id', $anioActivo->id)
            ->where('activo', true)
            ->with(['grado.nivel', 'seccion', 'docente']);
        
        if ($request->nivel_id) {
            $query->whereHas('grado', function($q) use ($request) {
                $q->where('nivel_id', $request->nivel_id);
            });
        }
        
        if ($request->grado_id) {
            $query->where('grado_id', $request->grado_id);
        }
        
        $aulas = $query->orderBy('grado_id')->orderBy('seccion_id')->get();
        
        $aulasConAvance = [];
        
        foreach ($aulas as $aula) {
            // Obtener cursos asignados a esta aula
            $cursosAsignados = CargaHoraria::where('aula_id', $aula->id)->get();
            
            if ($cursosAsignados->isEmpty()) {
                $aulasConAvance[] = [
                    'aula' => [
                        'id' => $aula->id,
                        'nombre' => $aula->nombre,
                        'codigo' => $aula->codigo,
                        'grado' => $aula->grado->nombre ?? '',
                        'seccion' => $aula->seccion->nombre ?? '',
                        'turno' => $aula->turno,
                        'docente' => $aula->docente ? $aula->docente->name : 'No asignado'
                    ],
                    'porcentaje' => 0,
                    'color' => '#dc3545',
                    'sin_cursos' => true
                ];
                continue;
            }
            
            // Obtener matrículas
            $matriculas = Matricula::where('aula_id', $aula->id)
                ->whereHas('alumno', function($q) {
                    $q->where('estado', 'activo');
                })
                ->get();
            
            if ($matriculas->isEmpty()) {
                $aulasConAvance[] = [
                    'aula' => [
                        'id' => $aula->id,
                        'nombre' => $aula->nombre,
                        'codigo' => $aula->codigo,
                        'grado' => $aula->grado->nombre ?? '',
                        'seccion' => $aula->seccion->nombre ?? '',
                        'turno' => $aula->turno,
                        'docente' => $aula->docente ? $aula->docente->name : 'No asignado'
                    ],
                    'porcentaje' => 0,
                    'color' => '#ffc107',
                    'sin_estudiantes' => true
                ];
                continue;
            }
            
            $totalEsperado = 0;
            $totalRegistrado = 0;
            
            foreach ($cursosAsignados as $carga) {
                $competencias = Competencia::where('curso_id', $carga->curso_id)
                    ->where('activo', true)
                    ->get();
                
                if ($competencias->isEmpty()) continue;
                
                $totalEsperado += $matriculas->count() * $competencias->count();
                
                foreach ($matriculas as $matricula) {
                    foreach ($competencias as $competencia) {
                        $nota = Nota::where('matricula_id', $matricula->id)
                            ->where('competencia_id', $competencia->id)
                            ->where('periodo_id', $periodoId)
                            ->where('tipo_evaluacion', 'BIMESTRAL')
                            ->exists();
                        
                        if ($nota) {
                            $totalRegistrado++;
                        }
                    }
                }
            }
            
            $porcentaje = $totalEsperado > 0 ? round(($totalRegistrado / $totalEsperado) * 100, 2) : 0;
            
            $aulasConAvance[] = [
                'aula' => [
                    'id' => $aula->id,
                    'nombre' => $aula->nombre,
                    'codigo' => $aula->codigo,
                    'grado' => $aula->grado->nombre ?? '',
                    'seccion' => $aula->seccion->nombre ?? '',
                    'turno' => $aula->turno,
                    'docente' => $aula->docente ? $aula->docente->name : 'No asignado'
                ],
                'porcentaje' => $porcentaje,
                'color' => $this->getColorByPercentage($porcentaje),
                'total_esperado' => $totalEsperado,
                'total_registrado' => $totalRegistrado
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $aulasConAvance
        ]);
    }
    
    /**
     * Obtener colores según porcentaje de avance
     */
    private function getColorByPercentage($porcentaje)
    {
        if ($porcentaje >= 90) return '#28a745'; // Verde
        if ($porcentaje >= 70) return '#17a2b8'; // Azul
        if ($porcentaje >= 50) return '#ffc107'; // Amarillo
        if ($porcentaje >= 25) return '#fd7e14'; // Naranja
        return '#dc3545'; // Rojo
    }
}