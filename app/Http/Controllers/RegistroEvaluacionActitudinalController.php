<?php
// app/Http/Controllers/RegistroEvaluacionActitudinalController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\EvaluacionActitudinal;
use App\Models\RegistroEvaluacionActitudinal;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RegistroEvaluacionActitudinalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        if ($rol === 'admin') {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->whereHas('cargaHoraria', function($query) use ($docenteId) {
                    $query->where('docente_id', $docenteId)->where('estado', 'activo');
                })
                ->where('activo', true)
                ->distinct()
                ->orderBy('nombre')
                ->get();
        }
        
        $periodos = Periodo::with('anioAcademico')->orderBy('orden')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-evaluaciones-actitudinales.index', compact('aulas', 'periodos', 'anioActivo'));
    }
    
    public function getDataForRegistro(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        if ($rol !== 'admin') {
            $tieneAcceso = DB::table('carga_horaria')
                ->where('docente_id', $docenteId)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        $aula = Aula::with(['grado.nivel'])->find($aulaId);
        if (!$aula) {
            return response()->json(['error' => 'Aula no encontrada'], 404);
        }

        $nivelId = optional($aula->grado)->nivel_id ?? 0;
        
        $matriculas = Matricula::with(['alumno'])
            ->where('aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno')
            ->orderBy('alumnos.apellido_materno')
            ->orderBy('alumnos.nombres')
            ->select('matriculas.*')
            ->get();
        
        $evaluaciones = EvaluacionActitudinal::where('activo', true)
            ->where('nivel_id', $nivelId)
            ->orderBy('orden')
            ->get();
        
        $registros = [];
        foreach ($evaluaciones as $evaluacion) {
            foreach ($matriculas as $matricula) {
                $registro = RegistroEvaluacionActitudinal::where('matricula_id', $matricula->id)
                    ->where('eval_actitudinal_id', $evaluacion->id)
                    ->where('periodo_id', $periodoId)
                    ->first();
                
                if ($registro) {
                    $registros[$matricula->id][$evaluacion->id] = $registro;
                }
            }
        }
        
        $periodo = Periodo::find($periodoId);
        $registroHabilitado = $periodo ? $periodo->activo : false;
        

        $moduloActitudinal = \App\Models\ModuloRegistro::where('codigo', 'registro-evaluaciones-actitudinales')->first();
        $opcionesValoracion = [];
        
        if ($moduloActitudinal) {
            $tiposNotas = $moduloActitudinal->tiposNotas()
                ->wherePivot('activo', true)
                ->orderBy('orden')
                ->get();
            
            if ($tiposNotas->count() > 0) {
                // Mapear los tipos de nota a valoraciones con código y nombre
                $opcionesValoracion = $tiposNotas->map(function($item) {
                    return [
                        'codigo' => $item->codigo,
                        'nombre' => $item->nombre,
                    ];
                })->toArray();
            }
        }

        return response()->json([
            'matriculas' => $matriculas,
            'evaluaciones' => $evaluaciones,
            'registros' => $registros,
            'registro_habilitado' => $registroHabilitado,
            'opciones_valoracion' => $opcionesValoracion
        ]);
    }
    
    public function saveRegistros(Request $request)
    {
        // Obtener las opciones de valoración configuradas para el módulo actitudinal
        $moduloActitudinal = \App\Models\ModuloRegistro::where('codigo', 'registro-evaluaciones-actitudinales')->first();
        $allowedValoraciones = [];

        if ($moduloActitudinal) {
            $allowedValoraciones = $moduloActitudinal->tiposNotas()
                ->wherePivot('activo', true)
                ->pluck('codigo')
                ->toArray();
        }

        if (empty($allowedValoraciones)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay tipos de nota configurados para el módulo de evaluaciones actitudinales.'
            ], 422);
        }

        $request->validate([
            'registros' => 'required|array|min:1',
            'registros.*.matricula_id' => 'required|exists:matriculas,id',
            'registros.*.evaluacion_id' => 'required|exists:eval_actitudinales,id',
            'registros.*.valoracion' => 'required|in:' . implode(',', $allowedValoraciones),
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id'
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar evaluaciones actitudinales.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->registros as $item) {
                RegistroEvaluacionActitudinal::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'eval_actitudinal_id' => $item['evaluacion_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'valoracion' => $item['valoracion'],
                        'comentario' => $item['comentario'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Evaluaciones actitudinales guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleHabilitacion(Request $request)
    {
        if (auth()->user()->rol !== 'admin' && auth()->user()->role->nombre !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }
        
        $periodo = Periodo::find($request->periodo_id);
        
        if (!$periodo) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo no encontrado'
            ], 404);
        }
        
        $periodo->update(['activo' => !$periodo->activo]);
        
        return response()->json([
            'success' => true,
            'message' => $periodo->activo ? 'Registro de evaluaciones actitudinales habilitado' : 'Registro de evaluaciones actitudinales deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }
    
    public function getOpcionesValoraciones()
    {
        return response()->json([
            'SIEMPRE' => 'Siempre',
            'CASI SIEMPRE' => 'Casi Siempre',
            'ALGUNAS VECES' => 'Algunas Veces',
            'NUNCA' => 'Nunca'
        ]);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'aula_id' => 'required|exists:aulas,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        $aulaId = (int) $request->input('aula_id');
        $periodoId = (int) $request->input('periodo_id');

        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();

        if ($rol !== 'admin') {
            $tieneAcceso = DB::table('carga_horaria')
                ->where('docente_id', $docenteId)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->exists();

            abort_if(!$tieneAcceso, 403, 'No tienes acceso a este aula.');
        }

        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])->findOrFail($aulaId);
        $periodo = Periodo::with('anioAcademico')->findOrFail($periodoId);
        $nivelId = optional($aula->grado)->nivel_id ?? 0;

        $matriculas = Matricula::with(['alumno'])
            ->where('aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno')
            ->orderBy('alumnos.apellido_materno')
            ->orderBy('alumnos.nombres')
            ->select('matriculas.*')
            ->get();

        $evaluaciones = EvaluacionActitudinal::where('activo', true)
            ->where('nivel_id', $nivelId)
            ->orderBy('orden')
            ->get();

        $registros = collect();
        if ($matriculas->isNotEmpty() && $evaluaciones->isNotEmpty()) {
            $registros = RegistroEvaluacionActitudinal::where('periodo_id', $periodoId)
                ->whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereIn('eval_actitudinal_id', $evaluaciones->pluck('id'))
                ->get()
                ->keyBy(function ($registro) {
                    return $registro->matricula_id . '_' . $registro->eval_actitudinal_id;
                });
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridLines(false);

        $totalColumns = 2 + max(1, $evaluaciones->count());
        $lastCol = Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REGISTRO DE EVALUACIONES ACTITUDINALES');
        $sheet->getStyle('A1')->applyFromArray($this->headerStyle('#065f46'));
        $sheet->getStyle('A1')->getFont()->setSize(14);

        $aulaTexto = ($aula->grado?->nivel?->nombre ?? '-') . ' - ' . ($aula->grado?->nombre ?? '-') . ' "' . ($aula->seccion?->nombre ?? '-') . '" (' . ($aula->turno_nombre ?? '-') . ') - ' . ($aula->anioAcademico?->anio ?? '-');
        $sheet->setCellValue('A3', 'Aula:');
        $sheet->mergeCells("B3:{$lastCol}3");
        $sheet->setCellValue('B3', $aulaTexto);
        $sheet->setCellValue('A4', 'Periodo:');
        $sheet->mergeCells("B4:{$lastCol}4");
        $sheet->setCellValue('B4', ($periodo->nombre ?? '-') . ' - ' . ($periodo->anioAcademico?->anio ?? '-'));
        $sheet->setCellValue('A5', 'Nivel:');
        $sheet->mergeCells("B5:{$lastCol}5");
        $sheet->setCellValue('B5', $aula->grado?->nivel?->nombre ?? '-');
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        $row = 7;
        $sheet->setCellValue('A' . $row, 'N°');
        $sheet->setCellValue('B' . $row, 'Alumno');
        $colIndex = 3;
        foreach ($evaluaciones as $evaluacion) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($col . $row, $evaluacion->nombre ?? 'Evaluación');
            $colIndex++;
        }
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($this->headerStyle('#065f46'));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true);
        $row++;

        $dataStartRow = $row;
        $num = 1;
        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            $sheet->setCellValue('A' . $row, $num);
            $sheet->setCellValue('B' . $row, trim(($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '') . ' ' . ($alumno->nombres ?? '')));

            $colIndex = 3;
            foreach ($evaluaciones as $evaluacion) {
                $key = $matricula->id . '_' . $evaluacion->id;
                $valoracion = $registros[$key]->valoracion ?? '';
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($col . $row, $valoracion);
                $colIndex++;
            }

            $num++;
            $row++;
        }

        $lastDataRow = max($row - 1, $dataStartRow);
        $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$dataStartRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($totalColumns >= 3) {
            $sheet->getStyle("C{$dataStartRow}:{$lastCol}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('C8');
        foreach (range(1, $totalColumns) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $fileName = 'evaluaciones_actitudinales_' . $aula->id . '_' . $periodo->id . '_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'eval_act_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function headerStyle(string $fillColor): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => ltrim($fillColor, '#')],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }
}