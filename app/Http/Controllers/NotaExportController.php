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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $row = 1;
        // Encabezado de metadatos
        $sheet->setCellValue('A'.$row, 'Aula:');
        $sheet->setCellValue('B'.$row, $aula->grado->nivel->nombre.' - '.$aula->grado->nombre.' "'.$aula->seccion->nombre.'" ('.$aula->turno_nombre.') - '.$aula->anioAcademico->anio);
        $row++;
        $sheet->setCellValue('A'.$row, 'Curso:');
        $sheet->setCellValue('B'.$row, $curso->nombre);
        $row++;
        $sheet->setCellValue('A'.$row, 'Periodo:');
        $sheet->setCellValue('B'.$row, $periodo->nombre.' - '.$periodo->anioAcademico->anio);
        $row += 2;
        // Encabezado de tabla
        $sheet->setCellValue('A'.$row, 'N°');
        $sheet->setCellValue('B'.$row, 'Alumno');
        $col = 'C';
        foreach ($competencias as $comp) {
            $sheet->setCellValue($col.$row, $comp->abreviatura ?: $comp->nombre);
            $col++;
        }
        $row++;
        // Datos de alumnos y notas
        $num = 1;
        foreach ($matriculas as $matricula) {
            $sheet->setCellValue('A'.$row, $num);
            $alumno = $matricula->alumno;
            $sheet->setCellValue('B'.$row, $alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombres);
            $col = 'C';
            foreach ($competencias as $comp) {
                $key = $matricula->id . '_' . $comp->id;
                $nota = $notas[$key]->nota ?? '';
                $sheet->setCellValue($col.$row, $nota);
                $col++;
            }
            $row++;
            $num++;
        }
        // Descargar
        $fileName = 'notas_'.$aula->id.'_'.$curso->id.'_'.$periodo->id.'_'.date('Ymd_His').'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'notas_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
