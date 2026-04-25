-- Módulos necesarios para que funcione todo
INSERT INTO modulos (codigo, nombre, ruta, icono, orden, activo) VALUES
-- Administración
('users', 'Usuarios', 'admin.users.index', 'fa-users', 10, 1),
('anios', 'Años Académicos', 'admin.anios.index', 'fa-calendar', 11, 1),
('periodos', 'Periodos', 'admin.periodos.index', 'fa-calendar-week', 12, 1),
('alumnos', 'Alumnos', 'admin.alumnos.index', 'fa-user-graduate', 20, 1),
('apoderados', 'Apoderados', 'admin.apoderados.index', 'fa-users', 21, 1),
('matriculas', 'Matrículas', 'admin.matriculas.index', 'fa-address-card', 22, 1),
('cursos', 'Cursos', 'admin.cursos.index', 'fa-book', 30, 1),
('aulas', 'Aulas', 'admin.aulas.index', 'fa-door-open', 31, 1),
('carga-horaria', 'Carga Horaria', 'admin.carga-horaria.index', 'fa-clock', 32, 1),

-- Configuración académica
('configuracion-academica', 'Configuración Académica', 'admin.configuracion-academica.index', 'fa-sliders-h', 40, 1),
('cursos-jerarquico', 'Configurar Cursos', 'admin.cursos-jerarquico.index', 'fa-sitemap', 41, 1),

-- Notas
('notas', 'Registro de Notas', 'admin.notas.index', 'fa-edit', 50, 1),
('apreciaciones', 'Apreciaciones', 'admin.apreciaciones.index', 'fa-comment-dots', 51, 1),
('registro-evaluaciones', 'Evaluaciones', 'admin.registro-evaluaciones.index', 'fa-clipboard-list', 52, 1),
('registro-asistencias', 'Asistencias', 'admin.registro-asistencias.index', 'fa-calendar-check', 53, 1),
('registro-otras-evaluaciones', 'Otras Evaluaciones', 'admin.registro-otras-evaluaciones.index', 'fa-tasks', 54, 1),
('registro-competencias-transversales', 'Competencias Transversales', 'admin.registro-competencias-transversales.index', 'fa-exchange-alt', 55, 1),

-- Reportes
('libretas', 'Libretas', 'admin.libretas.index', 'fa-print', 60, 1),

-- Configuración del sistema
('configuracion-sistema', 'Configuración Sistema', 'admin.configuracion.index', 'fa-cog', 70, 1),
('configuracion-notas', 'Configuración Notas', 'admin.configuracion-notas.index', 'fa-tags', 71, 1),

-- Gestión de módulos y permisos
('modulos-gestion', 'Gestión de Módulos', 'admin.modulos.index', 'fa-cubes', 80, 1),
('permisos-roles', 'Permisos por Rol', 'admin.permisos.asignar-roles', 'fa-tag', 81, 1),
('permisos-usuarios', 'Permisos por Usuario', 'admin.permisos.asignar-usuarios', 'fa-user-plus', 82, 1);