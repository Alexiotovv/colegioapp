{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Colegio') - ColcoopCV</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    @yield('css')
    
    <style>
        :root {
            --primary-color: #2c5031;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #16a085;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        .wrapper {
            display: flex;
            flex: 1;
            overflow-y: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            min-width: 280px;
            max-width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #2b542c 100%);
            transition: all 0.3s ease;
            overflow-y: auto;
            color: white;
        }
        
        .sidebar.collapsed {
            min-width: 0;
            max-width: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: #34495e;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: #3498db;
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        /* Content */
        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-x: auto;
            transition: all 0.3s ease;
            width: calc(100% - 280px);
        }
        
        .content.expanded {
            width: 100%;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(127, 98, 98, 0.08);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card {
            background: linear-gradient(189deg, #107948 0%, #d7c3c3 100%);
            color: #040303;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                top: 60px;
                height: calc(100% - 60px);
                z-index: 1040;
                box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .content {
                width: 100% !important;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #55555594;
        }
    </style>
    {{-- Incluir estilos CSS del progress bar --}}
    @push('styles')
    <style>
        .progress-container {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 12px;
            color: #555;
        }
        .progress-title {
            font-weight: 500;
        }
        .progress-percentage {
            font-weight: 600;
            color: var(--primary-color);
        }
        .progress-bar-container {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background-color: var(--primary-color);
            width: 0%;
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 10px;
            color: #888;
        }
        .progress-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .progress-stats i {
            font-size: 10px;
        }
        .progress-stats .completed {
            color: #28a745;
        }
        .progress-stats .pending {
            color: #dc3545;
        }
    </style>
    @endpush


</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid">
        <button class="btn btn-outline-light" id="toggleSidebar" style="border: none;">
            <i class="fas fa-bars"></i>
        </button>
        
        <a class="navbar-brand ms-3" href="{{ route('dashboard') }}">
            <i class="fas fa-school me-2"></i>
            <strong>ColcoopCV</strong> - Sistema de Gestión Escolar
        </a>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i>
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebarMenu">
        <div class="p-3">
            <div class="text-center mb-4">
                <div class="bg-white rounded-circle p-2 d-inline-block mb-2" style="width: 70px; height: 70px;">
                    <i class="fas fa-graduation-cap fa-3x" style="color: #2c3e50;"></i>
                </div>
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <small class="text-white-50">
                    <i class="fas fa-tag me-1"></i>{{ ucfirst(Auth::user()->role->nombre) }}
                </small>
            </div>
        </div>
        
        <nav class="nav flex-column px-3">
            @auth
                @if(Auth::user()->isAdmin())
                    <!-- Menú Admin -->
                    <div class="mb-2">
                        <div class="text-uppercase small text-white-50 mb-2">
                            <i class="fas fa-tachometer-alt me-1"></i> PRINCIPAL
                        </div>
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>
                    
                    <div class="mb-2">
                        <div class="text-uppercase small text-white-50 mb-2">
                            <i class="fas fa-cog me-1"></i> ADMINISTRACIÓN
                        </div>
                        <a href="#menuUsuarios" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-users"></i> Usuarios <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuUsuarios">
                            <a href="{{ route('admin.users.index') }}" class="nav-link small">📋 Listar Usuarios</a>
                            <a href="{{ route('admin.users.create') }}" class="nav-link small">➕ Crear Usuario</a>
                        </div>
                        
                        <!-- Alumnos -->
                        <a href="#menuAlumnos" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-user-graduate"></i> Alumnos <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuAlumnos">
                        <a href="{{ route('admin.alumnos.index') }}" class="nav-link small">
                            <i class="fas fa-list me-1"></i> Listar Alumnos
                        </a>
                        <a href="{{ route('admin.alumnos.create') }}" class="nav-link small">
                            <i class="fas fa-plus me-1"></i> Nuevo Alumno
                        </a>
                        </div>

                        <a href="#menuAcademicos" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-calendar-alt"></i> Año Académico <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuAcademicos">
                            <a href="{{ route('admin.anios.index') }}" class="nav-link small">📅 Gestionar Años</a>
                            <a href="{{ route('admin.configuracion-academica.index') }}" class="nav-link small">📚 Conf. Académica</a>
                            <a href="{{ route('admin.aulas.index') }}" class="nav-link small">📚 Aulas</a>
                            <a href="{{ route('admin.matriculas.index') }}" class="nav-link small">📚 Matrículas</a>
                            <a href="{{ route('admin.carga-horaria.index') }}" class="nav-link small">📝 Carga Horaria</a>
                        </div>
                        


                        <!-- Apoderados -->
                        <a href="#menuApoderados" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-users"></i> Apoderados <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuApoderados">
                            <a href="{{ route('admin.apoderados.index') }}" class="nav-link small">
                                <i class="fas fa-list me-1"></i> Listar Apoderados
                            </a>
                            <a href="{{ route('admin.apoderados.create') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i> Nuevo Apoderado
                            </a>
                        </div>
                        
                        <!-- Periodos Académicos -->
                        <a href="#menuPeriodos" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-calendar-week"></i> Periodos <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuPeriodos">
                            <a href="{{ route('admin.periodos.index') }}" class="nav-link small">
                                <i class="fas fa-list me-1"></i> Listar Periodos
                            </a>
                            <a href="{{ route('admin.periodos.create') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i> Nuevo Periodo
                            </a>
                        </div>
                        <a href="#menuMatriculas" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-address-card"></i>Registro Notas <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuMatriculas">
                            <a href="{{route('admin.notas.index')}}" class="nav-link small">📋 Notas de Libreta</a>
                            <a href="{{ route('admin.registro-competencias-transversales.index') }}" class="nav-link small">📋 Comp. Transls.</a>
                            <a href="{{ route('admin.apreciaciones.index') }}" class="nav-link small">📋 Aprec. del Tutor</a>
                            <a href="{{ route('admin.registro-evaluaciones.index') }}" class="nav-link small">📋 Eval. PP.FF.</a>
                            <a href="{{ route('admin.registro-asistencias.index') }}" class="nav-link small">📋 Asistencias
                            <a href="{{ route('admin.registro-otras-evaluaciones.index') }}" class="nav-link small">📋 Comporta. y Otros</a>
                        </a>
                        </div>
                        
                        <!-- Evaluaciones -->
                        <a href="#menuEvaluaciones" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-clipboard-list"></i> Gestión Notas<i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuEvaluaciones">
                            <a href="{{ route('admin.cursos-jerarquico.index') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i>Libretas(Cursos)</a>
                            <a href="{{ route('admin.competencias-transversales-jerarquico.index') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i> Comport. Transv.
                            </a>
                            <a href="{{ route('admin.evaluaciones-jerarquico.index') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i>Eval PP.FF.
                            </a>
                            <a href="{{ route('admin.tipos-inasistencia-jerarquico.index') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i> Asistencia
                            </a>
                            <a href="{{ route('admin.tipos-otras-evaluaciones-jerarquico.index') }}" class="nav-link small">
                                <i class="fas fa-plus me-1"></i> Comport. y Otros
                            </a>
                            <a href="{{ route('admin.configuracion-notas.index') }}" class="nav-link small">
                                <i class="fas fa-cog me-1"></i> Tipos de Notas
                            </a>
                        </div>




                        <a href="#menuPagos" data-bs-toggle="collapse" class="nav-link">
                            <i class="fas fa-dollar-sign"></i> Pagos <i class="fas fa-chevron-down float-end"></i>
                        </a>
                        <div class="collapse ps-3" id="menuPagos">
                            <a href="#" class="nav-link small">💰 Pensiones</a>
                            <a href="#" class="nav-link small">📊 Reportes</a>
                        </div>
                    </div>
                    



                    <!-- Configuración -->
                    <a href="#menuConfiguracion" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-cog"></i> Configuración <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse ps-3" id="menuConfiguracion">
                        <a href="{{ route('admin.configuracion.index') }}" class="nav-link small">
                            <i class="fas fa-sliders-h me-1"></i> Configuración del Sistema
                        </a>
                    </div>
                    <!-- Libretas -->
                    <a href="#menuLibretas" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-address-book"></i> Libretas <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse ps-3" id="menuLibretas">
                        <a href="{{ route('admin.libretas.index') }}" class="nav-link small">
                            <i class="fas fa-print me-1"></i> Exportar Libretas
                        </a>
                    </div>

                @elseif(Auth::user()->isDocente())
                    <!-- Menú Docente -->
                    <div class="mb-2">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>

                        <div class="nav-link">
                            <a href="{{route('admin.notas.index')}}" class="nav-link small">📋 Registro de Notas</a>
                        </div>
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-line"></i> Mis Alumnos
                        </a>
                    </div>

                @elseif(Auth::user()->isTutor())
                    <div class="mb-2">
                        <div class="nav-link">
                            <a href="{{ route('admin.apreciaciones.index') }}" class="nav-link small">📋 Apreciaciones</a>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="nav-link">
                            <a href="{{ route('admin.registro-evaluaciones.index') }}" class="nav-link small">📋 Registrar Evaluaciones</a>
                        </div>
                    </div>

                    <!-- Asistencias -->
                    <a href="#menuAsistencias" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-calendar-check"></i> Asistencias <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse ps-3" id="menuAsistencias">
                        <a href="{{ route('admin.registro-asistencias.index') }}" class="nav-link small">
                            <i class="fas fa-edit me-1"></i> Registrar Asistencias
                        </a>
                    </div>

                    <!-- Otras Evaluaciones -->
                    <a href="#menuOtrasEvaluaciones" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-clipboard-list"></i> Otras Evals. <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse ps-3" id="menuOtrasEvaluaciones">
                        <a href="{{ route('admin.registro-otras-evaluaciones.index') }}" class="nav-link small">
                            <i class="fas fa-edit me-1"></i> Registrar Evaluaciones
                        </a>
                    </div>
                    <!-- Competencias Transversales -->
                    <a href="#menuCompTransversales" data-bs-toggle="collapse" class="nav-link">
                        <i class="fas fa-exchange-alt"></i> Comp. Transversales <i class="fas fa-chevron-down float-end"></i>
                    </a>
                    <div class="collapse ps-3" id="menuCompTransversales">
                        <a href="{{ route('admin.registro-competencias-transversales.index') }}" class="nav-link small">
                            <i class="fas fa-edit me-1"></i> Registrar Competencias
                        </a>
                    </div>

                @elseif(Auth::user()->isApoderado())
                    <!-- Menú Apoderado -->
                    <div class="mb-2">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-child"></i> Mis Hijos
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Ver Notas
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-dollar-sign"></i> Pagos
                        </a>
                    </div>
                @endif
            @endauth
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@yield('scripts')

<script>
    $(document).ready(function() {
        // Toggle sidebar
        const toggleSidebar = $('#toggleSidebar');
        const sidebar = $('#sidebarMenu');
        const content = $('.content');
        
        // Cargar estado guardado
        if (localStorage.getItem('sidebarCollapsed') === 'true' && $(window).width() > 768) {
            sidebar.addClass('collapsed');
            content.addClass('expanded');
        }
        
        toggleSidebar.on('click', function() {
            if ($(window).width() > 768) {
                sidebar.toggleClass('collapsed');
                content.toggleClass('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.hasClass('collapsed'));
            } else {
                sidebar.toggleClass('show');
            }
        });
        
        // Cerrar sidebar en móvil al hacer clic fuera
        $(document).on('click', function(event) {
            if ($(window).width() <= 768) {
                if (!sidebar.is(event.target) && !sidebar.has(event.target).length && 
                    !toggleSidebar.is(event.target) && !toggleSidebar.has(event.target).length) {
                    sidebar.removeClass('show');
                }
            }
        });
        
        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
    
    // Función global para confirmar eliminación
    function confirmDelete(formId, message = '¿Estás seguro de eliminar este registro?') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
        return false;
    }
</script>

</body>
</html>