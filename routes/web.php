<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnioAcademicoController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoApiController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\ApoderadoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CapacidadController;
use App\Http\Controllers\CursoJerarquicoController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\ConfiguracionAcademicaController;
use App\Http\Controllers\CargaHorariaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ApreciacionController;
use App\Http\Controllers\RegistroEvaluacionController;
use App\Http\Controllers\EvaluacionJerarquicoController;
use App\Http\Controllers\TipoInasistenciaJerarquicoController;
use App\Http\Controllers\RegistroAsistenciaController;
use App\Http\Controllers\TipoOtraEvaluacionJerarquicoController;
use App\Http\Controllers\RegistroOtraEvaluacionController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\LibretaController;

// Rutas públicas (sin autenticación)
Route::middleware(['guest'])->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rutas solo para administradores
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        
        // Niveles
        Route::resource('niveles', NivelController::class);
        Route::patch('/niveles/{nivele}/toggle-active', [NivelController::class, 'toggleActive'])->name('niveles.toggle-active');
        
        // Grados
        Route::resource('grados', GradoController::class);
        Route::patch('/grados/{grado}/toggle-active', [GradoController::class, 'toggleActive'])->name('grados.toggle-active');
        
        // Secciones
        Route::resource('secciones', SeccionController::class);
        Route::patch('/secciones/{seccione}/toggle-active', [SeccionController::class, 'toggleActive'])->name('secciones.toggle-active');
        
        // Años Académicos
        Route::resource('anios', AnioAcademicoController::class);
        Route::patch('/anios/{anio}/set-activo', [AnioAcademicoController::class, 'setActivo'])->name('anios.set-activo');
        
        // Periodos
        Route::resource('periodos', PeriodoController::class);
        Route::patch('/periodos/{periodo}/toggle-active', [PeriodoController::class, 'toggleActive'])->name('periodos.toggle-active'); 

        // Alumnos
        Route::resource('alumnos', AlumnoController::class);
        Route::patch('/alumnos/{alumno}/change-status/{estado}', [AlumnoController::class, 'changeStatus'])->name('alumnos.change-status');
        
        // Matrículas
        Route::resource('matriculas', MatriculaController::class);
        Route::get('/matriculas/aulas-by-filters', [MatriculaController::class, 'getAulasByFilters'])->name('matriculas.aulas-by-filters');



        // Rutas de verificación (deben ir antes de Route::resource)
        Route::get('/apoderados/verificar-dni', [ApoderadoController::class, 'verificarDni'])->name('apoderados.verificar-dni');
        Route::get('/apoderados/verificar-email', [ApoderadoController::class, 'verificarEmail'])->name('apoderados.verificar-email');
        
        // Apoderados
        Route::resource('apoderados', ApoderadoController::class);
        Route::patch('/apoderados/{apoderado}/toggle-notifications', [ApoderadoController::class, 'toggleNotifications'])->name('apoderados.toggle-notifications');

         // Cursos
        Route::resource('cursos', CursoController::class);
        Route::patch('/cursos/{curso}/toggle-active', [CursoController::class, 'toggleActive'])->name('cursos.toggle-active');
        Route::get('/cursos-por-grado', [CursoController::class, 'getCursosByGrado'])->name('cursos.by-grado');
        
        // Competencias
        Route::resource('competencias', CompetenciaController::class);
        Route::patch('/competencias/{competencia}/toggle-active', [CompetenciaController::class, 'toggleActive'])->name('competencias.toggle-active');
        Route::get('/competencias-por-curso', [CompetenciaController::class, 'getCompetenciasByCurso'])->name('competencias.by-curso');
        
        // Capacidades
        Route::resource('capacidades', CapacidadController::class);
        Route::patch('/capacidades/{capacidad}/toggle-active', [CapacidadController::class, 'toggleActive'])->name('capacidades.toggle-active');
        Route::get('/capacidades-por-competencia', [CapacidadController::class, 'getCapacidadesByCompetencia'])->name('capacidades.by-competencia');

        // Competencias - JSON
        Route::get('/competencias/get-json/{competencia}', [CompetenciaController::class, 'getJson'])->name('competencias.get-json');

        // Cursos Jerárquicos
        Route::get('/cursos-jerarquico', [CursoJerarquicoController::class, 'index'])->name('cursos-jerarquico.index');
        Route::post('/cursos-jerarquico/change-year', [CursoJerarquicoController::class, 'changeYear'])->name('cursos-jerarquico.change-year');
        Route::post('/cursos-jerarquico/curso', [CursoJerarquicoController::class, 'storeCurso'])->name('cursos-jerarquico.store-curso');
        Route::post('/cursos-jerarquico/competencia', [CursoJerarquicoController::class, 'storeCompetencia'])->name('cursos-jerarquico.store-competencia');
        Route::post('/cursos-jerarquico/capacidad', [CursoJerarquicoController::class, 'storeCapacidad'])->name('cursos-jerarquico.store-capacidad');
        Route::put('/cursos/{curso}', [CursoJerarquicoController::class, 'updateCurso'])->name('cursos-jerarquico.update-curso');
        Route::put('/competencias/{competencia}', [CursoJerarquicoController::class, 'updateCompetencia'])->name('cursos-jerarquico.update-competencia');
        Route::put('/capacidades/{capacidad}', [CursoJerarquicoController::class, 'updateCapacidad'])->name('cursos-jerarquico.update-capacidad');
        Route::delete('/cursos-jerarquico/curso/{curso}', [CursoJerarquicoController::class, 'destroyCurso'])->name('cursos-jerarquico.destroy-curso');
        Route::delete('/cursos-jerarquico/competencia/{competencia}', [CursoJerarquicoController::class, 'destroyCompetencia'])->name('cursos-jerarquico.destroy-competencia');
        Route::delete('/cursos-jerarquico/capacidad/{capacidad}', [CursoJerarquicoController::class, 'destroyCapacidad'])->name('cursos-jerarquico.destroy-capacidad');

        Route::get('/cursos-jerarquico/curso/{curso}', [CursoJerarquicoController::class, 'getCurso'])->name('cursos-jerarquico.get-curso');
        Route::get('/cursos-jerarquico/competencia/{competencia}', [CursoJerarquicoController::class, 'getCompetencia'])->name('cursos-jerarquico.get-competencia');
        Route::get('/cursos-jerarquico/capacidad/{capacidad}', [CursoJerarquicoController::class, 'getCapacidad'])->name('cursos-jerarquico.get-capacidad');



        // Aulas
        Route::resource('aulas', AulaController::class);
        Route::patch('/aulas/{aula}/toggle-active', [AulaController::class, 'toggleActive'])->name('aulas.toggle-active');

        
        
        
        //Configuración Académica
        Route::get('/configuracion-academica', [ConfiguracionAcademicaController::class, 'index'])->name('configuracion-academica.index');
    
        // Rutas para Niveles
        Route::get('/configuracion/niveles', [ConfiguracionAcademicaController::class, 'getNiveles'])->name('configuracion.niveles');
        Route::get('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'getNivel'])->name('configuracion.niveles.show');
        Route::post('/configuracion/niveles', [ConfiguracionAcademicaController::class, 'storeNivel'])->name('configuracion.niveles.store');
        Route::put('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'updateNivel'])->name('configuracion.niveles.update');
        Route::delete('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'deleteNivel'])->name('configuracion.niveles.delete');
        Route::patch('/configuracion/niveles/{nivel}/toggle', [ConfiguracionAcademicaController::class, 'toggleNivel'])->name('configuracion.niveles.toggle');
        
        // Rutas para Grados
        Route::get('/configuracion/grados', [ConfiguracionAcademicaController::class, 'getGrados'])->name('configuracion.grados');
        Route::get('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'getGrado'])->name('configuracion.grados.show');
        Route::get('/configuracion/grados-por-nivel/{nivelId}', [ConfiguracionAcademicaController::class, 'getGradosByNivel'])->name('configuracion.grados.by-nivel');
        Route::post('/configuracion/grados', [ConfiguracionAcademicaController::class, 'storeGrado'])->name('configuracion.grados.store');
        Route::put('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'updateGrado'])->name('configuracion.grados.update');
        Route::delete('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'deleteGrado'])->name('configuracion.grados.delete');
        Route::patch('/configuracion/grados/{grado}/toggle', [ConfiguracionAcademicaController::class, 'toggleGrado'])->name('configuracion.grados.toggle');
        
        // Rutas para Secciones
        Route::get('/configuracion/secciones', [ConfiguracionAcademicaController::class, 'getSecciones'])->name('configuracion.secciones');
        Route::get('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'getSeccion'])->name('configuracion.secciones.show');
        Route::post('/configuracion/secciones', [ConfiguracionAcademicaController::class, 'storeSeccion'])->name('configuracion.secciones.store');
        Route::put('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'updateSeccion'])->name('configuracion.secciones.update');
        Route::delete('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'deleteSeccion'])->name('configuracion.secciones.delete');
        Route::patch('/configuracion/secciones/{seccion}/toggle', [ConfiguracionAcademicaController::class, 'toggleSeccion'])->name('configuracion.secciones.toggle');

        // Carga Horaria
        Route::resource('carga-horaria', CargaHorariaController::class);
        Route::patch('/carga-horaria/{cargaHorarium}/toggle', [CargaHorariaController::class, 'toggleActive'])->name('carga-horaria.toggle');
        
        
        
    });

    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Notas - accesible para admin y docente
        Route::middleware(['role:admin,docente'])->group(function () {
            Route::get('/notas', [NotaController::class, 'index'])->name('notas.index');
            Route::get('/notas/cursos-by-aula', [NotaController::class, 'getCursosByAula'])->name('notas.cursos-by-aula');
            Route::get('/notas/get-data', [NotaController::class, 'getDataForNotas'])->name('notas.get-data');
            Route::post('/notas/save', [NotaController::class, 'saveNotas'])->name('notas.save');
            
            //Rutas Conclusiones descriptivas
            Route::get('/notas/conclusion/{nota}', [NotaController::class, 'getConclusion'])->name('notas.get-conclusion');
            Route::post('/notas/conclusion', [NotaController::class, 'saveConclusion'])->name('notas.save-conclusion');           
        });
        
        // Solo admin puede habilitar/deshabilitar periodos
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/notas/toggle-habilitacion', [NotaController::class, 'toggleHabilitacion'])->name('notas.toggle-habilitacion');
        });
    });



    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Apreciaciones - accesible para admin y docente
        Route::middleware(['role:admin,tutor'])->group(function () {
            Route::get('/apreciaciones', [ApreciacionController::class, 'index'])->name('apreciaciones.index');
            Route::get('/apreciaciones/get-data', [ApreciacionController::class, 'getDataForApreciaciones'])->name('apreciaciones.get-data');
            Route::post('/apreciaciones/save', [ApreciacionController::class, 'saveApreciaciones'])->name('apreciaciones.save');
        });
        
        // Solo admin puede habilitar/deshabilitar periodos
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/apreciaciones/toggle-habilitacion', [ApreciacionController::class, 'toggleHabilitacion'])->name('apreciaciones.toggle-habilitacion');
        });

          // Evaluaciones Jerárquicas
        Route::get('/evaluaciones-jerarquico', [EvaluacionJerarquicoController::class, 'index'])->name('evaluaciones-jerarquico.index');
        Route::post('/evaluaciones-jerarquico/evaluacion', [EvaluacionJerarquicoController::class, 'storeEvaluacion'])->name('evaluaciones-jerarquico.store');
        Route::put('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'updateEvaluacion'])->name('evaluaciones-jerarquico.update');
        Route::delete('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'destroyEvaluacion'])->name('evaluaciones-jerarquico.destroy');
        Route::patch('/evaluaciones-jerarquico/evaluacion/{evaluacion}/toggle', [EvaluacionJerarquicoController::class, 'toggleActive'])->name('evaluaciones-jerarquico.toggle');
        Route::get('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'getEvaluacion'])->name('evaluaciones-jerarquico.get');

        // Tipos de Inasistencia Jerárquicos
        Route::get('/tipos-inasistencia-jerarquico', [TipoInasistenciaJerarquicoController::class, 'index'])->name('tipos-inasistencia-jerarquico.index');
        Route::post('/tipos-inasistencia-jerarquico/tipo', [TipoInasistenciaJerarquicoController::class, 'storeTipo'])->name('tipos-inasistencia-jerarquico.store');
        Route::put('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'updateTipo'])->name('tipos-inasistencia-jerarquico.update');
        Route::delete('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'destroyTipo'])->name('tipos-inasistencia-jerarquico.destroy');
        Route::patch('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}/toggle', [TipoInasistenciaJerarquicoController::class, 'toggleActive'])->name('tipos-inasistencia-jerarquico.toggle');
        Route::get('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'getTipo'])->name('tipos-inasistencia-jerarquico.get');

        // Tipos de Otras Evaluaciones Jerárquicos
        Route::get('/tipos-otras-evaluaciones-jerarquico', [TipoOtraEvaluacionJerarquicoController::class, 'index'])->name('tipos-otras-evaluaciones-jerarquico.index');
        Route::post('/tipos-otras-evaluaciones-jerarquico/tipo', [TipoOtraEvaluacionJerarquicoController::class, 'storeTipo'])->name('tipos-otras-evaluaciones-jerarquico.store');
        Route::put('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'updateTipo'])->name('tipos-otras-evaluaciones-jerarquico.update');
        Route::delete('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'destroyTipo'])->name('tipos-otras-evaluaciones-jerarquico.destroy');
        Route::patch('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}/toggle', [TipoOtraEvaluacionJerarquicoController::class, 'toggleActive'])->name('tipos-otras-evaluaciones-jerarquico.toggle');
        Route::get('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'getTipo'])->name('tipos-otras-evaluaciones-jerarquico.get');

        // Configuración del Sistema
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('/configuracion/institucion', [ConfiguracionController::class, 'updateInstitucion'])->name('configuracion.update-institucion');
        Route::put('/configuracion/libreta', [ConfiguracionController::class, 'updateLibreta'])->name('configuracion.update-libreta');
        Route::post('/configuracion/delete-logo', [ConfiguracionController::class, 'deleteLogo'])->name('configuracion.delete-logo');
        Route::post('/configuracion/delete-libreta-image', [ConfiguracionController::class, 'deleteLibretaImage'])->name('configuracion.delete-libreta-image');

        // Libretas (Exportar)
        Route::get('/libretas', [LibretaController::class, 'index'])->name('libretas.index');
        Route::get('/libretas/alumnos-by-aula', [LibretaController::class, 'getAlumnosByAula'])->name('libretas.alumnos-by-aula');
        Route::post('/libretas/exportar-aula', [LibretaController::class, 'exportarAula'])->name('libretas.exportar-aula');
        Route::post('/libretas/exportar-alumno', [LibretaController::class, 'exportarAlumno'])->name('libretas.exportar-alumno');
        Route::get('/libretas/previsualizar', [LibretaController::class, 'previsualizar'])->name('libretas.previsualizar');

    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/registro-evaluaciones/toggle-habilitacion', [RegistroEvaluacionController::class, 'toggleHabilitacion'])->name('registro-evaluaciones.toggle-habilitacion');
    });

    // Registro de Evaluaciones
    Route::middleware(['role:admin,tutor,auxiliar'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/registro-evaluaciones', [RegistroEvaluacionController::class, 'index'])->name('registro-evaluaciones.index');
        Route::get('/registro-evaluaciones/get-data', [RegistroEvaluacionController::class, 'getDataForRegistro'])->name('registro-evaluaciones.get-data');
        Route::post('/registro-evaluaciones/save', [RegistroEvaluacionController::class, 'saveRegistros'])->name('registro-evaluaciones.save');
    });

    

    // Registro de Asistencias
    Route::middleware(['role:admin,tutor,auxiliar'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/registro-asistencias', [RegistroAsistenciaController::class, 'index'])->name('registro-asistencias.index');
        Route::get('/registro-asistencias/get-data', [RegistroAsistenciaController::class, 'getDataForRegistro'])->name('registro-asistencias.get-data');
        Route::post('/registro-asistencias/save', [RegistroAsistenciaController::class, 'saveRegistros'])->name('registro-asistencias.save');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/registro-asistencias/toggle-habilitacion', [RegistroAsistenciaController::class, 'toggleHabilitacion'])->name('registro-asistencias.toggle-habilitacion');
    });
    // /////////////  End Tipos de Inasistencia Jerárquicos


    

    // Registro de Otras Evaluaciones
    Route::middleware(['role:admin,tutor'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/registro-otras-evaluaciones', [RegistroOtraEvaluacionController::class, 'index'])->name('registro-otras-evaluaciones.index');
        Route::get('/registro-otras-evaluaciones/get-data', [RegistroOtraEvaluacionController::class, 'getDataForRegistro'])->name('registro-otras-evaluaciones.get-data');
        Route::post('/registro-otras-evaluaciones/save', [RegistroOtraEvaluacionController::class, 'saveRegistros'])->name('registro-otras-evaluaciones.save');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/registro-otras-evaluaciones/toggle-habilitacion', [RegistroOtraEvaluacionController::class, 'toggleHabilitacion'])->name('registro-otras-evaluaciones.toggle-habilitacion');
    });

    //End Registro de Otras Evaluaciones





    //Se puso afuera porque requiere autenticación pero no es exclusivo de admin.
    Route::get('/carga-horaria/cursos-by-docente', [CargaHorariaController::class, 'getCursosByDocente'])->name('admin.carga-horaria.cursos-by-docente');
    Route::get('/carga-horaria/aulas-by-curso', [CargaHorariaController::class, 'getAulasByCurso'])->name('admin.carga-horaria.aulas-by-curso');
    //Se puso afuera porque requiere autenticación pero no es exclusivo de admin.
    Route::get('/aulas/grados-by-nivel', [AulaController::class, 'getGradosByNivel'])->name('admin.aulas.grados-by-nivel');

    // API Routes (sin middleware auth para peticiones AJAX)
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/alumnos/store', [App\Http\Controllers\Api\AlumnoApiController::class, 'store'])->name('alumnos.store');
        Route::get('/alumnos/search', [App\Http\Controllers\Api\AlumnoApiController::class, 'search'])->name('alumnos.search');
    });

    // Rutas para docentes
    Route::middleware(['role:docente'])->prefix('docente')->name('docente.')->group(function () {
        // Route::get('/mis-cursos', [DocenteController::class, 'misCursos'])->name('cursos');
        // Route::get('/notas', [NotaController::class, 'index'])->name('notas');
    });
    
    // Rutas para apoderados
    Route::middleware(['role:apoderado'])->prefix('apoderado')->name('apoderado.')->group(function () {
        // Route::get('/mis-hijos', [ApoderadoController::class, 'misHijos'])->name('hijos');
        // Route::get('/notas', [ApoderadoController::class, 'notas'])->name('notas');
    });






});