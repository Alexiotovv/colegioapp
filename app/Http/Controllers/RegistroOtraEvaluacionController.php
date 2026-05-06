<?php
// app/Http/Controllers/RegistroOtraEvaluacionController.php

namespace App\Http\Controllers;

use App\Models\RegistroOtraEvaluacion;
use App\Models\Aula;
use App\Models\TipoOtraEvaluacion;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RegistroOtraEvaluacionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Obtener aulas según el rol
        if ($rol === 'admin') {
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            // Docente: solo las aulas donde tiene carga horaria (incluyendo las sin curso asignado)
            $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
                ->whereHas('cargaHoraria', function($query) use ($docenteId) {
                    $query->where('docente_id', $docenteId)
                        ->where('estado', 'activo');
                })
                ->where('activo', true)
                ->distinct()
                ->orderBy('nombre')
                ->get();
        }
        
        // Obtener todos los tipos de otras evaluaciones activos
        $tiposEvaluacion = TipoOtraEvaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-otras-evaluaciones.index', compact('aulas', 'tiposEvaluacion', 'periodos', 'anioActivo'));
    }
    
    public function getDataForRegistro(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        $docenteId = auth()->id();
        
        // Verificar permisos
        if ($rol !== 'admin') {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->whereHas('cargaHoraria', function($query) use ($docenteId) {
                    $query->where('docente_id', $docenteId)
                        ->where('estado', 'activo');
                })
                ->where('activo', true)
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        // Obtener alumnos matriculados en el aula (ordenados alfabéticamente)
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        // Obtener todos los tipos de otras evaluaciones activos
        $tiposEvaluacion = TipoOtraEvaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $tipoIds = $tiposEvaluacion->pluck('id')->toArray();
        
        $registros = RegistroOtraEvaluacion::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('tipo_otra_evaluacion_id', $tipoIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('tipo_otra_evaluacion_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;
        
        // Obtener configuraciones de cada tipo
        $configuraciones = [];
        foreach ($tiposEvaluacion as $tipo) {
            if ($tipo->tipo_dato === 'NUMERICO') {
                $configuraciones[$tipo->id] = [
                    'tipo' => 'NUMERICO',
                    'min' => $tipo->min_valor,
                    'max' => $tipo->max_valor,
                ];
            } else {
                $configuraciones[$tipo->id] = [
                    'tipo' => 'LITERAL',
                    'opciones' => $tipo->opciones_literales,
                ];
            }
        }
        
        return response()->json([
            'matriculas' => $matriculas,
            'tipos_evaluacion' => $tiposEvaluacion,
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
            'configuraciones' => $configuraciones,
        ]);
    }
    
    public function saveRegistros(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        $request->validate([
            'registros' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar evaluaciones.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        DB::beginTransaction();
        
        try {
            foreach ($request->registros as $item) {
                RegistroOtraEvaluacion::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'tipo_otra_evaluacion_id' => $item['tipo_otra_evaluacion_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'valor' => $item['valor'],
                        'observacion' => $item['observacion'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Evaluaciones guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las evaluaciones: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleHabilitacion(Request $request)
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        if ($rol !== 'admin') {
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
            'message' => $periodo->activo ? 'Registro de evaluaciones habilitado' : 'Registro de evaluaciones deshabilitado',
            'habilitado' => $periodo->activo
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
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->exists();

            abort_if(!$tieneAcceso, 403, 'No tienes acceso a este aula.');
        }

        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])->findOrFail($aulaId);
        $periodo = Periodo::with('anioAcademico')->findOrFail($periodoId);

        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();

        $tiposEvaluacion = TipoOtraEvaluacion::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $registros = collect();
        if ($matriculas->isNotEmpty() && $tiposEvaluacion->isNotEmpty()) {
            $registros = RegistroOtraEvaluacion::where('periodo_id', $periodoId)
                ->whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereIn('tipo_otra_evaluacion_id', $tiposEvaluacion->pluck('id'))
                ->get()
                ->keyBy(function ($registro) {
                    return $registro->matricula_id . '_' . $registro->tipo_otra_evaluacion_id;
                });
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridLines(false);

        $totalColumns = 3 + max(1, $tiposEvaluacion->count());
        $lastCol = Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REGISTRO COMPORTAMIENTO Y OTRAS EVALUACIONES');
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
        $sheet->setCellValue('B' . $row, 'Código');
        $sheet->setCellValue('C' . $row, 'Alumno');
        $colIndex = 4;
        foreach ($tiposEvaluacion as $tipo) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($col . $row, $tipo->nombre ?? 'Evaluación');
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
            $sheet->setCellValue('B' . $row, $alumno->codigo_estudiante ?? 'N/A');
            $sheet->setCellValue('C' . $row, trim(($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '') . ' ' . ($alumno->nombres ?? '')));

            $colIndex = 4;
            foreach ($tiposEvaluacion as $tipo) {
                $key = $matricula->id . '_' . $tipo->id;
                $valor = $registros[$key]->valor ?? '';
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($col . $row, $valor);
                $colIndex++;
            }

            $num++;
            $row++;
        }

        $lastDataRow = max($row - 1, $dataStartRow);
        $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$dataStartRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($totalColumns >= 4) {
            $sheet->getStyle("D{$dataStartRow}:{$lastCol}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->freezePane('D8');
        foreach (range(1, $totalColumns) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $fileName = 'otras_evaluaciones_' . $aula->id . '_' . $periodo->id . '_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'otras_eval_');
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