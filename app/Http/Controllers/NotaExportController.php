<?php
// app/Http/Controllers/NotaExportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aula;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\Competencia;
use App\Models\Nota;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class NotaExportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $aulaId = $request->input('aula_id');
        $cursoId = $request->input('curso_id');
        $periodoId = $request->input('periodo_id');

        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])->find($aulaId);
        $curso = Curso::with('nivel')->find($cursoId);
        $periodo = Periodo::with('anioAcademico')->find($periodoId);

        if (!$aula || !$curso || !$periodo) {
            return response()->json(['error' => 'Datos no encontrados'], 404);
        }

        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $aulaId)
            ->where('estado', 'activa')
            ->orderBy(DB::raw('CONCAT((SELECT apellido_paterno FROM alumnos WHERE alumnos.id = matriculas.alumno_id), " ", (SELECT apellido_materno FROM alumnos WHERE alumnos.id = matriculas.alumno_id), " ", (SELECT nombres FROM alumnos WHERE alumnos.id = matriculas.alumno_id))'))
            ->get();

        $competencias = Competencia::where('curso_id', $cursoId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $matriculaIds = $matriculas->pluck('id')->toArray();
        $competenciaIds = $competencias->pluck('id')->toArray();

        $notas = Nota::where('periodo_id', $periodoId)
            ->whereIn('matricula_id', $matriculaIds)
            ->whereIn('competencia_id', $competenciaIds)
            ->get()
            ->keyBy(function($nota) {
                return $nota->matricula_id . '_' . $nota->competencia_id;
            });

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->setShowGridLines(false);

        $totalColumns = 2 + max(1, $competencias->count());
        $lastCol = Coordinate::stringFromColumnIndex($totalColumns);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REGISTRO DE NOTAS');
        $sheet->getStyle('A1')->applyFromArray($this->headerStyle('#065f46'));
        $sheet->getStyle('A1')->getFont()->setSize(14);

        $aulaTexto = ($aula->grado?->nivel?->nombre ?? '-') . ' - ' . ($aula->grado?->nombre ?? '-') . ' "' . ($aula->seccion?->nombre ?? '-') . '" (' . ($aula->turno_nombre ?? '-') . ') - ' . ($aula->anioAcademico?->anio ?? '-');
        $sheet->setCellValue('A3', 'Aula:');
        $sheet->mergeCells("B3:{$lastCol}3");
        $sheet->setCellValue('B3', $aulaTexto);
        $sheet->setCellValue('A4', 'Curso:');
        $sheet->mergeCells("B4:{$lastCol}4");
        $sheet->setCellValue('B4', $curso->nombre ?? '-');
        $sheet->setCellValue('A5', 'Periodo:');
        $sheet->mergeCells("B5:{$lastCol}5");
        $sheet->setCellValue('B5', ($periodo->nombre ?? '-') . ' - ' . ($periodo->anioAcademico?->anio ?? '-'));
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        $row = 7;
        $sheet->setCellValue('A' . $row, 'N°');
        $sheet->setCellValue('B' . $row, 'Alumno');
        $colIndex = 3;
        foreach ($competencias as $comp) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($col . $row, $comp->abreviatura ?: $comp->nombre);
            $colIndex++;
        }
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($this->headerStyle('#065f46'));
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true);
        $row++;

        $dataStartRow = $row;
        // Datos de alumnos y notas
        $num = 1;
        foreach ($matriculas as $matricula) {
            $sheet->setCellValue('A'.$row, $num);
            $alumno = $matricula->alumno;
            $sheet->setCellValue('B'.$row, trim(($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '') . ' ' . ($alumno->nombres ?? '')));
            $colIndex = 3;
            foreach ($competencias as $comp) {
                $key = $matricula->id . '_' . $comp->id;
                $nota = $notas[$key]->nota ?? '';
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($col . $row, $nota);
                $colIndex++;
            }
            $row++;
            $num++;
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

        // Descargar
        $fileName = 'notas_'.$aula->id.'_'.$curso->id.'_'.$periodo->id.'_'.date('Ymd_His').'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'notas_');
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
