<?php

namespace App\Http\Controllers;

use App\Models\PagoImportado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
                    'marzo' => $this->moneyOnly($sheet->getCell("I{$row}")->getCalculatedValue()),
                    'abril' => $this->moneyOnly($sheet->getCell("J{$row}")->getCalculatedValue()),
                    'mayo' => $this->moneyOnly($sheet->getCell("K{$row}")->getCalculatedValue()),
                    'junio' => $this->moneyOnly($sheet->getCell("L{$row}")->getCalculatedValue()),
                    'julio' => $this->moneyOnly($sheet->getCell("M{$row}")->getCalculatedValue()),
                    'agosto' => $this->moneyOnly($sheet->getCell("N{$row}")->getCalculatedValue()),
                    'setiembre' => $this->moneyOnly($sheet->getCell("O{$row}")->getCalculatedValue()),
                    'octubre' => $this->moneyOnly($sheet->getCell("P{$row}")->getCalculatedValue()),
                    'noviembre' => $this->moneyOnly($sheet->getCell("Q{$row}")->getCalculatedValue()),
                    'diciembre' => $this->moneyOnly($sheet->getCell("R{$row}")->getCalculatedValue()),
                    'total' => $this->moneyOnly($sheet->getCell("S{$row}")->getCalculatedValue()),
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

    private function moneyOnly($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['S/', 's/', ' '], '', $value);
        $value = str_replace(',', '', $value);

        return is_numeric($value) ? number_format((float) $value, 2, '.', '') : null;
    }

    private function normalizeString($value): string
    {
        return trim((string) $value);
    }
}
