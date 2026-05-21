<?php

namespace App\Http\Controllers;

use App\Models\PagoImportado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportacionPagosController extends Controller
{
    public function countByYear(Request $request)
    {
        $validated = $request->validate([
            'anio_emision' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $anioEmision = (int) $validated['anio_emision'];
        $cantidad = PagoImportado::where('anio_emision', $anioEmision)->count();

        return response()->json([
            'success' => true,
            'anio_emision' => $anioEmision,
            'count' => $cantidad,
            'has_records' => $cantidad > 0,
        ]);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $pagos = PagoImportado::query()
            ->search($search)
            ->orderByDesc('anio_emision')
            ->orderBy('estudiante')
            ->paginate(20)
            ->withQueryString();

        return view('pagos-importados.index', compact('pagos', 'search'));
    }

    public function resumen(Request $request)
    {
        $anioEmision = $request->get('anio_emision', null);
        
        // Obtener años disponibles
        $anosDisponibles = PagoImportado::distinct()
            ->orderByDesc('anio_emision')
            ->pluck('anio_emision')
            ->toArray();

        if (!$anioEmision && count($anosDisponibles) > 0) {
            $anioEmision = $anosDisponibles[0];
        }

        // Datos agrupados por nivel, grado, sección
        $resumen = [];
        $graficoDatos = [
            'niveles' => [],
            'series' => [],
        ];

        if ($anioEmision) {
            $query = PagoImportado::where('anio_emision', $anioEmision);
            
            $registros = $query->get();

            $meses = ['marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];

            // Agrupar por nivel -> grado -> sección
            foreach ($registros as $registro) {
                $nivel = $registro->nivel ?: 'Sin Nivel';
                $grado = $registro->grado ?: 'Sin Grado';
                $seccion = $registro->seccion ?: 'Sin Sección';

                if (!isset($resumen[$nivel])) {
                    $resumen[$nivel] = [];
                }

                if (!isset($resumen[$nivel][$grado])) {
                    $resumen[$nivel][$grado] = [];
                }

                if (!isset($resumen[$nivel][$grado][$seccion])) {
                    $resumen[$nivel][$grado][$seccion] = [
                        'total_estudiantes' => 0,
                        'pagos_por_mes' => array_fill_keys($meses, 0),
                        'monto_por_mes' => array_fill_keys($meses, 0),
                    ];
                }

                $resumen[$nivel][$grado][$seccion]['total_estudiantes']++;

                foreach ($meses as $mes) {
                    $montoMes = is_numeric($registro->{$mes}) ? (float) $registro->{$mes} : 0.0;

                    if ($montoMes > 0) {
                        $resumen[$nivel][$grado][$seccion]['pagos_por_mes'][$mes]++;
                    }

                    $resumen[$nivel][$grado][$seccion]['monto_por_mes'][$mes] += $montoMes;
                }
            }

            // Preparar datos para gráfico
            foreach ($resumen as $nivel => $grados) {
                if (!isset($graficoDatos['niveles'][$nivel])) {
                    $graficoDatos['niveles'][$nivel] = [];
                }

                foreach ($grados as $grado => $secciones) {
                    foreach ($secciones as $seccion => $data) {
                        $label = "{$grado} {$seccion}";
                        if (!isset($graficoDatos['niveles'][$nivel][$label])) {
                            $graficoDatos['niveles'][$nivel][$label] = $data;
                        }
                    }
                }
            }
        }

        return view('pagos-importados.resumen', compact('resumen', 'anosDisponibles', 'anioEmision', 'graficoDatos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'anio_emision' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $anioEmision = $request->input('anio_emision');
        $archivo = $request->file('archivo');
        $reader = IOFactory::createReaderForFile($archivo->getPathname());
        $spreadsheet = $reader->load($archivo->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $anioDetectado = $this->extractYearFromFirstRow($sheet);

        if (!$anioDetectado) {
            return back()
                ->withErrors(['archivo' => 'No se detecto un anio valido en la primera fila del Excel. Verifique el encabezado combinado con el anio de emision.'])
                ->withInput();
        }

        if ((int) $anioDetectado !== (int) $anioEmision) {
            return back()
                ->withErrors(['anio_emision' => "El anio del archivo ({$anioDetectado}) no coincide con el anio ingresado ({$anioEmision}). No se realizo la importacion."])
                ->withInput();
        }

        $importados = 0;
        $eliminados = 0;

        DB::transaction(function () use ($sheet, $highestRow, $anioEmision, &$importados, &$eliminados) {
            // Reemplazo total por anio: elimina y vuelve a cargar el Excel actualizado.
            $eliminados = PagoImportado::where('anio_emision', $anioEmision)->delete();
            $columnasMeses = $this->detectarColumnasMeses($sheet);

            for ($row = 5; $row <= $highestRow; $row++) {
                $numeroFila = $this->normalizeString($sheet->getCell("A{$row}")->getCalculatedValue());
                $estudiante = $this->normalizeString($sheet->getCell("B{$row}")->getCalculatedValue());
                $dniEst = $this->numericOnly($sheet->getCell("C{$row}")->getCalculatedValue());
                $docFacturacionRaw = $this->normalizeString($sheet->getCell("D{$row}")->getCalculatedValue());
                $nombreFacturacion = $this->normalizeString($sheet->getCell("E{$row}")->getCalculatedValue());
                $nivel = $this->normalizeString($sheet->getCell("F{$row}")->getCalculatedValue());
                $grado = $this->normalizeString($sheet->getCell("G{$row}")->getCalculatedValue());
                $seccion = $this->normalizeString($sheet->getCell("H{$row}")->getCalculatedValue());

                if ($estudiante === '' && $dniEst === '' && $nombreFacturacion === '') {
                    continue;
                }

                $rowData = [
                    'anio_emision' => $anioEmision,
                    'numero_fila' => $numeroFila !== '' ? (int) $numeroFila : null,
                    'estudiante' => $estudiante,
                    'dni_est' => $dniEst,
                    'doc_facturacion_dni' => $this->numericOnly($docFacturacionRaw),
                    'nombre_facturacion' => $nombreFacturacion,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'seccion' => $seccion,
                    'marzo' => $this->moneyByKey($sheet, $columnasMeses, 'marzo', $row),
                    'abril' => $this->moneyByKey($sheet, $columnasMeses, 'abril', $row),
                    'mayo' => $this->moneyByKey($sheet, $columnasMeses, 'mayo', $row),
                    'junio' => $this->moneyByKey($sheet, $columnasMeses, 'junio', $row),
                    'julio' => $this->moneyByKey($sheet, $columnasMeses, 'julio', $row),
                    'agosto' => $this->moneyByKey($sheet, $columnasMeses, 'agosto', $row),
                    'setiembre' => $this->moneyByKey($sheet, $columnasMeses, 'setiembre', $row),
                    'octubre' => $this->moneyByKey($sheet, $columnasMeses, 'octubre', $row),
                    'noviembre' => $this->moneyByKey($sheet, $columnasMeses, 'noviembre', $row),
                    'diciembre' => $this->moneyByKey($sheet, $columnasMeses, 'diciembre', $row),
                    'total' => $this->moneyByKey($sheet, $columnasMeses, 'total', $row),
                ];

                PagoImportado::create($rowData);
                $importados++;
            }
        });

        return redirect()
            ->route('admin.pagos-importados.index')
            ->with('success', "Importacion completada: {$importados} registros importados y {$eliminados} eliminados del anio {$anioEmision}.");
    }

    private function extractYearFromFirstRow(Worksheet $sheet): ?int
    {
        $textos = [];

        foreach ($sheet->getMergeCells() as $mergeRange) {
            [$inicio] = explode(':', $mergeRange);
            if (preg_match('/^[A-Z]+1$/', $inicio) === 1) {
                $texto = $this->normalizeString($sheet->getCell($inicio)->getCalculatedValue());
                if ($texto !== '') {
                    $textos[] = $texto;
                }
            }
        }

        if (empty($textos)) {
            $highestColumn = $sheet->getHighestColumn(1);
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $texto = $this->normalizeString($sheet->getCell($col . '1')->getCalculatedValue());
                if ($texto !== '') {
                    $textos[] = $texto;
                }
            }
        }

        if (empty($textos)) {
            return null;
        }

        $textoCompleto = implode(' ', $textos);
        if (preg_match('/\b(20\d{2}|2100)\b/', $textoCompleto, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function numericOnly($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        preg_match('/(\d+)/', $value, $matches);

        return $matches[1] ?? null;
    }

    private function detectarColumnasMeses(Worksheet $sheet): array
    {
        $map = [
            'marzo' => null,
            'abril' => null,
            'mayo' => null,
            'junio' => null,
            'julio' => null,
            'agosto' => null,
            'setiembre' => null,
            'octubre' => null,
            'noviembre' => null,
            'diciembre' => null,
            'total' => null,
        ];

        $headerRow = 4;
        $highestColumn = $sheet->getHighestColumn($headerRow);
        $highestIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($i = 1; $i <= $highestIndex; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $texto = $this->normalizeHeader((string) $sheet->getCell("{$col}{$headerRow}")->getCalculatedValue());

            if ($texto === '') {
                continue;
            }

            foreach (array_keys($map) as $key) {
                if ($texto === $key || str_contains($texto, $key)) {
                    $map[$key] = $col;
                }
            }
        }

        // Fallback fijo esperado por plantilla actual (incluye columna Matricula antes de marzo).
        $fallback = [
            'marzo' => 'J',
            'abril' => 'K',
            'mayo' => 'L',
            'junio' => 'M',
            'julio' => 'N',
            'agosto' => 'O',
            'setiembre' => 'P',
            'octubre' => 'Q',
            'noviembre' => 'R',
            'diciembre' => 'S',
            'total' => 'T',
        ];

        foreach ($fallback as $key => $columna) {
            if ($map[$key] === null) {
                $map[$key] = $columna;
            }
        }

        return $map;
    }

    private function moneyByKey(Worksheet $sheet, array $columnasMeses, string $key, int $row): ?string
    {
        $col = $columnasMeses[$key] ?? null;
        if (!$col) {
            return null;
        }

        return $this->moneyFromCell($sheet, "{$col}{$row}");
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $value);

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    private function moneyFromCell(Worksheet $sheet, string $cellRef): ?string
    {
        $cell = $sheet->getCell($cellRef);
        $rawValue = $cell->getValue();

        if ($rawValue instanceof RichText) {
            $rawValue = $rawValue->getPlainText();
        }

        return $this->moneyOnly(
            $cell->getCalculatedValue(),
            $cell->getFormattedValue(),
            $rawValue
        );
    }

    private function moneyOnly($value, $formattedValue = null, $rawValue = null): ?string
    {
        $value = trim((string) $value);
        $formattedValue = trim((string) ($formattedValue ?? ''));
        $rawValue = trim((string) ($rawValue ?? ''));

        if ($this->containsDebe($value) || $this->containsDebe($formattedValue) || $this->containsDebe($rawValue)) {
            return null;
        }

        $candidate = $value !== '' ? $value : ($formattedValue !== '' ? $formattedValue : $rawValue);

        if ($candidate === '') {
            return null;
        }

        $candidate = str_replace(['S/', 's/', ' '], '', $candidate);
        $candidate = str_replace(',', '', $candidate);

        // If any alphabetic text remains (e.g. "(DEBE)"), treat as unpaid and do not import amount.
        if (preg_match('/[[:alpha:]]/u', $candidate) === 1) {
            return null;
        }

        $candidate = str_replace(['(', ')'], '', $candidate);

        return is_numeric($candidate) ? number_format((float) $candidate, 2, '.', '') : null;
    }

    private function containsDebe(string $value): bool
    {
        return stripos($value, 'debe') !== false;
    }

    private function normalizeString($value): string
    {
        return trim((string) $value);
    }
}
