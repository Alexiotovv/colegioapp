<?php
// app/Http/Controllers/CuadroDinamicoController.php

namespace App\Http\Controllers;

use App\Models\CuadroDinamico;
use App\Models\DescripcionCuadroDinamico;
use App\Models\Nivel;
use Illuminate\Http\Request;

class CuadroDinamicoController extends Controller
{
    public function index()
    {
        $niveles = Nivel::orderBy('orden')->get();
        $nivelId = request()->query('nivel_id');
        $query = CuadroDinamico::with('descripciones')->orderBy('orden');
        if ($nivelId) {
            $query->where(function($q) use ($nivelId) {
                $q->whereNull('nivel_id')->orWhere('nivel_id', $nivelId);
            });
        }
        $cuadros = $query->get();
        // debug logging to help trace missing list issue
        try {
            \Log::info('CuadroDinamico index: count=' . $cuadros->count() . ', nivelId=' . ($nivelId ?? 'null'));
            \Log::info('DB table exists cuadros_dinamicos: ' . (\Schema::hasTable('cuadros_dinamicos') ? 'yes' : 'no'));
        } catch (\Throwable $e) {
            // swallow logging errors to avoid breaking page
        }
        return view('cuadros_dinamicos.index', compact('cuadros', 'niveles', 'nivelId'));
    }

    public function edit(CuadroDinamico $cuadro)
    {
        $niveles = Nivel::orderBy('orden')->get();
        return view('cuadros_dinamicos.edit', ['niveles' => $niveles, 'cuadro' => $cuadro]);
    }

    public function update(Request $request, CuadroDinamico $cuadro)
    {
        // normalize checkbox inputs so validation accepts them (true => 1, false => 0)
        $request->merge([
            'involucra_libreta' => $request->has('involucra_libreta') ? 1 : 0,
            'mostrar_en_libreta' => $request->has('mostrar_en_libreta') ? 1 : 0,
            'activo' => $request->has('activo') ? 1 : 0,
        ]);

        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'nivel_id' => 'nullable|exists:niveles,id',
            'tipo' => 'nullable|string',
            'columnas_count' => 'nullable|integer|min:1',
            'filas_count' => 'nullable|integer|min:0',
            'encabezados_text' => 'nullable|string',
            'mostrar_en_nivel_seleccionado' => 'nullable|boolean',
            'nota_tipo' => 'nullable|string',
            'involucra_libreta' => 'nullable|boolean',
            'ancho' => 'nullable|string',
            'mostrar_en_libreta' => 'nullable|boolean',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
            // celdas will be validated and saved as opciones for tabla_generica
        ]);

        $data['involucra_libreta'] = $request->has('involucra_libreta');
        $data['mostrar_en_libreta'] = $request->has('mostrar_en_libreta');
        $data['activo'] = $request->has('activo');

        $cuadro->update($data);

        // save opciones for tabla_generica
        if ($request->input('tipo') === 'tabla_generica') {
            $op = [
                'columnas' => (int) $request->input('columnas_count', 3),
                'filas' => (int) $request->input('filas_count', 4),
                'mostrar_encabezados' => (int) $request->input('mostrar_encabezados', 0),
                'encabezados' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $request->input('encabezados_text', ''))), function($v){ return $v !== ''; })),
                'mostrar_en_nivel_seleccionado' => $request->boolean('mostrar_en_nivel_seleccionado'),
                'celdas' => array_values(array_filter($request->input('celdas', []), function($row){ return is_array($row); }))
            ];
            // normalize celdas to ensure consistent indexing
            $normalized = [];
            for ($r = 0; $r < $op['filas']; $r++) {
                $row = isset($op['celdas'][$r]) && is_array($op['celdas'][$r]) ? $op['celdas'][$r] : [];
                $normalizedRow = [];
                for ($c = 0; $c < $op['columnas']; $c++) {
                    $normalizedRow[] = isset($row[$c]) ? (string) $row[$c] : '';
                }
                $normalized[] = $normalizedRow;
            }
            $op['celdas'] = $normalized;
            $cuadro->opciones = $op;
            $cuadro->save();
        }

        // notas: we no longer use DescripcionCuadroDinamico here; cell values are saved as opciones for tabla_generica below

        return redirect()->route('admin.cuadros-dinamicos.index')->with('success', 'Cuadro actualizado');
    }

    public function create()
    {
        $niveles = Nivel::orderBy('orden')->get();
        return view('cuadros_dinamicos.create', compact('niveles'));
    }

    public function store(Request $request)
    {
        // normalize checkbox inputs so validation accepts them (true => 1, false => 0)
        $request->merge([
            'involucra_libreta' => $request->has('involucra_libreta') ? 1 : 0,
            'mostrar_en_libreta' => $request->has('mostrar_en_libreta') ? 1 : 0,
            'activo' => $request->has('activo') ? 1 : 0,
        ]);

        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'nivel_id' => 'nullable|exists:niveles,id',
            'tipo' => 'nullable|string',
            'columnas_count' => 'nullable|integer|min:1',
            'filas_count' => 'nullable|integer|min:0',
            'encabezados_text' => 'nullable|string',
            'mostrar_en_nivel_seleccionado' => 'nullable|boolean',
            'nota_tipo' => 'nullable|string',
            'involucra_libreta' => 'nullable|boolean',
            'ancho' => 'nullable|string',
            'mostrar_en_libreta' => 'nullable|boolean',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data['involucra_libreta'] = $request->has('involucra_libreta');
        $data['mostrar_en_libreta'] = $request->has('mostrar_en_libreta');
        $data['activo'] = $request->has('activo');

        $cuadro = CuadroDinamico::create($data);

        // store opciones for tabla_generica
        if ($request->input('tipo') === 'tabla_generica') {
            $op = [
                'columnas' => (int) $request->input('columnas_count', 3),
                'filas' => (int) $request->input('filas_count', 4),
                'mostrar_encabezados' => (int) $request->input('mostrar_encabezados', 0),
                'encabezados' => array_values(array_filter(preg_split('/\r?\n/', $request->input('encabezados_text', '')), function($v){ return trim($v) !== ''; })),
                'mostrar_en_nivel_seleccionado' => $request->boolean('mostrar_en_nivel_seleccionado'),
                // collect celdas: expect array of rows with columns
                'celdas' => array_values(array_filter($request->input('celdas', []), function($row){ return is_array($row); }))
            ];
            // normalize celdas to ensure consistent indexing
            $normalized = [];
            for ($r = 0; $r < $op['filas']; $r++) {
                $row = isset($op['celdas'][$r]) && is_array($op['celdas'][$r]) ? $op['celdas'][$r] : [];
                $normalizedRow = [];
                for ($c = 0; $c < $op['columnas']; $c++) {
                    $normalizedRow[] = isset($row[$c]) ? (string) $row[$c] : '';
                }
                $normalized[] = $normalizedRow;
            }
            $op['celdas'] = $normalized;
            $cuadro->opciones = $op;
            $cuadro->save();
        }

        // no longer saving DescripcionCuadroDinamico here; cell values are persisted inside opciones

        return redirect()->route('admin.cuadros-dinamicos.index')->with('success', 'Cuadro dinámico creado');
    }

    public function destroy(CuadroDinamico $cuadro)
    {
        // delete related descriptions first
        $cuadro->descripciones()->delete();
        $cuadro->delete();
        return redirect()->route('admin.cuadros-dinamicos.index')->with('success', 'Cuadro eliminado');
    }
}
