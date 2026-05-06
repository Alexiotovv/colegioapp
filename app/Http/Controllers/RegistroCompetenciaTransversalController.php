<?php
// app/Http/Controllers/RegistroCompetenciaTransversalController.php

namespace App\Http\Controllers;

use App\Models\RegistroCompetenciaTransversal;
use App\Models\CargaHoraria;
use App\Models\Aula;
use App\Models\CompetenciaTransversal;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\AnioAcademico;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModuloRegistro;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RegistroCompetenciaTransversalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $esAdmin = $user->isAdmin();
        $docenteId = auth()->id();
        
        // Obtener aulas según el rol
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->when(!$esAdmin, function ($query) use ($docenteId) {
                $query->where('docente_id', $docenteId)
                    ->whereHas('grado.nivel', function ($q) {
                        $q->whereRaw('LOWER(nombre) LIKE ?', ['%secundaria%']);
                    });
            })
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        // Obtener todas las competencias transversales activas
        $competencias = CompetenciaTransversal::with('nivel')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('registro-competencias-transversales.index', compact('aulas', 'competencias', 'periodos', 'anioActivo'));
    }
    
    public function getDataForRegistro(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $user = auth()->user();
        $esAdmin = $user->isAdmin();
        $docenteId = auth()->id();
        
        // Verificar permisos
        if (!$esAdmin) {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->whereHas('grado.nivel', function ($query) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', ['%secundaria%']);
                })
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        // Obtener alumnos matriculados en el aula
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        // Obtener el aula y determinar su nivel para filtrar competencias aplicables
        $aula = Aula::with(['grado.nivel'])->find($aulaId);
        $nivelId = null;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelId = $aula->grado->nivel->id;
        }

        // Obtener todas las competencias transversales activas (filtrar por nivel si aplica)
        $competencias = CompetenciaTransversal::with('nivel')
            ->where('activo', true)
            ->when($nivelId !== null, function ($query) use ($nivelId) {
                $query->where(function ($q) use ($nivelId) {
                    $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
                });
            })
            ->orderBy('orden')
            ->get();

        // Obtener registros existentes
        $matriculaIds = $matriculas->pluck('id')->toArray();
        $competenciaIds = $competencias->pluck('id')->toArray();

        $registros = RegistroCompetenciaTransversal::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_transversal_id', $competenciaIds)
            ->get()
            ->groupBy('matricula_id')
            ->map(function($items) {
                return $items->keyBy('competencia_transversal_id');
            });
        
        $periodo = Periodo::find($periodoId);
        $registrosHabilitados = $periodo ? $periodo->activo : false;

        $esPrimaria = false;
        $esSecundaria = false;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelNombre = $aula->grado->nivel->nombre;
            $esPrimaria = stripos($nivelNombre, 'primaria') !== false;
            $esSecundaria = stripos($nivelNombre, 'secundaria') !== false;
        }

        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);
        
        return response()->json([
            'matriculas' => $matriculas,
            'competencias' => $competencias,
            'registros' => $registros,
            'registros_habilitados' => $registrosHabilitados,
            'aula_es_primaria' => $esPrimaria,
            'aula_es_secundaria' => $esSecundaria,
            'requerir_conclusion_bc_primaria' => $requiereConclusionBCPrimaria,
            'requerir_conclusion_b_secundaria' => $requiereConclusionBSecundaria,
        ]);
    }
    
    public function saveRegistros(Request $request)
    {
        $user = auth()->user();
        $esAdmin = $user->isAdmin();
        $docenteId = auth()->id();
        
        // Verificar acceso al aula
        if (!$esAdmin) {
            $tieneAcceso = Aula::where('id', $request->aula_id)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->whereHas('grado.nivel', function ($query) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', ['%secundaria%']);
                })
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        
        $request->validate([
            'registros' => 'required|array',
            'periodo_id' => 'required|exists:periodos,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar competencias transversales.'
            ], 422);
        }
        
        $docenteId = auth()->id();
        
        $matriculaIds = collect($request->registros)->pluck('matricula_id')->unique()->toArray();
        $competenciaIds = collect($request->registros)->pluck('competencia_transversal_id')->unique()->toArray();

        $existingRegistros = RegistroCompetenciaTransversal::where('periodo_id', $request->periodo_id)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_transversal_id', $competenciaIds)
            ->get()
            ->keyBy(function ($registro) {
                return $registro->matricula_id . '_' . $registro->competencia_transversal_id;
            });

        $aula = Aula::with(['grado.nivel'])->find($request->aula_id);
        $esPrimaria = false;
        $esSecundaria = false;
        if ($aula && $aula->grado && $aula->grado->nivel) {
            $nivelNombre = $aula->grado->nivel->nombre;
            $esPrimaria = stripos($nivelNombre, 'primaria') !== false;
            $esSecundaria = stripos($nivelNombre, 'secundaria') !== false;
        }

        $requiereConclusionBCPrimaria = (bool) Configuracion::getValor('notas_requiere_conclusion_bc_primaria', false);
        $requiereConclusionBSecundaria = (bool) Configuracion::getValor('notas_requiere_conclusion_b_secundaria', false);

        DB::beginTransaction();
        
        try {
            $notasPermitidas = ['AD', 'A', 'B', 'C', 'CND', 'ND'];
            
            foreach ($request->registros as $item) {
                $registroKey = $item['matricula_id'] . '_' . $item['competencia_transversal_id'];
                $existingRegistro = $existingRegistros[$registroKey] ?? null;
                $existingConclusion = $existingRegistro && $existingRegistro->conclusion ? true : false;
                $notaValor = strtoupper(trim($item['nota']));
                $tieneConclusion = !empty($item['conclusion']);

                if ($requiereConclusionBCPrimaria && $esPrimaria && in_array($notaValor, ['B', 'C'])) {
                    if (! $tieneConclusion && ! $existingConclusion) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Las notas B/C en aulas de Primaria requieren una conclusión descriptiva. Por favor registre la conclusión antes de guardar.'
                        ], 422);
                    }
                }

                if ($requiereConclusionBSecundaria && $esSecundaria && $notaValor === 'B') {
                    if (! $tieneConclusion && ! $existingConclusion) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'La nota B en aulas de Secundaria requiere una conclusión descriptiva. Por favor registre la conclusión antes de guardar.'
                        ], 422);
                    }
                }
                if (!in_array($item['nota'], $notasPermitidas)) {
                    throw new \Exception("Nota no válida: " . $item['nota']);
                }
                
                RegistroCompetenciaTransversal::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'competencia_transversal_id' => $item['competencia_transversal_id'],
                        'periodo_id' => $request->periodo_id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'nota' => $item['nota'],
                        'tipo_calificacion' => 'LITERAL',
                        // 'conclusion' => $item['conclusion'] ?? null,
                        'fecha_registro' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Competencias transversales guardadas exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las competencias: ' . $e->getMessage()
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
            'message' => $periodo->activo ? 'Registro de competencias transversales habilitado' : 'Registro de competencias transversales deshabilitado',
            'habilitado' => $periodo->activo
        ]);
    }


    public function saveConclusion(Request $request)
    {
        $request->validate([
            'matricula_id' => 'required|exists:matriculas,id',
            'competencia_id' => 'required|exists:competencias_transversales,id',
            'periodo_id' => 'required|exists:periodos,id',
            'nota' => 'nullable|string|max:10',
            'conclusion' => 'required|string',
        ]);
        
        $user = auth()->user();
        $esAdmin = $user->isAdmin();
        $docenteId = auth()->id();
        
        // Verificar acceso al aula
        if (!$esAdmin) {
            $matricula = Matricula::with('aula')->find($request->matricula_id);
            if (!$matricula) {
                return response()->json(['error' => 'Matrícula no encontrada'], 404);
            }
            
            $tieneAcceso = Aula::where('id', $matricula->aula_id)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->whereHas('grado.nivel', function ($query) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', ['%secundaria%']);
                })
                ->exists();
            
            if (!$tieneAcceso) {
                return response()->json(['error' => 'No tienes acceso a este aula'], 403);
            }
        }
        
        
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo || !$periodo->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo no está habilitado para registrar conclusiones.'
            ], 422);
        }
        
        $registro = RegistroCompetenciaTransversal::where('matricula_id', $request->matricula_id)
            ->where('competencia_transversal_id', $request->competencia_id)
            ->where('periodo_id', $request->periodo_id)
            ->first();

        if ($registro) {
            $registro->update([
                'docente_id' => auth()->id(),
                'conclusion' => $request->conclusion,
                'fecha_registro' => now(),
            ]);
        } else {
            $nota = strtoupper(trim((string) $request->nota));
            $notasPermitidas = ['AD', 'A', 'B', 'C', 'CND', 'ND'];

            if ($nota === '' || !in_array($nota, $notasPermitidas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar una nota válida antes de guardar la conclusión.'
                ], 422);
            }

            $registro = RegistroCompetenciaTransversal::create([
                'matricula_id' => $request->matricula_id,
                'competencia_transversal_id' => $request->competencia_id,
                'periodo_id' => $request->periodo_id,
                'docente_id' => auth()->id(),
                'nota' => $nota,
                'tipo_calificacion' => 'LITERAL',
                'conclusion' => $request->conclusion,
                'fecha_registro' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Conclusión guardada exitosamente',
            'registro_id' => $registro->id
        ]);
    }

    public function getOpcionesNotas()
    {
        $modulo = ModuloRegistro::where('codigo', 'competencias_transversales')->first();
        
        if (!$modulo) {
            // Si no hay configuración, devolver opciones por defecto
            return response()->json(['AD', 'A', 'B', 'C', 'CND', 'ND']);
        }
        
        $tiposNotas = $modulo->tiposNotas()
            ->wherePivot('activo', true)
            ->orderBy('orden')
            ->get();
        
        $opciones = $tiposNotas->pluck('codigo')->toArray();
        
        return response()->json($opciones);
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
        $esAdmin = $user->isAdmin();
        $docenteId = auth()->id();

        if (!$esAdmin) {
            $tieneAcceso = Aula::where('id', $aulaId)
                ->where('docente_id', $docenteId)
                ->where('activo', true)
                ->whereHas('grado.nivel', function ($query) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', ['%secundaria%']);
                })
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

        $nivelId = $aula->grado?->nivel?->id;
        $competencias = CompetenciaTransversal::with('nivel')
            ->where('activo', true)
            ->when($nivelId !== null, function ($query) use ($nivelId) {
                $query->where(function ($q) use ($nivelId) {
                    $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
                });
            })
            ->orderBy('orden')
            ->get();

        $registros = collect();
        if ($matriculas->isNotEmpty() && $competencias->isNotEmpty()) {
            $registros = RegistroCompetenciaTransversal::where('periodo_id', $periodoId)
                ->whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereIn('competencia_transversal_id', $competencias->pluck('id'))
                ->get()
                ->keyBy(function ($registro) {
                    return $registro->matricula_id . '_' . $registro->competencia_transversal_id;
                });
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridLines(false);

        $totalColumns = 3 + max(1, $competencias->count());
        $lastCol = Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REGISTRO DE COMPETENCIAS TRANSVERSALES');
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
        foreach ($competencias as $competencia) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($col . $row, $competencia->nombre ?? 'Competencia');
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
            foreach ($competencias as $competencia) {
                $key = $matricula->id . '_' . $competencia->id;
                $nota = $registros[$key]->nota ?? '';
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($col . $row, $nota);
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

        $fileName = 'competencias_transversales_' . $aula->id . '_' . $periodo->id . '_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'comp_trans_');
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