<?php

namespace App\Http\Controllers;

use App\Models\AnioAcademico;
use App\Models\CargaHoraria;
use Illuminate\Http\Request;

class CargaHorariaReporteController extends Controller
{
    public function index(Request $request)
    {
        $anios = AnioAcademico::orderBy('anio', 'desc')->get();
        $anioActivo = AnioAcademico::activo()->first();
        $anioSeleccionado = $anioActivo ?? $anios->first();

        $tree = [];
        if ($anioSeleccionado) {
            $tree = $this->buildTreeData($anioSeleccionado->id);
        }

        return view('carga-horaria.reporte', compact('anios', 'anioSeleccionado', 'anioActivo', 'tree'));
    }

    public function data(Request $request)
    {
        $anioId = $request->input('anio_id');
        $aulaId = $request->input('aula_id');
        $anioActivo = AnioAcademico::activo()->first();
        $export = $request->boolean('export') || $request->input('export');

        if (!$anioId && $anioActivo) {
            $anioId = $anioActivo->id;
        }

        if (!$anioId) {
            return response()->json(['html' => view('carga-horaria.partials.reporte-arbol', ['tree' => []])->render()]);
        }

        // Si se solicita modo exportación (export=1) devolvemos las cargas en JSON
        if ($export) {
            $cargas = CargaHoraria::with([
                'docente',
                'curso',
                'aula.grado.nivel',
                'aula.seccion',
                'aula.anioAcademico'
            ])
            ->join('aulas', 'carga_horaria.aula_id', '=', 'aulas.id')
            ->where('aulas.anio_academico_id', $anioId)
            ->where('carga_horaria.estado', CargaHoraria::ESTADO_ACTIVO)
            ->when($aulaId && is_numeric($aulaId), function ($q) use ($aulaId) {
                return $q->where('carga_horaria.aula_id', $aulaId);
            })
            ->orderBy('aulas.grado_id')
            ->orderBy('aulas.seccion_id')
            ->orderBy('carga_horaria.aula_id')
            ->orderBy('carga_horaria.docente_id')
            ->orderBy('carga_horaria.curso_id')
            ->select('carga_horaria.*')
            ->get();

            $datos = $cargas->map(function ($carga) {
                return [
                    'docente_nombre' => $carga->docente?->name ?? 'N/A',
                    'aula_nombre' => $carga->aula?->nombre ?? 'N/A',
                    'grado_nombre' => $carga->aula?->grado?->nombre ?? 'N/A',
                    'seccion_nombre' => $carga->aula?->seccion?->nombre ?? 'N/A',
                    'turno' => $carga->aula?->turno_nombre ?? 'N/A',
                    'curso_nombre' => $carga->curso?->nombre ?? $carga->curso?->titulo ?? 'N/A',
                    'horas_semanales' => $carga->horas_semanales,
                    'dia_semana' => $carga->dia_semana_nombre ?? 'Flexible',
                    'horario' => $this->buildHorarioTexto($carga),
                    'anio_academico' => $carga->aula?->anioAcademico?->anio ?? 'N/A',
                ];
            });

            return response()->json(['cargas' => $datos]);
        }

        // Para vista jerárquica
        if (!$aulaId) {
            $tree = $this->buildTreeData((int) $anioId);
            $html = view('carga-horaria.partials.reporte-arbol', compact('tree'))->render();
            return response()->json(['html' => $html]);
        }

        // Si se llega aquí y no es modo export, devolvemos igualmente la vista (posible filtro por aula)
        // Construimos el árbol completo y lo retornamos; la vista cliente puede mostrar/filtrar por aula
        $tree = $this->buildTreeData((int) $anioId);
        $html = view('carga-horaria.partials.reporte-arbol', compact('tree'))->render();
        return response()->json(['html' => $html]);
    }

