<?php
// app/Http/Controllers/ConfiguracionController.php

namespace App\Http\Controllers;

use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibreta;
use App\Models\ConfiguracionAvanceCuadro;
use App\Models\ConfiguracionLibretaCuadro;
use App\Models\Nivel;
use App\Models\CompetenciaTransversal;
use App\Models\User;
use App\Models\AsignacionCompetenciaTransversal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        $niveles = Nivel::orderBy('orden')->get();
        $cuadrosPorNivel = ConfiguracionLibretaCuadro::all()->pluck('cuadros', 'nivel_id')->toArray();
        $avanceCuadrosPorNivel = ConfiguracionAvanceCuadro::all()->pluck('cuadros', 'nivel_id')->toArray();
        $mostrarConclusionAvancePorNivel = [];
        foreach ($niveles as $nivel) {
            $mostrarConclusionAvancePorNivel[$nivel->id] = ConfiguracionAvanceCuadro::isConclusionVisibleForNivel($nivel->id);
        }
        
        // Obtener competencias transversales con asignaciones
        $competenciasTransversales = CompetenciaTransversal::with(['nivel', 'usuariosAsignados'])
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
        
        // Obtener profesores (usuarios con rol diferente a admin)
        $profesores = User::whereHas('role', function($query) {
            $query->where('nombre', '!=', 'admin');
        })->where('activo', true)->orderBy('name')->get();
        
        // Obtener configuraciones de caracteres
        $caracteresConfig = [
            'conclusiones_caracteres_max' => \App\Models\Configuracion::getValor('conclusiones_caracteres_max', 500),
            'competencias_transversales_caracteres_max' => \App\Models\Configuracion::getValor('competencias_transversales_caracteres_max', 500),
            'apreciaciones_caracteres_max' => \App\Models\Configuracion::getValor('apreciaciones_caracteres_max', 500),
        ];
        
        return view('configuracion.index', compact('configInstitucion', 'configLibreta', 'niveles', 'cuadrosPorNivel', 'avanceCuadrosPorNivel', 'mostrarConclusionAvancePorNivel', 'competenciasTransversales', 'profesores', 'caracteresConfig'));
    }
    
    public function updateInstitucion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'ruc' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'web' => 'nullable|url|max:100',
        ]);
        
        $config = ConfiguracionInstitucion::getConfig();
        
        $data = $request->except([
            'logo_login',
            'logo_dashboard',
            'favicon'
        ]);
        
        if ($request->hasFile('logo_login')) {
            $this->deleteOldFile($config->logo_login);
            $data['logo_login'] = $request->file('logo_login')->store('logos', 'public');
        }
        
        if ($request->hasFile('logo_dashboard')) {
            $this->deleteOldFile($config->logo_dashboard);
            $data['logo_dashboard'] = $request->file('logo_dashboard')->store('logos', 'public');
        }
        
        if ($request->hasFile('favicon')) {
            $this->deleteOldFile($config->favicon);
            $data['favicon'] = $request->file('favicon')->store('logos', 'public');
        }
        
        $config->update($data);
        
        return redirect()->route('admin.configuracion.index')
            ->with('success', 'Configuración de la institución actualizada exitosamente');
    }
    
    public function updateLibreta(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:200',
            'subtitulo' => 'nullable|string|max:200',
            'dre' => 'nullable|string|max:100',
            'ugel' => 'nullable|string|max:100',
            'nombre_director' => 'nullable|string|max:200',
            'cargo_director' => 'nullable|string|max:100',
            'nombre_tutor' => 'nullable|string|max:200',
            'cargo_tutor' => 'nullable|string|max:100',
            'texto_pie' => 'nullable|string',
            'nombre_subdirector' => 'nullable|string|max:200',
            'cargo_subdirector' => 'nullable|string|max:100',
        ]);
        
        $config = ConfiguracionLibreta::getConfig();
        
        $data = $request->except([
            'logo_pais',
            'logo_region',
            'logo_institucion',
            'firma_director',
            'firma_tutor',
            'firma_subdirector'
        ]);

        if ($request->hasFile('logo_pais')) {
            $this->deleteOldFile($config->logo_pais);
            $data['logo_pais'] = $request->file('logo_pais')->store('libretas', 'public');
        }

        if ($request->hasFile('logo_region')) {
            $this->deleteOldFile($config->logo_region);
            $data['logo_region'] = $request->file('logo_region')->store('libretas', 'public');
        }

        if ($request->hasFile('logo_institucion')) {
            $this->deleteOldFile($config->logo_institucion);
            $data['logo_institucion'] = $request->file('logo_institucion')->store('libretas', 'public');
        }

        if ($request->hasFile('firma_director')) {
            $this->deleteOldFile($config->firma_director);
            $data['firma_director'] = $request->file('firma_director')->store('firmas', 'public');
        }

        if ($request->hasFile('firma_tutor')) {
            $this->deleteOldFile($config->firma_tutor);
            $data['firma_tutor'] = $request->file('firma_tutor')->store('firmas', 'public');
        }

        if ($request->hasFile('firma_subdirector')) {
            $this->deleteOldFile($config->firma_subdirector);
            $data['firma_subdirector'] = $request->file('firma_subdirector')->store('firmas', 'public');
        }

        $config->update($data);
        
        return redirect()->route('admin.configuracion.index')
            ->with('success', 'Configuración de libreta actualizada exitosamente');
    }
    
    public function deleteLogo(Request $request)
    {
        $campo = $request->campo;
        $config = ConfiguracionInstitucion::getConfig();
        
        if ($config->$campo) {
            $this->deleteOldFile($config->$campo);
            $config->update([$campo => null]);
        }
        
        return response()->json(['success' => true]);
    }
    
    // public function deleteLibretaImage(Request $request)
    // {
    //     $campo = $request->campo;
    //     $config = ConfiguracionLibreta::getConfig();
        
    //     if ($config->$campo) {
    //         $this->deleteOldFile($config->$campo);
    //         $config->update([$campo => null]);
    //     }
        
    //     return response()->json(['success' => true]);
    // }
    public function deleteLibretaImage(Request $request)
    {
        $camposPermitidos = [
            'logo_pais', 
            'logo_region', 
            'logo_institucion', 
            'firma_director', 
            'firma_tutor',
            'firma_subdirector'  // 🔥 Nuevo
        ];
        
        $campo = $request->campo;
        
        if (!in_array($campo, $camposPermitidos)) {
            return response()->json(['success' => false, 'message' => 'Campo no permitido'], 422);
        }
        
        $config = ConfiguracionLibreta::getConfig();
        
        if ($config->$campo) {
            $this->deleteOldFile($config->$campo);
            $config->update([$campo => null]);
        }
        
        return response()->json(['success' => true]);
    }

    public function saveLibretaCuadros(Request $request)
    {
        $data = $request->validate([
            'nivel_id' => 'required|integer|exists:niveles,id',
            'cuadros' => 'nullable|array',
            'cuadros.*' => 'string'
        ]);

        $nivelId = $data['nivel_id'];
        $cuadros = $data['cuadros'] ?? [];

        ConfiguracionLibretaCuadro::setCuadrosForNivel($nivelId, $cuadros);

        return redirect()->route('admin.configuracion.index')->with('success', 'Cuadros de libreta guardados');
    }

    public function saveAvanceCuadros(Request $request)
    {
        $data = $request->validate([
            'nivel_id' => 'required|integer|exists:niveles,id',
            'cuadros' => 'nullable|array',
            'cuadros.*' => 'string',
            'modo_bimestres' => 'nullable|string|in:bimestres_all,bimestres_single',
            'mostrar_nl_alcanzado' => 'nullable|boolean',
            'mostrar_conclusion_descriptiva' => 'nullable|boolean',
        ]);

        $cuadros = $data['cuadros'] ?? [];
        $mostrarConclusion = $request->boolean('mostrar_conclusion_descriptiva', true);
        $mostrarNlAlcanzado = $request->boolean('mostrar_nl_alcanzado', false);
        $modoBimestres = $data['modo_bimestres'] ?? 'bimestres_all';

        // Eliminar marcadores previos
        $cuadros = array_values(array_filter($cuadros, function ($item) {
            return $item !== '_modo_bimestres_single' && $item !== '_modo_bimestres_all' && $item !== '_mostrar_nl_alcanzado';
        }));

        // Agregar marcador del modo bimestres actual (siempre)
        $cuadros[] = ($modoBimestres === 'bimestres_single') ? '_modo_bimestres_single' : '_modo_bimestres_all';

        // Agregar marcador de mostrar NL alcanzado si está habilitado
        if ($mostrarNlAlcanzado) {
            $cuadros[] = '_mostrar_nl_alcanzado';
        }

        if ($mostrarConclusion) {
            if (!in_array(ConfiguracionAvanceCuadro::CONCLUSION_VISIBLE_KEY, $cuadros, true)) {
                $cuadros[] = ConfiguracionAvanceCuadro::CONCLUSION_VISIBLE_KEY;
            }
        } else {
            $cuadros = array_values(array_filter($cuadros, function ($item) {
                return $item !== ConfiguracionAvanceCuadro::CONCLUSION_VISIBLE_KEY;
            }));
        }

        ConfiguracionAvanceCuadro::setCuadrosForNivel(
            $data['nivel_id'],
            array_values($cuadros)
        );

        return redirect()->route('admin.configuracion.index')->with('success', 'Avance cuadros guardados');
    }


    public function updateCaracteres(Request $request)
    {
        $request->validate([
            'conclusiones_caracteres_max' => 'required|integer|min:100|max:5000',
            'competencias_transversales_caracteres_max' => 'required|integer|min:100|max:5000',
            'apreciaciones_caracteres_max' => 'required|integer|min:100|max:5000',
        ]);

        // Usar el modelo Configuracion para actualizar
        \App\Models\Configuracion::setValor(
            'conclusiones_caracteres_max',
            $request->conclusiones_caracteres_max,
            'Cantidad máxima de caracteres en conclusiones descriptivas',
            'numero'
        );

        \App\Models\Configuracion::setValor(
            'competencias_transversales_caracteres_max',
            $request->competencias_transversales_caracteres_max,
            'Cantidad máxima de caracteres en competencias transversales',
            'numero'
        );

        \App\Models\Configuracion::setValor(
            'apreciaciones_caracteres_max',
            $request->apreciaciones_caracteres_max,
            'Cantidad máxima de caracteres en apreciaciones',
            'numero'
        );

        return redirect()->route('admin.configuracion.index')
            ->with('success', 'Límites de caracteres actualizados exitosamente');
    }
    
    public function asignarCompetenciasTransversales(Request $request)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias_transversales,id',
            'profesores' => 'array',
            'profesores.*' => 'exists:users,id',
        ]);
        
        $competenciaId = $request->competencia_id;
        $profesoresSeleccionados = $request->profesores ?? [];
        
        // Eliminar asignaciones existentes para esta competencia
        AsignacionCompetenciaTransversal::where('competencia_transversal_id', $competenciaId)->delete();
        
        // Crear nuevas asignaciones
        foreach ($profesoresSeleccionados as $userId) {
            AsignacionCompetenciaTransversal::create([
                'competencia_transversal_id' => $competenciaId,
                'user_id' => $userId,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Asignaciones actualizadas correctamente'
        ]);
    }

    public function eliminarAsignacionCompetenciaTransversal(Request $request)
    {
        $request->validate([
            'competencia_id' => 'required|exists:competencias_transversales,id',
            'user_id' => 'required|exists:users,id',
        ]);

        AsignacionCompetenciaTransversal::where('competencia_transversal_id', $request->competencia_id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profesor eliminado de la asignación correctamente'
        ]);
    }

    private function deleteOldFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}