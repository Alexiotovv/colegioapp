<?php
// app/Http/Controllers/LibretaController.php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Nota;
use App\Models\Apreciacion;
use App\Models\RegistroEvaluacion;
use App\Models\RegistroAsistencia;
use App\Models\RegistroOtraEvaluacion;
use App\Models\ConfiguracionInstitucion;
use App\Models\ConfiguracionLibreta;
use App\Models\AnioAcademico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LibretaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rol = $user->role->nombre ?? $user->rol;
        
        $aulas = Aula::with(['grado.nivel', 'seccion', 'anioAcademico'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $anioActivo = AnioAcademico::where('activo', true)->first();
        
        return view('libretas.index', compact('aulas', 'periodos', 'anioActivo'));
    }
    
    public function getAlumnosByAula(Request $request)
    {
        $aulaId = $request->aula_id;
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        return response()->json($matriculas);
    }
    
    public function exportarAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
            ->find($aulaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        $data = [
            'aula' => $aula,
            'periodos' => $periodos,
            'matriculas' => $matriculas,
            'configInstitucion' => $configInstitucion,
            'configLibreta' => $configLibreta,
            'tipo' => 'aula'
        ];
        
        $pdf = Pdf::loadView('libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('libreta_aula_' . $aula->nombre . '.pdf');
    }
    
    public function exportarAlumno(Request $request)
    {
        $matriculaId = $request->matricula_id;
        $periodoId = $request->periodo_id;
        
        $matricula = Matricula::with(['alumno', 'aula.grado.nivel', 'aula.seccion', 'aula.anioAcademico', 'aula.docente'])
            ->find($matriculaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        $data = [
            'matricula' => $matricula,
            'periodos' => $periodos,
            'configInstitucion' => $configInstitucion,
            'configLibreta' => $configLibreta,
            'tipo' => 'alumno'
        ];
        
        $pdf = Pdf::loadView('libretas.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $nombreAlumno = str_replace(' ', '_', $matricula->alumno->nombre_completo);
        return $pdf->download('libreta_' . $nombreAlumno . '.pdf');
    }
    
    public function previsualizar(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        $matriculaId = $request->matricula_id;
        
        $aula = null;
        $matricula = null;
        
        if ($matriculaId) {
            $matricula = Matricula::with(['alumno', 'aula.grado.nivel', 'aula.seccion', 'aula.anioAcademico', 'aula.docente'])
                ->find($matriculaId);
        } else {
            $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
                ->find($aulaId);
        }
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = null;
        if ($aulaId && !$matriculaId) {
            $matriculas = Matricula::with(['alumno'])
                ->select('matriculas.*')
                ->where('matriculas.aula_id', $aulaId)
                ->where('matriculas.estado', 'activa')
                ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
                ->orderBy('alumnos.apellido_paterno', 'ASC')
                ->orderBy('alumnos.apellido_materno', 'ASC')
                ->orderBy('alumnos.nombres', 'ASC')
                ->get();
        }
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        return view('libretas.previsualizar', compact('aula', 'matricula', 'matriculas', 'periodos', 'configInstitucion', 'configLibreta'));
    }

    public function previsualizarAula(Request $request)
    {
        $aulaId = $request->aula_id;
        $periodoId = $request->periodo_id;
        
        $aula = Aula::with(['grado.nivel', 'seccion', 'anioAcademico', 'docente'])
            ->find($aulaId);
        
        $periodos = Periodo::with('anioAcademico')
            ->orderBy('orden')
            ->get();
        
        $matriculas = Matricula::with(['alumno'])
            ->select('matriculas.*')
            ->where('matriculas.aula_id', $aulaId)
            ->where('matriculas.estado', 'activa')
            ->join('alumnos', 'matriculas.alumno_id', '=', 'alumnos.id')
            ->orderBy('alumnos.apellido_paterno', 'ASC')
            ->orderBy('alumnos.apellido_materno', 'ASC')
            ->orderBy('alumnos.nombres', 'ASC')
            ->get();
        
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        $configLibreta = ConfiguracionLibreta::getConfig();
        
        return view('libretas.previsualizar-aula', compact('aula', 'matriculas', 'periodos', 'configInstitucion', 'configLibreta'));
    }
}