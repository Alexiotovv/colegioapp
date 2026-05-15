<?php

namespace App\Http\Controllers;

use App\Models\AnioAcademico;
use App\Models\Apreciacion;
use App\Models\Aula;
use App\Models\CargaHoraria;
use App\Models\CompetenciaTransversal;
use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibretaCuadro;
use App\Models\Evaluacion;
use App\Models\EvaluacionActitudinal;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Periodo;
use App\Models\RegistroCompetenciaTransversal;
use App\Models\RegistroEvaluacion;
use App\Models\RegistroEvaluacionActitudinal;
use App\Models\RegistroAsistencia;
use App\Models\RegistroOrdenMerito;
use App\Models\RegistroOtraEvaluacion;
use App\Models\TipoInasistencia;
use App\Models\TipoOtraEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteNotasController extends Controller
{
    private const MODULO_EXPORTAR_COMPLETO = 'reportes-notas-exportar-completo';

    public function index(Request $request)
    {
        $user = auth()->user();
        $anioSeleccionado = $this->obtenerAnioSeleccionado($request);
        $anios = AnioAcademico::orderByDesc('anio')->get();
        $periodos = $this->obtenerPeriodosPorAnio($anioSeleccionado?->id);
        $aulas = $this->obtenerAulasPorUsuario($user, $anioSeleccionado?->id);
        $puedeExportarCompleto = $this->puedeExportarCompleto($user);

        $periodoSeleccionado = $this->obtenerPeriodoSeleccionado($request, $periodos);
        $aulaSeleccionada = $this->obtenerAulaSeleccionada($request, $aulas);

        return view('reportes.notas.index', compact(
            'anios',
            'anioSeleccionado',
            'periodos',
            'periodoSeleccionado',
            'aulas',
            'aulaSeleccionada',
            'puedeExportarCompleto'
        ));
    }

    public function filtros(Request $request)
    {
        $request->validate([
            'anio_id' => ['required', 'exists:anio_academicos,id'],
        ]);

        $user = auth()->user();
        $anioId = (int) $request->input('anio_id');
        $periodos = $this->obtenerPeriodosPorAnio($anioId);
        $aulas = $this->obtenerAulasPorUsuario($user, $anioId);

        return response()->json([
            'periodos' => $periodos->map(fn ($periodo) => [
                'id' => $periodo->id,
                'nombre' => $periodo->nombre,
                'texto' => $periodo->nombre_completo,
                'activo' => $periodo->activo,
            ])->values(),
            'aulas' => $aulas->map(fn ($aula) => [
                'id' => $aula->id,
                'nombre' => $aula->nombre_completo,
                'grado' => $aula->grado?->nombre,
                'seccion' => $aula->seccion?->nombre,
                'turno' => $aula->turno_nombre,
            ])->values(),
            'periodo_default_id' => $periodos->first()?->id,
            'aula_default_id' => $aulas->first()?->id,
        ]);
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'anio_id' => ['required', 'exists:anio_academicos,id'],
            'periodo_id' => ['required', 'exists:periodos,id'],
            'aula_id' => ['required', 'exists:aulas,id'],
            'exportar_completo' => ['nullable', 'in:0,1'],
        ]);

        $user = auth()->user();
        $anioId = (int) $request->input('anio_id');
        $periodoId = (int) $request->input('periodo_id');
        $aulaId = (int) $request->input('aula_id');
        $exportarCompleto = (bool) $request->input('exportar_completo', false);
        if ($exportarCompleto && !$this->puedeExportarCompleto($user)) {
            $exportarCompleto = false;
        }

        $anio = AnioAcademico::findOrFail($anioId);
        $periodo = Periodo::with('anioAcademico')->findOrFail($periodoId);
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])->findOrFail($aulaId);

        $this->asegurarAccesoAula($user, $aula, $anioId);

        $cargas = $this->obtenerCargasPorAula($user, $aula->id, $anioId);
        if ($cargas->isEmpty()) {
            return back()->with('error', 'No hay cursos asignados para el aula seleccionada.');
        }

        $alumnos = $this->obtenerAlumnosPorAula($aula->id);
        if ($alumnos->isEmpty()) {
            return back()->with('error', 'El aula seleccionada no tiene alumnos matriculados activos.');
        }

        $institucion = ConfiguracionInstitucion::getConfig();
        $notasGlobales = $this->obtenerNotasGlobales($periodoId, $alumnos, $cargas);
        $cuadrosHabilitados = ConfiguracionLibretaCuadro::getCuadrosForNivel($aula->nivel_id);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->crearHojaGeneralidades($spreadsheet, $institucion, $anio, $periodo, $aula, $cargas, $alumnos);
        $this->crearHojasPorCurso($spreadsheet, $institucion, $anio, $periodo, $aula, $cargas, $alumnos, $notasGlobales);

        if ($exportarCompleto) {
            $this->crearHojasComplementariasSegunConfig(
                $spreadsheet,
                $institucion,
                $anio,
                $periodo,
                $aula,
                $alumnos,
                $cuadrosHabilitados
            );
        }

        $nombreArchivo = sprintf(
            'reporte_notas_%s_%s_%s.xlsx',
            $anio->anio,
            $periodo->nombre,
            str_replace(' ', '_', $aula->nombre_completo)
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            if (ob_get_length()) {
                @ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function obtenerAnioSeleccionado(Request $request): ?AnioAcademico
    {
        if ($request->filled('anio_id')) {
            return AnioAcademico::find($request->input('anio_id'));
        }

        return AnioAcademico::where('activo', true)->first() ?? AnioAcademico::orderByDesc('anio')->first();
    }

    private function obtenerPeriodoSeleccionado(Request $request, Collection $periodos)
    {
        if ($request->filled('periodo_id')) {
            return $periodos->firstWhere('id', (int) $request->input('periodo_id')) ?? $periodos->first();
        }

        return $periodos->first();
    }

    private function puedeExportarCompleto($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $user->puedeAccederModulo(self::MODULO_EXPORTAR_COMPLETO);
    }

    private function obtenerAulaSeleccionada(Request $request, Collection $aulas)
    {
        if ($request->filled('aula_id')) {
            return $aulas->firstWhere('id', (int) $request->input('aula_id')) ?? $aulas->first();
        }

        return $aulas->first();
    }

    private function obtenerPeriodosPorAnio(?int $anioId): Collection
    {
        if (!$anioId) {
            return collect();
        }

        return Periodo::with('anioAcademico')
            ->where('anio_academico_id', $anioId)
            ->orderBy('orden')
            ->get();
    }

    private function obtenerAulasPorUsuario($user, ?int $anioId): Collection
    {
        $query = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->where('activo', true)
            ->when($anioId, function ($q) use ($anioId) {
                $q->where('anio_academico_id', $anioId);
            });

        if (!$user || !$user->isAdmin()) {
            $userId = $user?->id;
            $query->where(function ($q) use ($userId, $anioId) {
                $q->where('docente_id', $userId)
                    ->orWhereHas('cargaHoraria', function ($subQ) use ($userId, $anioId) {
                        $subQ->where('docente_id', $userId)
                            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                            ->when($anioId, function ($subQuery) use ($anioId) {
                                $subQuery->whereHas('aula', function ($aulaQuery) use ($anioId) {
                                    $aulaQuery->where('anio_academico_id', $anioId);
                                });
                            });
                    });
            });
        }

        return $query->orderBy('grado_id')->orderBy('seccion_id')->get();
    }

    private function obtenerCargasPorAula($user, int $aulaId, int $anioId): Collection
    {
        $baseQuery = CargaHoraria::with([
                'docente',
                'curso.competencias' => function ($q) {
                    $q->where('activo', true)->orderBy('orden')->orderBy('nombre');
                },
                'aula.grado.nivel',
                'aula.seccion',
                'aula.anioAcademico',
            ])
            ->where('aula_id', $aulaId)
            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
            ->whereHas('curso', function ($q) use ($anioId) {
                $q->where('anio_academico_id', $anioId)
                  ->where('activo', true);
            });

        // Admin: siempre obtiene todos los cursos del aula.
        if ($user && $user->isAdmin()) {
            return $baseQuery->orderBy('curso_id')->get()->groupBy('curso_id')->map(function ($grupo) {
                return $grupo->first();
            })->values();
        }

        $todasLasCargas = (clone $baseQuery)
            ->orderBy('curso_id')
            ->get();

        // Para no-admin: si tiene cursos asignados en el aula, exporta solo esos.
        $cargasUsuario = (clone $baseQuery)
            ->where('docente_id', $user?->id)
            ->orderBy('curso_id')
            ->get();

        $cargasFinales = $cargasUsuario->isNotEmpty() ? $cargasUsuario : $todasLasCargas;

        return $cargasFinales->groupBy('curso_id')->map(function ($grupo) {
            return $grupo->first();
        })->values();
    }

    private function obtenerAlumnosPorAula(int $aulaId): Collection
    {
        return Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
    }

    private function obtenerNotasGlobales(int $periodoId, Collection $alumnos, Collection $cargas): Collection
    {
        $matriculaIds = $alumnos->pluck('id')->all();
        $competenciaIds = $cargas->flatMap(function ($carga) {
            return $carga->curso?->competencias?->pluck('id') ?? collect();
        })->filter()->unique()->values()->all();

        if (empty($matriculaIds) || empty($competenciaIds)) {
            return collect();
        }

        return Nota::with('conclusionDescriptiva')
            ->where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_id', $competenciaIds)
            ->get()
            ->keyBy(function ($nota) {
                return $nota->matricula_id . '_' . $nota->competencia_id;
            });
    }

    private function asegurarAccesoAula($user, Aula $aula, int $anioId): void
    {
        if ($user && $user->isAdmin()) {
            return;
        }

        $tieneAcceso = Aula::where('id', $aula->id)
            ->where('anio_academico_id', $anioId)
            ->where(function ($q) use ($user) {
                $q->where('docente_id', $user?->id)
                    ->orWhereHas('cargaHoraria', function ($subQ) use ($user) {
                        $subQ->where('docente_id', $user?->id)
                            ->where('estado', CargaHoraria::ESTADO_ACTIVO);
                    });
            })
            ->exists();

        abort_if(!$tieneAcceso, 403, 'No tienes permiso para exportar esta aula.');
    }

    private function crearHojaGeneralidades(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $cargas, Collection $alumnos): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Generalidades');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'REPORTE DE NOTAS - GENERALIDADES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', $institucion->nombre ?? 'Institución educativa');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->fromArray([
            ['Año académico', $anio->anio],
            ['Periodo', $periodo->nombre],
            ['Aula', $aula->nombre_completo],
            ['Nivel', $aula->grado?->nivel?->nombre ?? '-'],
            ['Grado', $aula->grado?->nombre ?? '-'],
            ['Sección', $aula->seccion?->nombre ?? '-'],
            ['Turno', $aula->turno_nombre ?? '-'],
            ['Total de alumnos', $alumnos->count()],
            ['Cursos incluidos', $cargas->count()],
        ], null, 'A4');

        $sheet->setCellValue('A14', 'CÓDIGO');
        $sheet->setCellValue('B14', 'CURSO');
        $sheet->setCellValue('C14', 'DOCENTE');
        $sheet->setCellValue('D14', 'COMPETENCIAS');
        $sheet->setCellValue('E14', 'ALUMNOS');
        $sheet->setCellValue('F14', 'OBSERVACIÓN');
        $sheet->getStyle('A14:F14')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row = 15;
        foreach ($cargas as $carga) {
            $competencias = $carga->curso?->competencias?->where('activo', true)->sortBy('orden')->values() ?? collect();
            $sheet->setCellValue("A{$row}", $carga->curso?->codigo ?? '-');
            $sheet->setCellValue("B{$row}", $carga->curso?->nombre ?? '-');
            $sheet->setCellValue("C{$row}", $carga->docente?->name ?? '-');
            $sheet->setCellValue("D{$row}", $competencias->count());
            $sheet->setCellValue("E{$row}", $alumnos->count());
            $sheet->setCellValue("F{$row}", $carga->curso?->tipo_nombre ?? '-');
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
    }

    private function crearHojasPorCurso(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $cargas, Collection $alumnos, Collection $notasGlobales): void
    {
        foreach ($cargas as $carga) {
            $curso = $carga->curso;
            $competencias = $curso?->competencias?->where('activo', true)->sortBy('orden')->values() ?? collect();
            $sheet = $spreadsheet->createSheet();

            $titulo = $curso?->codigo ? $curso->codigo . ' - ' . $curso->nombre : ($curso?->nombre ?? 'Curso');
            $sheet->setTitle($this->sanitizarTituloHoja($curso?->codigo ?? $curso?->nombre ?? 'Curso'));
            $sheet->setShowGridLines(false);

            $maxCol = 4 + max(1, $competencias->count() * 2);
            $lastCol = Coordinate::stringFromColumnIndex($maxCol);

            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->setCellValue('A2', 'REPORTE DE NOTAS - ' . $titulo);
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A3', sprintf(
                'Año académico: %s | Periodo: %s | Aula: %s | Docente: %s',
                $anio->anio,
                $periodo->nombre,
                $aula->nombre_completo,
                $carga->docente?->name ?? '-'
            ));
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A3')->getFont()->setItalic(true);

            $sheet->mergeCells('A4:A5');
            $sheet->setCellValue('A4', 'N°');
            $sheet->mergeCells('B4:B5');
            $sheet->setCellValue('B4', 'ID');
            $sheet->mergeCells('C4:C5');
            $sheet->setCellValue('C4', 'Cód. Estudiante');
            $sheet->mergeCells('D4:D5');
            $sheet->setCellValue('D4', 'Apellidos y nombres');

            $col = 5;
            foreach ($competencias as $index => $competencia) {
                $codigoComp = str_pad((string) ($competencia->orden ?: ($index + 1)), 2, '0', STR_PAD_LEFT);
                $colInicio = Coordinate::stringFromColumnIndex($col);
                $colFin = Coordinate::stringFromColumnIndex($col + 1);
                $sheet->mergeCells("{$colInicio}4:{$colFin}4");
                $sheet->setCellValue("{$colInicio}4", $codigoComp);
                $sheet->setCellValue("{$colInicio}5", 'NL');
                $sheet->setCellValue("{$colFin}5", 'Conclusión descriptiva de la competencia');
                $col += 2;
            }

            $sheet->getStyle("A4:{$lastCol}5")->applyFromArray($this->headerStyle('#065f46'));
            $sheet->getStyle("A4:{$lastCol}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A4:{$lastCol}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A4:' . $lastCol . '5')->getAlignment()->setWrapText(true);

            $row = 6;
            foreach ($alumnos as $index => $matricula) {
                $alumno = $matricula->alumno;
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("C{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
                $sheet->setCellValue("D{$row}", $this->nombreCompletoAlumno($alumno));

                $col = 5;
                foreach ($competencias as $indexCompetencia => $competencia) {
                    $nota = $notasGlobales[$matricula->id . '_' . $competencia->id] ?? null;
                    $codigoComp = str_pad((string) ($competencia->orden ?: ($indexCompetencia + 1)), 2, '0', STR_PAD_LEFT);
                    $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($col) . $row, $nota?->nota ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($col + 1) . $row, $nota?->conclusionDescriptiva?->conclusion ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $col += 2;
                }

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }

            $legendStart = $row + 1;
            $sheet->mergeCells("A{$legendStart}:{$lastCol}{$legendStart}");
            $sheet->setCellValue("A{$legendStart}", 'LEYENDA');
            $sheet->getStyle("A{$legendStart}")->getFont()->setBold(true);

            $legendRow = $legendStart + 1;
            $sheet->setCellValue("A{$legendRow}", 'NL');
            $sheet->setCellValue("B{$legendRow}", 'Nivel de logro alcanzado');
            $legendRow++;

            foreach ($competencias as $index => $competencia) {
                $codigoComp = str_pad((string) ($competencia->orden ?: ($index + 1)), 2, '0', STR_PAD_LEFT);
                $sheet->setCellValue("A{$legendRow}", $codigoComp);
                $sheet->setCellValue("B{$legendRow}", $competencia->nombre);
                $legendRow++;
            }

            foreach (range(1, $maxCol) as $i) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
            $sheet->getColumnDimension('B')->setVisible(false);
            $sheet->getColumnDimension('C')->setVisible(false);
        }
    }

    private function nombreCompletoAlumno($alumno): string
    {
        if (!$alumno) {
            return '';
        }

        return trim(sprintf(
            '%s %s, %s',
            $alumno->apellido_paterno ?? '',
            $alumno->apellido_materno ?? '',
            $alumno->nombres ?? ''
        ));
    }

    private function sanitizarTituloHoja(?string $titulo): string
    {
        $titulo = trim((string) $titulo);
        $titulo = preg_replace('#[\\/\?\*\[\]:]#', '-', $titulo) ?: 'Hoja';
        return mb_substr($titulo, 0, 31);
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

    public function exportarUnificado(Request $request)
    {
        $request->validate([
            'anio_id'    => ['required', 'exists:anio_academicos,id'],
            'periodo_id' => ['required', 'exists:periodos,id'],
            'aula_id'    => ['required', 'exists:aulas,id'],
            'exportar_completo' => ['nullable', 'in:0,1'],
        ]);

        $user     = auth()->user();
        $anioId   = (int) $request->input('anio_id');
        $periodoId = (int) $request->input('periodo_id');
        $aulaId   = (int) $request->input('aula_id');
        $exportarCompleto = (bool) $request->input('exportar_completo', false);
        if ($exportarCompleto && !$this->puedeExportarCompleto($user)) {
            $exportarCompleto = false;
        }

        $anio    = AnioAcademico::findOrFail($anioId);
        $periodo = Periodo::with('anioAcademico')->findOrFail($periodoId);
        $aula    = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])->findOrFail($aulaId);

        $this->asegurarAccesoAula($user, $aula, $anioId);

        $cargas = $this->obtenerCargasPorAula($user, $aula->id, $anioId);
        if ($cargas->isEmpty()) {
            return back()->with('error', 'No hay cursos asignados para el aula seleccionada.');
        }

        $alumnos = $this->obtenerAlumnosPorAula($aula->id);
        if ($alumnos->isEmpty()) {
            return back()->with('error', 'El aula seleccionada no tiene alumnos matriculados activos.');
        }

        $institucion   = ConfiguracionInstitucion::getConfig();
        $notasGlobales = $this->obtenerNotasGlobales($periodoId, $alumnos, $cargas);
        $cuadrosHabilitados = ConfiguracionLibretaCuadro::getCuadrosForNivel($aula->nivel_id);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->crearHojaGeneralidades($spreadsheet, $institucion, $anio, $periodo, $aula, $cargas, $alumnos);
        $this->crearHojaUnificadaCursos(
            $spreadsheet,
            $institucion,
            $anio,
            $periodo,
            $aula,
            $cargas,
            $alumnos,
            $notasGlobales,
            $exportarCompleto,
            $cuadrosHabilitados
        );

        if ($exportarCompleto) {
            $this->crearHojasComplementariasSegunConfig(
                $spreadsheet,
                $institucion,
                $anio,
                $periodo,
                $aula,
                $alumnos,
                $cuadrosHabilitados
            );
        }

        $nombreArchivo = sprintf(
            'reporte_unificado_%s_%s_%s.xlsx',
            $anio->anio,
            $periodo->nombre,
            str_replace(' ', '_', $aula->nombre_completo)
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            if (ob_get_length()) {
                @ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function crearHojaUnificadaCursos(
        Spreadsheet $spreadsheet,
        $institucion,
        AnioAcademico $anio,
        Periodo $periodo,
        Aula $aula,
        Collection $cargas,
        Collection $alumnos,
        Collection $notasGlobales,
        bool $incluirOtrosCuadros,
        ?array $cuadrosHabilitados
    ): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Todas las Áreas');
        $sheet->setShowGridLines(false);

        $nivelId       = $aula->nivel_id;
        $matriculaIds  = $alumnos->pluck('id')->filter()->values()->toArray();

        // ── Tipos de evaluación del nivel ─────────────────────────────────────
        $ctItems  = CompetenciaTransversal::where('activo', true)->where('nivel_id', $nivelId)->whereNull('deleted_at')->orderBy('orden')->orderBy('nombre')->get();
        $epItems  = Evaluacion::where('activo', true)->where('nivel_id', $nivelId)->whereNull('deleted_at')->orderBy('orden')->get();
        $eaItems  = EvaluacionActitudinal::where('activo', true)->where('nivel_id', $nivelId)->whereNull('deleted_at')->orderBy('orden')->get();
        $inaItems = TipoInasistencia::where('activo', true)->where('nivel_id', $nivelId)->whereNull('deleted_at')->orderBy('orden')->get();

        // ── Registros complementarios en bloque (evita N+1) ──────────────────
        $regCT  = !empty($matriculaIds) ? RegistroCompetenciaTransversal::whereIn('matricula_id', $matriculaIds)->where('periodo_id', $periodo->id)->get()->keyBy(fn($r) => $r->matricula_id . '_' . $r->competencia_transversal_id) : collect();
        $regApr = !empty($matriculaIds) ? Apreciacion::whereIn('matricula_id', $matriculaIds)->where('periodo_id', $periodo->id)->get()->keyBy('matricula_id') : collect();
        $regEP  = !empty($matriculaIds) ? RegistroEvaluacion::whereIn('matricula_id', $matriculaIds)->where('periodo_id', $periodo->id)->get()->keyBy(fn($r) => $r->matricula_id . '_' . $r->evaluacion_id) : collect();
        $regEA  = !empty($matriculaIds) ? RegistroEvaluacionActitudinal::whereIn('matricula_id', $matriculaIds)->where('periodo_id', $periodo->id)->get()->keyBy(fn($r) => $r->matricula_id . '_' . $r->eval_actitudinal_id) : collect();
        $regIna = !empty($matriculaIds) ? RegistroAsistencia::whereIn('matricula_id', $matriculaIds)->where('periodo_id', $periodo->id)->get()->keyBy(fn($r) => $r->matricula_id . '_' . $r->tipo_inasistencia_id) : collect();
        $regComportamiento = !empty($matriculaIds)
            ? RegistroOtraEvaluacion::with('tipoOtraEvaluacion')
                ->whereIn('matricula_id', $matriculaIds)
                ->where('periodo_id', $periodo->id)
                ->get()
                ->groupBy('matricula_id')
                ->map(function (Collection $items) {
                    $preferido = $items->first(function ($registro) {
                        $nombre = mb_strtolower((string) ($registro->tipoOtraEvaluacion?->nombre ?? ''), 'UTF-8');
                        return str_contains($nombre, 'comport');
                    });

                    return ($preferido ?? $items->first())?->valor ?? '';
                })
            : collect();

        // ── Estructura de columnas por curso ─────────────────────────────────
        $cursosData = $cargas->map(fn($carga) => [
            'carga'        => $carga,
            'curso'        => $carga->curso,
            'competencias' => $carga->curso?->competencias?->where('activo', true)->sortBy('orden')->values() ?? collect(),
        ]);

        $colFixed       = 4; // N°, ID(hidden), Cód, Nombre
        $colCursosStart = $colFixed + 1;
        $totalCursosCols = $cursosData->sum(fn($d) => max(1, $d['competencias']->count()));

        // ── Secciones complementarias: [label, items, ancho_por_col] ─────────
        $secciones = [];
        if ($incluirOtrosCuadros && $this->cuadroHabilitado($cuadrosHabilitados, 'competencias_transversales') && $ctItems->isNotEmpty()) {
            $secciones[] = ['label' => 'Comp. Transversales', 'items' => $ctItems, 'key' => 'ct', 'width' => 14];
        }
        if ($incluirOtrosCuadros && $this->cuadroHabilitado($cuadrosHabilitados, 'apreciaciones_tutor')) {
            $secciones[] = ['label' => 'Apreciación Tutor', 'items' => collect([null]), 'key' => 'apr', 'width' => 30];
        }
        if ($incluirOtrosCuadros && $this->cuadroHabilitado($cuadrosHabilitados, 'evaluacion_padre') && $epItems->isNotEmpty()) {
            $secciones[] = ['label' => 'Evaluación Padre', 'items' => $epItems, 'key' => 'ep', 'width' => 14];
        }
        if ($incluirOtrosCuadros && $this->cuadroHabilitado($cuadrosHabilitados, 'evaluaciones_actitudinales') && $eaItems->isNotEmpty()) {
            $secciones[] = ['label' => 'Eval. Actitudinal', 'items' => $eaItems, 'key' => 'ea', 'width' => 14];
        }
        if ($incluirOtrosCuadros && $this->cuadroHabilitado($cuadrosHabilitados, 'inasistencias') && $inaItems->isNotEmpty()) {
            $secciones[] = ['label' => 'Inasistencias', 'items' => $inaItems, 'key' => 'ina', 'width' => 10];
        }
        $secciones[] = ['label' => 'COMPORTAMIENTO', 'items' => collect([null]), 'key' => 'comp', 'width' => 7];
        $secciones[] = ['label' => 'Orden de Mérito', 'items' => collect([null]), 'key' => 'om', 'width' => 7];

        // Calcular el offset de inicio de cada sección
        $offset = $colCursosStart + $totalCursosCols;
        foreach ($secciones as &$sec) {
            $sec['colStart'] = $offset;
            $sec['count']    = $sec['items']->count();
            $offset         += $sec['count'];
        }
        unset($sec);

        $totalCols = $offset - 1;
        $lastCol   = Coordinate::stringFromColumnIndex($totalCols);

        // ── Filas 1-3: cabecera ───────────────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'REPORTE UNIFICADO DE NOTAS');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', sprintf('Año académico: %s | Periodo: %s | Aula: %s', $anio->anio, $periodo->nombre, $aula->nombre_completo));
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ── Fila 4-5: headers ─────────────────────────────────────────────────
        $sheet->mergeCells('A4:A5'); $sheet->setCellValue('A4', 'N°');
        $sheet->mergeCells('B4:B5'); $sheet->setCellValue('B4', 'ID');
        $sheet->mergeCells('C4:C5'); $sheet->setCellValue('C4', 'Cód. Estudiante');
        $sheet->mergeCells('D4:D5'); $sheet->setCellValue('D4', 'Apellidos y Nombres');

        // Headers de cursos
        $col = $colCursosStart;
        foreach ($cursosData as $item) {
            $numComps = max(1, $item['competencias']->count());
            $spanCols = $numComps;
            $cStart   = Coordinate::stringFromColumnIndex($col);
            $cEnd     = Coordinate::stringFromColumnIndex($col + $spanCols - 1);
            if ($spanCols > 1) {
                $sheet->mergeCells("{$cStart}4:{$cEnd}4");
            }
            $sheet->setCellValue("{$cStart}4", $item['curso']?->nombre ?? '—');
            $subCol = $col;
            foreach ($item['competencias'] as $idx => $comp) {
                $code    = str_pad((string) ($comp->orden ?: ($idx + 1)), 2, '0', STR_PAD_LEFT);
                $nlCol   = Coordinate::stringFromColumnIndex($subCol);
                $sheet->setCellValue("{$nlCol}5", $code . ' NL');
                $subCol += 1;
            }
            if ($item['competencias']->isEmpty()) {
                $sheet->setCellValue("{$cStart}5", 'NL');
            }
            $col += $spanCols;
        }

        // Headers de secciones complementarias
        foreach ($secciones as $sec) {
            $colIni = Coordinate::stringFromColumnIndex($sec['colStart']);
            $colFin = Coordinate::stringFromColumnIndex($sec['colStart'] + $sec['count'] - 1);
            if ($sec['count'] > 1) {
                $sheet->mergeCells("{$colIni}4:{$colFin}4");
            } else {
                $sheet->mergeCells("{$colIni}4:{$colIni}5");
            }
            $sheet->setCellValue("{$colIni}4", $sec['label']);

            // Sub-headers fila 5 (sólo si hay más de 1 item, es decir tiene nombres individuales)
            if ($sec['count'] > 1) {
                foreach ($sec['items'] as $idx => $item) {
                    $subColLetter = Coordinate::stringFromColumnIndex($sec['colStart'] + $idx);
                    $nombre = $item?->nombre ?? '';
                    $sheet->setCellValue("{$subColLetter}5", $nombre);
                }
            }
        }

        $sheet->getStyle("A4:{$lastCol}5")->applyFromArray($this->headerStyle('#065f46'));
        $sheet->getStyle("A4:{$lastCol}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:{$lastCol}5")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A4:{$lastCol}5")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(95);
        $sheet->getRowDimension(5)->setRowHeight(22);

        // ── Filas de datos ────────────────────────────────────────────────────
        $ordenesMerito = $this->obtenerOrdenMeritoPorMatriculas($alumnos, $periodo->id);
        $row = 6;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("C{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("D{$row}", $this->nombreCompletoAlumno($alumno));

            // Notas por curso
            $col = $colCursosStart;
            foreach ($cursosData as $item) {
                $numComps = max(1, $item['competencias']->count());
                $subCol   = $col;
                foreach ($item['competencias'] as $comp) {
                    $nota = $notasGlobales[$matricula->id . '_' . $comp->id] ?? null;
                    $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($subCol) . $row, $nota?->nota ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $subCol += 1;
                }
                $col += $numComps;
            }

            // Secciones complementarias
            foreach ($secciones as $sec) {
                switch ($sec['key']) {
                    case 'ct':
                        foreach ($ctItems as $idx => $ctItem) {
                            $reg = $regCT[$matricula->id . '_' . $ctItem->id] ?? null;
                            $letter = Coordinate::stringFromColumnIndex($sec['colStart'] + $idx);
                            $sheet->setCellValueExplicit("{$letter}{$row}", $reg?->nota ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                        break;
                    case 'apr':
                        $reg = $regApr[$matricula->id] ?? null;
                        $letter = Coordinate::stringFromColumnIndex($sec['colStart']);
                        $sheet->setCellValueExplicit("{$letter}{$row}", $reg?->apreciacion ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->getStyle("{$letter}{$row}")->getAlignment()->setWrapText(true);
                        break;
                    case 'ep':
                        foreach ($epItems as $idx => $epItem) {
                            $reg = $regEP[$matricula->id . '_' . $epItem->id] ?? null;
                            $letter = Coordinate::stringFromColumnIndex($sec['colStart'] + $idx);
                            $sheet->setCellValueExplicit("{$letter}{$row}", $reg?->valoracion ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                        break;
                    case 'ea':
                        foreach ($eaItems as $idx => $eaItem) {
                            $reg = $regEA[$matricula->id . '_' . $eaItem->id] ?? null;
                            $letter = Coordinate::stringFromColumnIndex($sec['colStart'] + $idx);
                            $sheet->setCellValueExplicit("{$letter}{$row}", $reg?->valoracion ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                        break;
                    case 'ina':
                        foreach ($inaItems as $idx => $inaItem) {
                            $reg = $regIna[$matricula->id . '_' . $inaItem->id] ?? null;
                            $letter = Coordinate::stringFromColumnIndex($sec['colStart'] + $idx);
                            $sheet->setCellValue("{$letter}{$row}", $reg?->cantidad ?? 0);
                        }
                        break;
                    case 'comp':
                        $letter = Coordinate::stringFromColumnIndex($sec['colStart']);
                        $sheet->setCellValueExplicit("{$letter}{$row}", (string) ($regComportamiento[$matricula->id] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        break;
                    case 'om':
                        $ordenMerito = $ordenesMerito[$matricula->id] ?? null;
                        $letter = Coordinate::stringFromColumnIndex($sec['colStart']);
                        $sheet->setCellValue("{$letter}{$row}", $ordenMerito?->nota_valor ?? '—');
                        break;
                }
            }

            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // ── Anchos de columna ─────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('C')->setVisible(false);
        $sheet->getColumnDimension('D')->setWidth(30);

        $col = $colCursosStart;
        foreach ($cursosData as $item) {
            $numComps = max(1, $item['competencias']->count());
            for ($i = 0; $i < $numComps; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + $i))->setWidth(6);
            }
            $col += $numComps;
        }

        foreach ($secciones as $sec) {
            for ($i = 0; $i < $sec['count']; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($sec['colStart'] + $i))->setWidth($sec['width']);
            }
        }
    }

    private function crearHojasComplementariasSegunConfig(
        Spreadsheet $spreadsheet,
        $institucion,
        AnioAcademico $anio,
        Periodo $periodo,
        Aula $aula,
        Collection $alumnos,
        ?array $cuadrosHabilitados
    ): void {
        if ($this->cuadroHabilitado($cuadrosHabilitados, 'competencias_transversales')) {
            $this->crearHojaCompetenciasTransversales($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'apreciaciones_tutor')) {
            $this->crearHojaApreciaciones($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'evaluacion_padre')) {
            $this->crearHojaEvaluacionPadre($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'evaluaciones_actitudinales')) {
            $this->crearHojaEvaluacionActitudinal($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'otras_evaluaciones')) {
            $this->crearHojaOtrasEvaluaciones($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'inasistencias')) {
            $this->crearHojaInasistencias($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }

        if ($this->cuadroHabilitado($cuadrosHabilitados, 'orden_merito')) {
            $this->crearHojaOrdenMerito($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        }
    }

    private function cuadroHabilitado(?array $cuadrosHabilitados, string $clave): bool
    {
        return $cuadrosHabilitados === null || in_array($clave, $cuadrosHabilitados, true);
    }

    private function crearHojasComplementarias(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $this->crearHojaCompetenciasTransversales($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        $this->crearHojaApreciaciones($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        $this->crearHojaEvaluacionPadre($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        $this->crearHojaEvaluacionActitudinal($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
        $this->crearHojaOtrasEvaluaciones($spreadsheet, $institucion, $anio, $periodo, $aula, $alumnos);
    }

    private function obtenerOrdenMeritoPorMatriculas(Collection $alumnos, int $periodoId): Collection
    {
        $matriculaIds = $alumnos->pluck('id')->filter()->values();
        if ($matriculaIds->isEmpty()) {
            return collect();
        }

        return RegistroOrdenMerito::query()
            ->where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->get()
            ->keyBy('matricula_id');
    }

    private function obtenerCompetenciasTransversalesAlumno(int $matriculaId, int $periodoId): array
    {
        $registros = RegistroCompetenciaTransversal::with('competenciaTransversal')
            ->where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->get();
        
        return $registros->map(function ($reg) {
            $nombre = $reg->competenciaTransversal?->nombre ?? 'Sin nombre';
            $nota = $reg->nota ?? '—';
            return "{$nombre}: {$nota}";
        })->toArray();
    }

    private function obtenerApreciacionAlumno(int $matriculaId, int $periodoId): string
    {
        $apreciacion = Apreciacion::where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->first();
        
        return $apreciacion?->apreciacion ?? '—';
    }

    private function obtenerEvaluacionActitudinalAlumno(int $matriculaId, int $periodoId): array
    {
        $registros = RegistroEvaluacionActitudinal::with('evaluacionActitudinal')
            ->where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->get();
        
        return $registros->map(function ($reg) {
            $nombre = $reg->evaluacionActitudinal?->nombre ?? 'Sin nombre';
            $valoracion = $reg->valoracion_nombre ?? $reg->valoracion ?? '—';
            return "{$nombre}: {$valoracion}";
        })->toArray();
    }

    private function obtenerEvaluacionPadreAlumno(int $matriculaId, int $periodoId): array
    {
        $registros = RegistroEvaluacion::with('evaluacion')
            ->where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->get();

        return $registros->map(function ($reg) {
            $nombre = $reg->evaluacion?->nombre ?? 'Sin nombre';
            $valoracion = $reg->valoracion_nombre ?? $reg->valoracion ?? '—';
            return "{$nombre}: {$valoracion}";
        })->toArray();
    }

    private function obtenerInasistenciasAlumno(int $matriculaId, int $periodoId): array
    {
        $registros = RegistroAsistencia::with('tipoInasistencia')
            ->where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->whereNotNull('cantidad')
            ->get();

        return $registros->map(function ($reg) {
            $nombre = $reg->tipoInasistencia?->nombre ?? 'Sin tipo';
            $cantidad = (string) ($reg->cantidad ?? 0);
            return "{$nombre}: {$cantidad}";
        })->toArray();
    }

    private function crearHojaEvaluacionPadre(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Evaluación Padre');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'EVALUACIÓN AL PADRE DE FAMILIA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Evaluación Padre de Familia');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $evaluaciones = $this->obtenerEvaluacionPadreAlumno($matricula->id, $periodo->id);
            $texto = implode("\n", $evaluaciones);

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $texto);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function obtenerOtrasEvaluacionesAlumno(int $matriculaId, int $periodoId): array
    {
        $registros = RegistroOtraEvaluacion::with('tipoOtraEvaluacion')
            ->where('matricula_id', $matriculaId)
            ->where('periodo_id', $periodoId)
            ->get();
        
        return $registros->map(function ($reg) {
            $nombre = $reg->tipoOtraEvaluacion?->nombre ?? 'Sin nombre';
            $valor = $reg->valor ?? '—';
            return "{$nombre}: {$valor}";
        })->toArray();
    }

    private function crearHojaCompetenciasTransversales(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Competencias Transversales');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'COMPETENCIAS TRANSVERSALES');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Competencias Transversales');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $competencias = $this->obtenerCompetenciasTransversalesAlumno($matricula->id, $periodo->id);
            $texto = implode("\n", $competencias);
            
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $texto);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function crearHojaApreciaciones(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Apreciaciones del Tutor');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'APRECIACIONES DEL TUTOR');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Apreciaciones');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $apreciacion = $this->obtenerApreciacionAlumno($matricula->id, $periodo->id);
            
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $apreciacion);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function crearHojaEvaluacionActitudinal(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Evaluación Actitudinal');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'EVALUACIÓN ACTITUDINAL');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Conducta');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $evaluaciones = $this->obtenerEvaluacionActitudinalAlumno($matricula->id, $periodo->id);
            $texto = implode("\n", $evaluaciones);
            
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $texto);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function crearHojaOtrasEvaluaciones(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Otras Evaluaciones');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'OTRAS EVALUACIONES');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Evaluaciones');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $evaluaciones = $this->obtenerOtrasEvaluacionesAlumno($matricula->id, $periodo->id);
            $texto = implode("\n", $evaluaciones);
            
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $texto);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function crearHojaInasistencias(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Inasistencias');
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'INASISTENCIAS');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'Inasistencias');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle('#065f46'));

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $inasistencias = $this->obtenerInasistenciasAlumno($matricula->id, $periodo->id);
            $texto = implode("\n", $inasistencias);

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $texto);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getStyle('D4:D' . ($row - 1))->getAlignment()->setWrapText(true);
    }

    private function crearHojaOrdenMerito(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $alumnos): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($this->sanitizarTituloHoja('Orden Mérito'));
        $sheet->setShowGridLines(false);

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', strtoupper($institucion->nombre ?? 'INSTITUCIÓN EDUCATIVA'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'ORDEN DE MÉRITO');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', sprintf(
            'Año: %s | Periodo: %s | Aula: %s',
            $anio->anio,
            $periodo->nombre,
            $aula->nombre_completo
        ));
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $sheet->setCellValue('A4', 'N°');
        $sheet->setCellValue('B4', 'Cód. Estudiante');
        $sheet->setCellValue('C4', 'Apellidos y Nombres');
        $sheet->setCellValue('D4', 'N° de Orden');
        $sheet->setCellValue('E4', 'Observación');
        $sheet->getStyle('A4:E4')->applyFromArray($this->headerStyle('#065f46'));

        $ordenesMerito = $this->obtenerOrdenMeritoPorMatriculas($alumnos, $periodo->id);

        $row = 5;
        foreach ($alumnos as $index => $matricula) {
            $alumno = $matricula->alumno;
            $registro = $ordenesMerito[$matricula->id] ?? null;

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $alumno?->codigo_estudiante ?? $alumno?->dni ?? '');
            $sheet->setCellValue("C{$row}", $this->nombreCompletoAlumno($alumno));
            $sheet->setCellValue("D{$row}", $registro?->nota_valor ?? '—');
            $sheet->setCellValue("E{$row}", $registro?->observacion ?? '');
            $sheet->getStyle("E{$row}")->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setVisible(false);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setWidth(45);
    }
}

