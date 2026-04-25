<?php



//nueva Versión de Código
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
use App\Http\Controllers\CompetenciaTransversalJerarquicoController;
use App\Http\Controllers\RegistroCompetenciaTransversalController;
use App\Http\Controllers\ConfiguracionNotasController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\AvanceNotasController;

// Rutas públicas (sin autenticación)
Route::middleware(['guest'])->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ==================== MÓDULOS DE ADMINISTRACIÓN (solo visible si tiene el módulo) ====================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Módulo: users
        Route::middleware(['modulo:users'])->group(function () {
            Route::resource('users', UserController::class);
            Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        });
        
        // Módulo: niveles (si existe)
        Route::middleware(['modulo:niveles'])->group(function () {
            Route::resource('niveles', NivelController::class);
            Route::patch('/niveles/{nivele}/toggle-active', [NivelController::class, 'toggleActive'])->name('niveles.toggle-active');
        });
        
        // Módulo: grados
        Route::middleware(['modulo:grados'])->group(function () {
            Route::resource('grados', GradoController::class);
            Route::patch('/grados/{grado}/toggle-active', [GradoController::class, 'toggleActive'])->name('grados.toggle-active');
        });
        
        // Módulo: secciones
        Route::middleware(['modulo:secciones'])->group(function () {
            Route::resource('secciones', SeccionController::class);
            Route::patch('/secciones/{seccione}/toggle-active', [SeccionController::class, 'toggleActive'])->name('secciones.toggle-active');
        });
        
        // Módulo: anios
        Route::middleware(['modulo:anios'])->group(function () {
            Route::resource('anios', AnioAcademicoController::class);
            Route::patch('/anios/{anio}/set-activo', [AnioAcademicoController::class, 'setActivo'])->name('anios.set-activo');
        });
        
        // Módulo: periodos
        Route::middleware(['modulo:periodos'])->group(function () {
            Route::resource('periodos', PeriodoController::class);
            Route::patch('/periodos/{periodo}/toggle-active', [PeriodoController::class, 'toggleActive'])->name('periodos.toggle-active');
        });
        
        // Módulo: alumnos
        Route::middleware(['modulo:alumnos'])->group(function () {
            Route::resource('alumnos', AlumnoController::class);
            Route::patch('/alumnos/{alumno}/change-status/{estado}', [AlumnoController::class, 'changeStatus'])->name('alumnos.change-status');
        });
        
        // Módulo: matriculas
        Route::middleware(['modulo:matriculas'])->group(function () {
            Route::resource('matriculas', MatriculaController::class);
            Route::get('/matriculas/aulas-by-filters', [MatriculaController::class, 'getAulasByFilters'])->name('matriculas.aulas-by-filters');
        });
        
        // Módulo: apoderados
        Route::middleware(['modulo:apoderados'])->group(function () {
            Route::get('/apoderados/verificar-dni', [ApoderadoController::class, 'verificarDni'])->name('apoderados.verificar-dni');
            Route::get('/apoderados/verificar-email', [ApoderadoController::class, 'verificarEmail'])->name('apoderados.verificar-email');
            Route::resource('apoderados', ApoderadoController::class);
            Route::patch('/apoderados/{apoderado}/toggle-notifications', [ApoderadoController::class, 'toggleNotifications'])->name('apoderados.toggle-notifications');
        });
        
        // Módulo: cursos
        Route::middleware(['modulo:cursos'])->group(function () {
            Route::resource('cursos', CursoController::class);
            Route::patch('/cursos/{curso}/toggle-active', [CursoController::class, 'toggleActive'])->name('cursos.toggle-active');
            Route::get('/cursos-por-grado', [CursoController::class, 'getCursosByGrado'])->name('cursos.by-grado');
        });
        
        // Módulo: competencias
        Route::middleware(['modulo:competencias'])->group(function () {
            Route::resource('competencias', CompetenciaController::class);
            Route::patch('/competencias/{competencia}/toggle-active', [CompetenciaController::class, 'toggleActive'])->name('competencias.toggle-active');
            Route::get('/competencias-por-curso', [CompetenciaController::class, 'getCompetenciasByCurso'])->name('competencias.by-curso');
            Route::get('/competencias/get-json/{competencia}', [CompetenciaController::class, 'getJson'])->name('competencias.get-json');
        });
        
        // Módulo: capacidades
        Route::middleware(['modulo:capacidades'])->group(function () {
            Route::resource('capacidades', CapacidadController::class);
            Route::patch('/capacidades/{capacidad}/toggle-active', [CapacidadController::class, 'toggleActive'])->name('capacidades.toggle-active');
            Route::get('/capacidades-por-competencia', [CapacidadController::class, 'getCapacidadesByCompetencia'])->name('capacidades.by-competencia');
        });
        
        // Módulo: cursos-jerarquico (configuración de cursos/competencias/capacidades)
        Route::middleware(['modulo:cursos-jerarquico'])->group(function () {
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
        });
        
        // Módulo: aulas
        Route::middleware(['modulo:aulas'])->group(function () {
            Route::resource('aulas', AulaController::class);
            Route::patch('/aulas/{aula}/toggle-active', [AulaController::class, 'toggleActive'])->name('aulas.toggle-active');
        });
        
        // Módulo: configuracion-academica
        Route::middleware(['modulo:configuracion-academica'])->group(function () {
            Route::get('/configuracion-academica', [ConfiguracionAcademicaController::class, 'index'])->name('configuracion-academica.index');
            
            // Niveles
            Route::get('/configuracion/niveles', [ConfiguracionAcademicaController::class, 'getNiveles'])->name('configuracion.niveles');
            Route::get('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'getNivel'])->name('configuracion.niveles.show');
            Route::post('/configuracion/niveles', [ConfiguracionAcademicaController::class, 'storeNivel'])->name('configuracion.niveles.store');
            Route::put('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'updateNivel'])->name('configuracion.niveles.update');
            Route::delete('/configuracion/niveles/{nivel}', [ConfiguracionAcademicaController::class, 'deleteNivel'])->name('configuracion.niveles.delete');
            Route::patch('/configuracion/niveles/{nivel}/toggle', [ConfiguracionAcademicaController::class, 'toggleNivel'])->name('configuracion.niveles.toggle');
            
            // Grados
            Route::get('/configuracion/grados', [ConfiguracionAcademicaController::class, 'getGrados'])->name('configuracion.grados');
            Route::get('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'getGrado'])->name('configuracion.grados.show');
            Route::get('/configuracion/grados-por-nivel/{nivelId}', [ConfiguracionAcademicaController::class, 'getGradosByNivel'])->name('configuracion.grados.by-nivel');
            Route::post('/configuracion/grados', [ConfiguracionAcademicaController::class, 'storeGrado'])->name('configuracion.grados.store');
            Route::put('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'updateGrado'])->name('configuracion.grados.update');
            Route::delete('/configuracion/grados/{grado}', [ConfiguracionAcademicaController::class, 'deleteGrado'])->name('configuracion.grados.delete');
            Route::patch('/configuracion/grados/{grado}/toggle', [ConfiguracionAcademicaController::class, 'toggleGrado'])->name('configuracion.grados.toggle');
            
            // Secciones
            Route::get('/configuracion/secciones', [ConfiguracionAcademicaController::class, 'getSecciones'])->name('configuracion.secciones');
            Route::get('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'getSeccion'])->name('configuracion.secciones.show');
            Route::post('/configuracion/secciones', [ConfiguracionAcademicaController::class, 'storeSeccion'])->name('configuracion.secciones.store');
            Route::put('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'updateSeccion'])->name('configuracion.secciones.update');
            Route::delete('/configuracion/secciones/{seccion}', [ConfiguracionAcademicaController::class, 'deleteSeccion'])->name('configuracion.secciones.delete');
            Route::patch('/configuracion/secciones/{seccion}/toggle', [ConfiguracionAcademicaController::class, 'toggleSeccion'])->name('configuracion.secciones.toggle');
        });
        
        // Módulo: carga-horaria
        Route::middleware(['modulo:carga-horaria'])->group(function () {
            Route::resource('carga-horaria', CargaHorariaController::class);
            Route::patch('/carga-horaria/{cargaHorarium}/toggle', [CargaHorariaController::class, 'toggleActive'])->name('carga-horaria.toggle');
        });
        
        // ==================== MÓDULOS DE NOTAS ====================
        
        // Módulo: notas
        Route::middleware(['modulo:notas'])->group(function () {
            Route::get('/notas', [NotaController::class, 'index'])->name('notas.index');
            Route::get('/notas/cursos-by-aula', [NotaController::class, 'getCursosByAula'])->name('notas.cursos-by-aula');
            Route::get('/notas/get-data', [NotaController::class, 'getDataForNotas'])->name('notas.get-data');
            Route::post('/notas/save', [NotaController::class, 'saveNotas'])->name('notas.save');
            Route::get('/notas/conclusion/{nota}', [NotaController::class, 'getConclusion'])->name('notas.get-conclusion');
            Route::post('/notas/conclusion', [NotaController::class, 'saveConclusion'])->name('notas.save-conclusion');
            Route::get('/notas/opciones', [NotaController::class, 'getOpcionesNotas'])->name('notas.opciones');
        });
        
        // Módulo: notas-habilitar (solo admin)
        Route::middleware(['modulo:notas-habilitar'])->group(function () {
            Route::post('/notas/toggle-habilitacion', [NotaController::class, 'toggleHabilitacion'])->name('notas.toggle-habilitacion');
        });
        
        // ==================== MÓDULOS DE APRECIACIONES ====================
        
        // Módulo: apreciaciones
        Route::middleware(['modulo:apreciaciones'])->group(function () {
            Route::get('/apreciaciones', [ApreciacionController::class, 'index'])->name('apreciaciones.index');
            Route::get('/apreciaciones/get-data', [ApreciacionController::class, 'getDataForApreciaciones'])->name('apreciaciones.get-data');
            Route::post('/apreciaciones/save', [ApreciacionController::class, 'saveApreciaciones'])->name('apreciaciones.save');
        });
        
        // Módulo: apreciaciones-habilitar (solo admin)
        Route::middleware(['modulo:apreciaciones-habilitar'])->group(function () {
            Route::post('/apreciaciones/toggle-habilitacion', [ApreciacionController::class, 'toggleHabilitacion'])->name('apreciaciones.toggle-habilitacion');
        });
        
        // ==================== CONFIGURACIÓN JERÁRQUICA (solo admin) ====================
        
        // Módulo: evaluaciones-jerarquico
        Route::middleware(['modulo:evaluaciones-jerarquico'])->group(function () {
            Route::get('/evaluaciones-jerarquico', [EvaluacionJerarquicoController::class, 'index'])->name('evaluaciones-jerarquico.index');
            Route::post('/evaluaciones-jerarquico/evaluacion', [EvaluacionJerarquicoController::class, 'storeEvaluacion'])->name('evaluaciones-jerarquico.store');
            Route::put('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'updateEvaluacion'])->name('evaluaciones-jerarquico.update');
            Route::delete('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'destroyEvaluacion'])->name('evaluaciones-jerarquico.destroy');
            Route::patch('/evaluaciones-jerarquico/evaluacion/{evaluacion}/toggle', [EvaluacionJerarquicoController::class, 'toggleActive'])->name('evaluaciones-jerarquico.toggle');
            Route::get('/evaluaciones-jerarquico/evaluacion/{evaluacion}', [EvaluacionJerarquicoController::class, 'getEvaluacion'])->name('evaluaciones-jerarquico.get');
        });
        
        // Módulo: tipos-inasistencia-jerarquico
        Route::middleware(['modulo:tipos-inasistencia-jerarquico'])->group(function () {
            Route::get('/tipos-inasistencia-jerarquico', [TipoInasistenciaJerarquicoController::class, 'index'])->name('tipos-inasistencia-jerarquico.index');
            Route::post('/tipos-inasistencia-jerarquico/tipo', [TipoInasistenciaJerarquicoController::class, 'storeTipo'])->name('tipos-inasistencia-jerarquico.store');
            Route::put('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'updateTipo'])->name('tipos-inasistencia-jerarquico.update');
            Route::delete('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'destroyTipo'])->name('tipos-inasistencia-jerarquico.destroy');
            Route::patch('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}/toggle', [TipoInasistenciaJerarquicoController::class, 'toggleActive'])->name('tipos-inasistencia-jerarquico.toggle');
            Route::get('/tipos-inasistencia-jerarquico/tipo/{tipoInasistencia}', [TipoInasistenciaJerarquicoController::class, 'getTipo'])->name('tipos-inasistencia-jerarquico.get');
        });
        
        // Módulo: tipos-otras-evaluaciones-jerarquico
        Route::middleware(['modulo:tipos-otras-evaluaciones-jerarquico'])->group(function () {
            Route::get('/tipos-otras-evaluaciones-jerarquico', [TipoOtraEvaluacionJerarquicoController::class, 'index'])->name('tipos-otras-evaluaciones-jerarquico.index');
            Route::post('/tipos-otras-evaluaciones-jerarquico/tipo', [TipoOtraEvaluacionJerarquicoController::class, 'storeTipo'])->name('tipos-otras-evaluaciones-jerarquico.store');
            Route::put('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'updateTipo'])->name('tipos-otras-evaluaciones-jerarquico.update');
            Route::delete('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'destroyTipo'])->name('tipos-otras-evaluaciones-jerarquico.destroy');
            Route::patch('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}/toggle', [TipoOtraEvaluacionJerarquicoController::class, 'toggleActive'])->name('tipos-otras-evaluaciones-jerarquico.toggle');
            Route::get('/tipos-otras-evaluaciones-jerarquico/tipo/{tiposOtrasEvaluacione}', [TipoOtraEvaluacionJerarquicoController::class, 'getTipo'])->name('tipos-otras-evaluaciones-jerarquico.get');
        });
        
        // Módulo: competencias-transversales-jerarquico
        Route::middleware(['modulo:competencias-transversales-jerarquico'])->group(function () {
            Route::get('/competencias-transversales-jerarquico', [CompetenciaTransversalJerarquicoController::class, 'index'])->name('competencias-transversales-jerarquico.index');
            Route::post('/competencias-transversales-jerarquico/competencia', [CompetenciaTransversalJerarquicoController::class, 'storeCompetencia'])->name('competencias-transversales-jerarquico.store');
            Route::put('/competencias-transversales-jerarquico/competencia/{competenciasTransversale}', [CompetenciaTransversalJerarquicoController::class, 'updateCompetencia'])->name('competencias-transversales-jerarquico.update');
            Route::delete('/competencias-transversales-jerarquico/competencia/{competenciasTransversale}', [CompetenciaTransversalJerarquicoController::class, 'destroyCompetencia'])->name('competencias-transversales-jerarquico.destroy');
            Route::patch('/competencias-transversales-jerarquico/competencia/{competenciasTransversale}/toggle', [CompetenciaTransversalJerarquicoController::class, 'toggleActive'])->name('competencias-transversales-jerarquico.toggle');
            Route::get('/competencias-transversales-jerarquico/competencia/{competenciasTransversale}', [CompetenciaTransversalJerarquicoController::class, 'getCompetencia'])->name('competencias-transversales-jerarquico.get');
        });
        
        // ==================== REGISTROS (datos) ====================
        
        // Módulo: registro-evaluaciones
        Route::middleware(['modulo:registro-evaluaciones'])->group(function () {
            Route::get('/registro-evaluaciones', [RegistroEvaluacionController::class, 'index'])->name('registro-evaluaciones.index');
            Route::get('/registro-evaluaciones/get-data', [RegistroEvaluacionController::class, 'getDataForRegistro'])->name('registro-evaluaciones.get-data');
            Route::post('/registro-evaluaciones/save', [RegistroEvaluacionController::class, 'saveRegistros'])->name('registro-evaluaciones.save');
            Route::get('/registro-evaluaciones/opciones', [RegistroEvaluacionController::class, 'getOpcionesValoraciones'])->name('registro-evaluaciones.opciones');
        });
        
        // Módulo: registro-evaluaciones-habilitar
        Route::middleware(['modulo:registro-evaluaciones-habilitar'])->group(function () {
            Route::post('/registro-evaluaciones/toggle-habilitacion', [RegistroEvaluacionController::class, 'toggleHabilitacion'])->name('registro-evaluaciones.toggle-habilitacion');
        });
        
        // Módulo: registro-asistencias
        Route::middleware(['modulo:registro-asistencias'])->group(function () {
            Route::get('/registro-asistencias', [RegistroAsistenciaController::class, 'index'])->name('registro-asistencias.index');
            Route::get('/registro-asistencias/get-data', [RegistroAsistenciaController::class, 'getDataForRegistro'])->name('registro-asistencias.get-data');
            Route::post('/registro-asistencias/save', [RegistroAsistenciaController::class, 'saveRegistros'])->name('registro-asistencias.save');
        });
        
        // Módulo: registro-asistencias-habilitar
        Route::middleware(['modulo:registro-asistencias-habilitar'])->group(function () {
            Route::post('/registro-asistencias/toggle-habilitacion', [RegistroAsistenciaController::class, 'toggleHabilitacion'])->name('registro-asistencias.toggle-habilitacion');
        });
        
        // Módulo: registro-otras-evaluaciones
        Route::middleware(['modulo:registro-otras-evaluaciones'])->group(function () {
            Route::get('/registro-otras-evaluaciones', [RegistroOtraEvaluacionController::class, 'index'])->name('registro-otras-evaluaciones.index');
            Route::get('/registro-otras-evaluaciones/get-data', [RegistroOtraEvaluacionController::class, 'getDataForRegistro'])->name('registro-otras-evaluaciones.get-data');
            Route::post('/registro-otras-evaluaciones/save', [RegistroOtraEvaluacionController::class, 'saveRegistros'])->name('registro-otras-evaluaciones.save');
        });
        
        // Módulo: registro-otras-evaluaciones-habilitar
        Route::middleware(['modulo:registro-otras-evaluaciones-habilitar'])->group(function () {
            Route::post('/registro-otras-evaluaciones/toggle-habilitacion', [RegistroOtraEvaluacionController::class, 'toggleHabilitacion'])->name('registro-otras-evaluaciones.toggle-habilitacion');
        });
        
        // Módulo: registro-competencias-transversales
        Route::middleware(['modulo:registro-competencias-transversales'])->group(function () {
            Route::get('/registro-competencias-transversales', [RegistroCompetenciaTransversalController::class, 'index'])->name('registro-competencias-transversales.index');
            Route::get('/registro-competencias-transversales/get-data', [RegistroCompetenciaTransversalController::class, 'getDataForRegistro'])->name('registro-competencias-transversales.get-data');
            Route::post('/registro-competencias-transversales/save', [RegistroCompetenciaTransversalController::class, 'saveRegistros'])->name('registro-competencias-transversales.save');
            Route::post('/registro-competencias-transversales/save-conclusion', [RegistroCompetenciaTransversalController::class, 'saveConclusion'])->name('registro-competencias-transversales.save-conclusion');
            Route::get('/registro-competencias-transversales/opciones', [RegistroCompetenciaTransversalController::class, 'getOpcionesNotas'])->name('registro-competencias-transversales.opciones');
        });
        
        // Módulo: registro-competencias-transversales-habilitar
        Route::middleware(['modulo:registro-competencias-transversales-habilitar'])->group(function () {
            Route::post('/registro-competencias-transversales/toggle-habilitacion', [RegistroCompetenciaTransversalController::class, 'toggleHabilitacion'])->name('registro-competencias-transversales.toggle-habilitacion');
        });
        
        // ==================== CONFIGURACIÓN DEL SISTEMA ====================
        
        // Módulo: configuracion-sistema
        Route::middleware(['modulo:configuracion-sistema'])->group(function () {
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::put('/configuracion/institucion', [ConfiguracionController::class, 'updateInstitucion'])->name('configuracion.update-institucion');
            Route::put('/configuracion/libreta', [ConfiguracionController::class, 'updateLibreta'])->name('configuracion.update-libreta');
            Route::post('/configuracion/delete-logo', [ConfiguracionController::class, 'deleteLogo'])->name('configuracion.delete-logo');
            Route::post('/configuracion/delete-libreta-image', [ConfiguracionController::class, 'deleteLibretaImage'])->name('configuracion.delete-libreta-image');
        });
        
        // Módulo: configuracion-notas
        Route::middleware(['modulo:configuracion-notas'])->group(function () {
            Route::get('/configuracion-notas', [ConfiguracionNotasController::class, 'index'])->name('configuracion-notas.index');
            Route::get('/configuracion-notas/tipo-nota/{tiposNota}', [ConfiguracionNotasController::class, 'getTipoNota'])->name('configuracion-notas.tipo-nota.show');
            Route::post('/configuracion-notas/tipo-nota', [ConfiguracionNotasController::class, 'storeTipoNota'])->name('configuracion-notas.tipo-nota.store');
            Route::put('/configuracion-notas/tipo-nota/{tiposNota}', [ConfiguracionNotasController::class, 'updateTipoNota'])->name('configuracion-notas.tipo-nota.update');
            Route::delete('/configuracion-notas/tipo-nota/{tiposNota}', [ConfiguracionNotasController::class, 'destroyTipoNota'])->name('configuracion-notas.tipo-nota.destroy');
            Route::patch('/configuracion-notas/tipo-nota/{tiposNota}/toggle', [ConfiguracionNotasController::class, 'toggleTipoNota'])->name('configuracion-notas.tipo-nota.toggle');
            Route::get('/configuracion-notas/tipos-by-modulo', [ConfiguracionNotasController::class, 'getTiposNotasByModulo'])->name('configuracion-notas.tipos-by-modulo');
            Route::get('/configuracion-notas/tipos-nota-todos', [ConfiguracionNotasController::class, 'getAllTiposNotas'])->name('configuracion-notas.tipos-nota-todos');
            Route::post('/configuracion-notas/asignar', [ConfiguracionNotasController::class, 'asignarNotasModulo'])->name('configuracion-notas.asignar');
        });
        
        // Módulo: libretas
        Route::middleware(['modulo:libretas'])->group(function () {
            Route::get('/libretas', [LibretaController::class, 'index'])->name('libretas.index');
            Route::get('/libretas/alumnos-by-aula', [LibretaController::class, 'getAlumnosByAula'])->name('libretas.alumnos-by-aula');
            Route::post('/libretas/exportar-aula', [LibretaController::class, 'exportarAula'])->name('libretas.exportar-aula');
            Route::post('/libretas/exportar-alumno', [LibretaController::class, 'exportarAlumno'])->name('libretas.exportar-alumno');
            Route::get('/libretas/previsualizar', [LibretaController::class, 'previsualizar'])->name('libretas.previsualizar');
            Route::get('/libretas/previsualizar-aula', [LibretaController::class, 'previsualizarAula'])->name('libretas.previsualizar-aula');
        });
        
        // ==================== MÓDULOS PARA PERMISOS (solo admin) ====================
        
        // Módulo: modulos-gestion
        Route::middleware(['modulo:modulos-gestion'])->group(function () {
            Route::resource('modulos', ModuloController::class);
            Route::patch('/modulos/{modulo}/toggle', [ModuloController::class, 'toggleActive'])->name('modulos.toggle');
        });
        
        // Módulo: permisos-roles
        Route::middleware(['modulo:permisos-roles'])->group(function () {
            Route::get('/permisos/asignar-roles', [PermisoController::class, 'asignarModulosRol'])->name('permisos.asignar-roles');
            Route::post('/permisos/guardar-rol', [PermisoController::class, 'guardarAsignacionModulosRol'])->name('permisos.guardar-rol');
            Route::get('/permisos/rol/{role}/modulos', [PermisoController::class, 'getModulosByRol'])->name('permisos.rol-modulos');
        });
        
        // Módulo: permisos-usuarios
        Route::middleware(['modulo:permisos-usuarios'])->group(function () {
            Route::get('/permisos/asignar-usuarios', [PermisoController::class, 'asignarModulosUsuario'])->name('permisos.asignar-usuarios');
            Route::post('/permisos/guardar-usuario', [PermisoController::class, 'guardarAsignacionModulosUsuario'])->name('permisos.guardar-usuario');
            Route::get('/permisos/usuario/{user}/modulos-extra', [PermisoController::class, 'getModulosExtraByUser'])->name('permisos.usuario-modulos-extra');
        });


        // ==================== AVANCE DE NOTAS ====================
        // Módulo: avance-notas
        Route::middleware(['modulo:avance-notas'])->group(function () {
            Route::get('/avance-notas', [AvanceNotasController::class, 'index'])->name('avance-notas.index');
            Route::get('/avance-notas/avance-aula', [AvanceNotasController::class, 'getAvanceByAula'])->name('avance-notas.avance-aula');
            Route::get('/avance-notas/resumen-aulas', [AvanceNotasController::class, 'getResumenAulas'])->name('avance-notas.resumen-aulas');
        });


    });




    
    // ==================== RUTAS QUE REQUIEREN AUTENTICACIÓN (sin prefijo admin) ====================
    
    // Rutas para carga horaria (acceso por módulo)
    Route::middleware(['modulo:carga-horaria'])->group(function () {
            Route::get('/carga-horaria/cursos-by-docente', [CargaHorariaController::class, 'getCursosByDocente'])->name('admin.carga-horaria.cursos-by-docente');
            Route::get('/carga-horaria/aulas-by-curso', [CargaHorariaController::class, 'getAulasByCurso'])->name('admin.carga-horaria.aulas-by-curso');
            Route::get('/carga-horaria/verificar-duplicado', [CargaHorariaController::class, 'verificarDuplicado'])->name('admin.carga-horaria.verificar-duplicado');
            Route::get('/carga-horaria/todos-cursos', [CargaHorariaController::class, 'getAllCursos'])->name('admin.carga-horaria.todos-cursos');
    });
    
    // Rutas para aulas (acceso por módulo)
    Route::middleware(['modulo:aulas'])->group(function () {
        Route::get('/aulas/grados-by-nivel', [AulaController::class, 'getGradosByNivel'])->name('admin.aulas.grados-by-nivel');
    });
    
    // ==================== API ROUTES ====================
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/alumnos/store', [App\Http\Controllers\Api\AlumnoApiController::class, 'store'])->name('alumnos.store');
        Route::get('/alumnos/search', [App\Http\Controllers\Api\AlumnoApiController::class, 'search'])->name('alumnos.search');
    });
    
    // ==================== RUTAS LEGACY (comentadas, usar módulos en su lugar) ====================
    // Las rutas específicas por rol ya no son necesarias porque el middleware modulo maneja todo
    
    // Route::middleware(['role:docente'])->prefix('docente')->name('docente.')->group(function () { ... });
    // Route::middleware(['role:apoderado'])->prefix('apoderado')->name('apoderado.')->group(function () { ... });
});