    protected function buildTreeData(int $anioId): array
    {
        $cargas = CargaHoraria::with([
            'docente',
            'curso',
            'aula.grado.nivel',
            'aula.seccion',
            'aula.anioAcademico'
        ])
        ->join('aulas', 'carga_horaria.aula_id', '=', 'aulas.id')
        ->where('aulas.anio_academico_id', $anioId)
        ->where('carga_horaria.estado', CargaHoraria::ESTADO_ACTIVO)
        ->orderBy('aulas.grado_id')
        ->orderBy('aulas.seccion_id')
        ->orderBy('carga_horaria.aula_id')
        ->orderBy('carga_horaria.docente_id')
        ->orderBy('carga_horaria.curso_id')
        ->select('carga_horaria.*')
        ->get();

        $tree = [];

        foreach ($cargas as $carga) {
            if (!$carga->aula || !$carga->aula->grado || !$carga->aula->grado->nivel || !$carga->curso || !$carga->docente) {
                continue;
            }

            $nivel = $carga->aula->grado->nivel;
            $grado = $carga->aula->grado;
            $aula = $carga->aula;
            $docente = $carga->docente;
            $curso = $carga->curso;

            $nivelKey = $nivel->id;
            $gradoKey = $grado->id;
            $aulaKey = $aula->id;
            $docenteKey = $docente->id;

            if (!isset($tree[$nivelKey])) {
                $tree[$nivelKey] = [
                    'id' => $nivel->id,
                    'nombre' => $nivel->nombre,
                    'orden' => $nivel->orden ?? 0,
                    'grados' => [],
                ];
            }

            if (!isset($tree[$nivelKey]['grados'][$gradoKey])) {
                $tree[$nivelKey]['grados'][$gradoKey] = [
                    'id' => $grado->id,
                    'nombre' => $grado->nombre,
                    'orden' => $grado->orden ?? 0,
                    'aulas' => [],
                ];
            }

            if (!isset($tree[$nivelKey]['grados'][$gradoKey]['aulas'][$aulaKey])) {
                $tree[$nivelKey]['grados'][$gradoKey]['aulas'][$aulaKey] = [
                    'id' => $aula->id,
                    'nombre' => trim(($aula->seccion?->nombre ?? 'Sección') . ' - ' . ($aula->turno ? ucfirst(strtolower($aula->turno)) : '')),
                    'orden' => $aula->seccion_id ?? 0,
                    'docentes' => [],
                ];
            }

            if (!isset($tree[$nivelKey]['grados'][$gradoKey]['aulas'][$aulaKey]['docentes'][$docenteKey])) {
                $tree[$nivelKey]['grados'][$gradoKey]['aulas'][$aulaKey]['docentes'][$docenteKey] = [
                    'id' => $docente->id,
                    'nombre' => $docente->name,
                    'cursos' => [],
                ];
            }

            $tree[$nivelKey]['grados'][$gradoKey]['aulas'][$aulaKey]['docentes'][$docenteKey]['cursos'][] = [
                'id' => $curso->id,
                'nombre' => $curso->nombre ?? $curso->titulo ?? 'Sin nombre',
                'horas_semanales' => $carga->horas_semanales,
                'detalle_horario' => $this->buildHorarioTexto($carga),
            ];
        }

        $tree = collect($tree)->transform(function ($nivel) {
            $nivel['grados'] = collect($nivel['grados'])->sortBy('orden')->map(function ($grado) {
                $grado['aulas'] = collect($grado['aulas'])->sortBy('orden')->map(function ($aula) {
                    $aula['docentes'] = collect($aula['docentes'])->sortBy('nombre')->map(function ($docente) {
                        $docente['cursos'] = collect($docente['cursos'])->sortBy('nombre')->values()->all();
                        return $docente;
                    })->values()->all();
                    return $aula;
                })->values()->all();
                return $grado;
            })->values()->all();
            return $nivel;
        })->sortBy('orden')->values()->all();

        return $tree;
    }

    protected function buildHorarioTexto(CargaHoraria $carga): string
    {
        if ($carga->dia_semana && $carga->hora_inicio && $carga->hora_fin) {
            return sprintf('%s %s - %s', ucfirst(strtolower($carga->dia_semana)), $carga->hora_inicio->format('H:i'), $carga->hora_fin->format('H:i'));
        }

        return 'Horario flexible';
    }

    public function getAulasDisponibles(Request $request)
    {
        $anioId = $request->input('anio_id');

        if (!$anioId) {
            return response()->json([]);
        }

        $aulas = \App\Models\Aula::where('anio_academico_id', $anioId)
            ->with(['grado.nivel', 'seccion'])
            ->activo()
            ->orderBy('grado_id')
            ->orderBy('seccion_id')
            ->get()
            ->map(fn ($aula) => [
                'id' => $aula->id,
                'nombre' => $aula->nombre_completo,
                'grado' => $aula->grado?->nombre,
                'seccion' => $aula->seccion?->nombre,
                'turno' => $aula->turno_nombre,
            ]);

        return response()->json($aulas);
    }
}
