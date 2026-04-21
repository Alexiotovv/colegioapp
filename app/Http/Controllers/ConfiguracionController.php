<?php
// app/Http/Controllers/ConfiguracionController.php

namespace App\Http\Controllers;

use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibreta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        return view('configuracion.index', compact('configInstitucion', 'configLibreta'));
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


    private function deleteOldFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}