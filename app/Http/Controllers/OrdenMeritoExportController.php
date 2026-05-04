<?php

namespace App\Http\Controllers;

use App\Models\AnioAcademico;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\RegistroOrdenMerito;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrdenMeritoExportController extends Controller
{
    public function index()
    {
        $anios = AnioAcademico::orderByDesc('anio')->get();
        $niveles = Nivel::activo()->orderBy('orden')->orderBy('nombre')->get();
        $anioActivo = AnioAcademico::where('activo', true)->first();
        $ordenesDisponibles = range(1, 40);

        return view('libretas.orden-merito.index', compact('anios', 'niveles', 'anioActivo', 'ordenesDisponibles'));
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'anio_id' => ['required', 'exists:anio_academicos,id'],
            'nivel_id' => ['required', 'string'],
            'orden_merito' => ['required', 'array', 'min:1'],
            'orden_merito.*' => ['integer', 'min:1', 'max:40'],
        ]);

        $anioId = (int) $request->input('anio_id');
        $nivelInput = $request->input('nivel_id');
        $esTodos = $nivelInput === 'todos';
        $nivelId = $esTodos ? null : (int) $nivelInput;
        $ordenesMerito = collect($request->input('orden_merito', []))
            ->map(fn ($valor) => (int) $valor)
            ->filter(fn ($valor) => $valor >= 1 && $valor <= 40)
            ->unique()
            ->values()
            ->all();

        if (empty($ordenesMerito)) {
            return back()->with('error', 'Debes seleccionar al menos un orden de mérito válido.');
        }

        $anio = AnioAcademico::findOrFail($anioId);
        $nivel = $esTodos ? null : Nivel::findOrFail($nivelId);

        $registros = RegistroOrdenMerito::with([
                'matricula.alumno',
                'matricula.aula.grado.nivel',
                'matricula.aula.seccion',
                'matricula.aula.anioAcademico',
                'periodo.anioAcademico',
            ])
            ->whereIn('nota_valor', $ordenesMerito)
            ->whereHas('periodo', function ($query) use ($anioId) {
                $query->where('anio_academico_id', $anioId);
            })
            ->whereHas('matricula', function ($query) {
                $query->where('estado', Matricula::ESTADO_ACTIVA);
            })
            ->whereHas('matricula.aula', function ($query) use ($anioId, $nivelId) {
                $query->where('anio_academico_id', $anioId)
                    ->when($nivelId !== null, function ($aulaQuery) use ($nivelId) {
                        $aulaQuery->whereHas('grado', function ($gradoQuery) use ($nivelId) {
                            $gradoQuery->where('nivel_id', $nivelId);
                        });
                    });
            })
            ->get()
            ->sortBy(function ($registro) {
                return [
                    $registro->matricula->aula->grado->nivel->orden ?? 0,
                    $registro->matricula->aula->grado->nivel->nombre ?? '',
                    $registro->matricula->aula->grado->nombre ?? '',
                    $registro->matricula->aula->seccion->nombre ?? '',
                    $registro->periodo->orden ?? 0,
                    $registro->matricula->alumno->apellido_paterno ?? '',
                    $registro->matricula->alumno->apellido_materno ?? '',
                    $registro->matricula->alumno->nombres ?? '',
                ];
            })
            ->values();

        if ($registros->isEmpty()) {
            return back()->with('error', 'No se encontraron registros para el año, nivel y orden seleccionados.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Orden Mérito');
        $sheet->setShowGridLines(false);
        $sheet->freezePane('A5');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'EXPORTACIÓN DE ORDEN DE MÉRITO');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', sprintf('Año académico: %s | Nivel: %s | Orden(es): %s', $anio->anio, $nivel?->nombre ?? 'Todos', implode(', ', $ordenesMerito)));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        $headers = [
            'A4' => 'N°',
            'B4' => 'Aula',
            'C4' => 'Año académico',
            'D4' => 'Periodo académico',
            'E4' => 'Nivel',
            'F4' => 'Alumno',
            'G4' => 'N° de Orden',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A472A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        $row = 5;
        $contador = 1;

        foreach ($registros as $registro) {
            $matricula = $registro->matricula;
            $aula = $matricula?->aula;
            $alumno = $matricula?->alumno;
            $periodo = $registro->periodo;
            $nivelRegistro = $aula?->grado?->nivel;

            $sheet->setCellValue('A' . $row, $contador);
            $sheet->setCellValue('B' . $row, $aula?->nombre_completo ?? '-');
            $sheet->setCellValue('C' . $row, $aula?->anioAcademico?->anio ?? $anio->anio);
            $sheet->setCellValue('D' . $row, $periodo?->nombre_completo ?? ($periodo?->nombre ?? '-'));
            $sheet->setCellValue('E' . $row, $nivelRegistro?->nombre ?? $nivel->nombre);
            $sheet->setCellValue('F' . $row, trim(($alumno?->apellido_paterno ?? '') . ' ' . ($alumno?->apellido_materno ?? '') . ', ' . ($alumno?->nombres ?? '')));
            $sheet->setCellValue('G' . $row, $registro->nota_valor);

            $row++;
            $contador++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A4:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BFBFBF'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C5:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G5:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (['A' => 7, 'B' => 35, 'C' => 16, 'D' => 22, 'E' => 18, 'F' => 38, 'G' => 14] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->setAutoFilter('A4:G' . $lastRow);
        $sheet->setAutoFilter('A4:G4');
        $sheet->getStyle('A5:G' . $lastRow)->getAlignment()->setWrapText(true);

        $nombreArchivo = sprintf(
            'orden_merito_%s_%s_%s.xlsx',
            $anio->anio,
            preg_replace('/\s+/', '_', $nivel?->nombre ?? 'Todos'),
            implode('-', $ordenesMerito)
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
}
