<?php

namespace App\Http\Controllers;

use App\Models\AnioAcademico;
use App\Models\Aula;
use App\Models\CargaHoraria;
use App\Models\ConfiguracionInstitucion;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Periodo;
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
    public function index(Request $request)
    {
        $user = auth()->user();
        $anioSeleccionado = $this->obtenerAnioSeleccionado($request);
        $anios = AnioAcademico::orderByDesc('anio')->get();
        $periodos = $this->obtenerPeriodosPorAnio($anioSeleccionado?->id);
        $aulas = $this->obtenerAulasPorUsuario($user, $anioSeleccionado?->id);

        $periodoSeleccionado = $this->obtenerPeriodoSeleccionado($request, $periodos);
        $aulaSeleccionada = $this->obtenerAulaSeleccionada($request, $aulas);

        return view('reportes.notas.index', compact(
            'anios',
            'anioSeleccionado',
            'periodos',
            'periodoSeleccionado',
            'aulas',
            'aulaSeleccionada'
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
        ]);

        $user = auth()->user();
        $anioId = (int) $request->input('anio_id');
        $periodoId = (int) $request->input('periodo_id');
        $aulaId = (int) $request->input('aula_id');

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

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->crearHojaGeneralidades($spreadsheet, $institucion, $anio, $periodo, $aula, $cargas, $alumnos);
        $this->crearHojasPorCurso($spreadsheet, $institucion, $anio, $periodo, $aula, $cargas, $alumnos, $notasGlobales);

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
            $query->whereHas('cargaHoraria', function ($q) use ($userId, $anioId) {
                $q->where('docente_id', $userId)
                    ->where('estado', CargaHoraria::ESTADO_ACTIVO)
                    ->when($anioId, function ($subQuery) use ($anioId) {
                        $subQuery->whereHas('aula', function ($aulaQuery) use ($anioId) {
                            $aulaQuery->where('anio_academico_id', $anioId);
                        });
                    });
            });
        }

        return $query->orderBy('grado_id')->orderBy('seccion_id')->get();
    }

    private function obtenerCargasPorAula($user, int $aulaId, int $anioId): Collection
    {
        $query = CargaHoraria::with([
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

        if (!$user || !$user->isAdmin()) {
            $query->where('docente_id', $user?->id);
        }

        return $query->orderBy('curso_id')->get()->groupBy('curso_id')->map(function ($grupo) {
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

        $tieneAcceso = CargaHoraria::where('docente_id', $user?->id)
            ->where('aula_id', $aula->id)
            ->where('estado', CargaHoraria::ESTADO_ACTIVO)
            ->whereHas('aula', function ($q) use ($anioId) {
                $q->where('anio_academico_id', $anioId);
            })
            ->exists();

        abort_if(!$tieneAcceso, 403, 'No tienes permiso para exportar esta aula.');
    }

    private function crearHojaGeneralidades(Spreadsheet $spreadsheet, $institucion, AnioAcademico $anio, Periodo $periodo, Aula $aula, Collection $cargas, Collection $alumnos): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Generalidades');
        $sheet->setShowGridLines(false);
        $sheet->freezePane('A8');

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
        $sheet->getStyle('A14:F14')->applyFromArray($this->headerStyle('#065f46'));

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

        $sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));
        $sheet->setCellValue('A' . ($row + 1), 'Leyenda: NL = Nivel de logro alcanzado. Cada hoja del archivo contiene una competencia y su conclusión descriptiva cuando exista.');
        $sheet->getStyle('A' . ($row + 1))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:B12')->getFont()->setBold(true);
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
            $sheet->freezePane('D6');

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
                $sheet->setCellValue("B{$row}", $alumno?->id ?? '');
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
}
