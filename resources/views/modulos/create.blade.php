{{-- resources/views/modulos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Módulo')

@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .required-field::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 4px;
    }
    
    /* Estilos para Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(3rem + 2px);
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 2rem;
    }
</style>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-plus-circle me-2" style="color: var(--primary-color);"></i>
            Nuevo Módulo
        </h4>
        <a href="{{ route('admin.modulos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>
    
    <div class="form-container">
        <form method="POST" action="{{ route('admin.modulos.store') }}" id="moduloForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="codigo" class="form-label required-field">Código</label>
                    <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                           id="codigo" name="codigo" value="{{ old('codigo') }}" required>
                    <small class="text-muted">Ej: notas, usuarios, dashboard. (Importante establecer el código correctamente del nombre dela ruta ej. admin.avance-notas.index. el codigo sería: avance-notas)</small>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field">Nombre</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ruta" class="form-label">Ruta</label>
                    <select class="form-select @error('ruta') is-invalid @enderror" 
                            id="ruta" name="ruta" style="width: 100%;">
                        <option value="">Seleccionar ruta existente...</option>
                        @foreach($rutasDisponibles as $ruta)
                            <option value="{{ $ruta['name'] }}" {{ old('ruta') == $ruta['name'] ? 'selected' : '' }}>
                                {{ $ruta['name'] }} → {{ $ruta['uri'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Selecciona una ruta existente del sistema</small>
                    @error('ruta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="icono" class="form-label">Icono</label>
                    <div class="input-group">
                        <span class="input-group-text" id="iconoPreview"><i class="fas fa-cube"></i></span>
                        <input type="text" class="form-control @error('icono') is-invalid @enderror" 
                            id="icono" name="icono" value="{{ old('icono') }}" placeholder="Ej: fa-users, fa-home">
                        <button class="btn btn-outline-secondary" type="button" id="btnSeleccionarIcono">
                            <i class="fas fa-search"></i> Seleccionar
                        </button>
                    </div>
                    <small class="text-muted">Haz clic en "Seleccionar" para elegir un icono de la lista</small>
                    <div id="iconoSeleccionado" class="mt-2" style="display: none;">
                        <span class="badge bg-secondary">
                            <i class="fas" id="iconoSeleccionadoPreview"></i>
                            <span id="iconoSeleccionadoTexto"></span>
                        </span>
                    </div>
                    @error('icono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="padre_id" class="form-label">Módulo Padre</label>
                    <select class="form-select @error('padre_id') is-invalid @enderror" id="padre_id" name="padre_id">
                        <option value="">Ninguno (menú principal)</option>
                        @foreach($modulos as $modulo)
                            <option value="{{ $modulo->id }}">{{ $modulo->nombre }} ({{ $modulo->codigo }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Para crear submenús</small>
                    @error('padre_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('orden') is-invalid @enderror" 
                           id="orden" name="orden" value="{{ old('orden', 0) }}">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Guardar Módulo
                    </button>
                    <a href="{{ route('admin.modulos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para seleccionar iconos -->
<div class="modal fade" id="modalIconos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-icons me-2"></i>
                    Seleccionar Icono
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="buscadorIconos" placeholder="Buscar icono...">
                </div>
                <div class="row" id="listaIconos">
                    <!-- Los iconos se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('#ruta').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccionar ruta existente...',
            allowClear: true,
            width: '100%'
        });
        
        // Lista de iconos disponibles
        const iconosDisponibles = [
            'fa-home', 'fa-user', 'fa-users', 'fa-graduation-cap', 'fa-book',
            'fa-pencil', 'fa-edit', 'fa-file-text', 'fa-folder', 'fa-calendar',
            'fa-clock', 'fa-check', 'fa-times', 'fa-chart-bar', 'fa-chart-line',
            'fa-table', 'fa-list', 'fa-id-card', 'fa-address-book', 'fa-certificate',
            'fa-university', 'fa-comments', 'fa-bell', 'fa-cog', 'fa-database',
            'fa-cogs', 'fa-sliders-h', 'fa-wrench', 'fa-tools', 'fa-shield-alt',
            'fa-lock', 'fa-unlock', 'fa-key', 'fa-user-cog', 'fa-users-cog',
            'fa-server', 'fa-cloud', 'fa-upload', 'fa-download', 'fa-sync',
            'fa-power-off', 'fa-plug', 'fa-bug', 'fa-eye', 'fa-eye-slash',
            'fa-info-circle', 'fa-question-circle', 'fa-exclamation-triangle',
            'fa-life-ring', 'fa-clipboard', 'fa-archive', 'fa-cube', 'fa-cubes',
            'fa-dashboard', 'fa-tachometer-alt', 'fa-chalkboard', 'fa-school',
            'fa-door-open', 'fa-clock', 'fa-calendar-week', 'fa-calendar-alt',
            'fa-address-card', 'fa-print', 'fa-tags', 'fa-tag', 'fa-plus',
            'fa-plus-circle', 'fa-minus', 'fa-trash', 'fa-trash-alt', 'fa-undo',
            'fa-redo', 'fa-search', 'fa-filter', 'fa-sort', 'fa-sort-down',
            'fa-sort-up', 'fa-download', 'fa-upload', 'fa-external-link-alt',
            'fa-link', 'fa-paperclip', 'fa-image', 'fa-file', 'fa-file-pdf',
            'fa-file-excel', 'fa-file-word', 'fa-envelope', 'fa-phone', 'fa-mobile-alt',
            'fa-map-marker-alt', 'fa-building', 'fa-globe', 'fa-language',
            'fa-palette', 'fa-paint-brush', 'fa-code', 'fa-terminal', 'fa-database'
        ];
        
        // Renderizar iconos en el modal
        function renderizarIconos(busqueda = '') {
            const contenedor = $('#listaIconos');
            contenedor.empty();
            
            let iconosFiltrados = iconosDisponibles;
            if (busqueda) {
                iconosFiltrados = iconosDisponibles.filter(icono => 
                    icono.toLowerCase().includes(busqueda.toLowerCase())
                );
            }
            
            if (iconosFiltrados.length === 0) {
                contenedor.html('<div class="col-12 text-center py-5">No se encontraron iconos</div>');
                return;
            }
            
            iconosFiltrados.forEach(icono => {
                const iconoHtml = `
                    <div class="col-md-2 col-sm-3 col-4 mb-3">
                        <div class="icono-item text-center p-2 rounded" data-icono="${icono}" style="cursor: pointer; border: 1px solid #dee2e6; transition: all 0.2s;">
                            <i class="fas ${icono} fa-2x mb-2"></i>
                            <div class="small text-muted">${icono}</div>
                        </div>
                    </div>
                `;
                contenedor.append(iconoHtml);
            });
        }
        
        // Abrir modal de iconos
        $('#btnSeleccionarIcono').on('click', function() {
            renderizarIconos();
            $('#modalIconos').modal('show');
        });
        
        // Buscar iconos
        $('#buscadorIconos').on('keyup', function() {
            renderizarIconos($(this).val());
        });
        
        // Seleccionar icono
        $(document).on('click', '.icono-item', function() {
            const icono = $(this).data('icono');
            $('#icono').val(icono);
            $('#iconoPreview').html(`<i class="fas ${icono}"></i>`);
            $('#iconoSeleccionadoPreview').removeClass().addClass(`fas ${icono}`);
            $('#iconoSeleccionadoTexto').text(icono);
            $('#iconoSeleccionado').show();
            $('#modalIconos').modal('hide');
            
            // Mostrar toast de confirmación
            Swal.fire({
                icon: 'success',
                title: 'Icono seleccionado',
                text: `Has seleccionado: ${icono}`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        });
        
        // Hover effect para iconos
        $(document).on('mouseenter', '.icono-item', function() {
            $(this).css({
                'background-color': '#e9ecef',
                'transform': 'scale(1.05)',
                'box-shadow': '0 2px 8px rgba(0,0,0,0.1)'
            });
        }).on('mouseleave', '.icono-item', function() {
            $(this).css({
                'background-color': 'transparent',
                'transform': 'scale(1)',
                'box-shadow': 'none'
            });
        });
        
        // Previsualizar icono en tiempo real
        $('#icono').on('keyup change', function() {
            let icono = $(this).val();
            if (icono && iconosDisponibles.includes(icono)) {
                $('#iconoPreview').html(`<i class="fas ${icono}"></i>`);
                $('#iconoSeleccionadoPreview').removeClass().addClass(`fas ${icono}`);
                $('#iconoSeleccionadoTexto').text(icono);
                $('#iconoSeleccionado').show();
            } else if (icono) {
                $('#iconoPreview').html(`<i class="fas ${icono}"></i>`);
                $('#iconoSeleccionado').hide();
            } else {
                $('#iconoPreview').html('<i class="fas fa-cube"></i>');
                $('#iconoSeleccionado').hide();
            }
        });
        
        $('#moduloForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn').html('<span class="loading-spinner me-2"></span> Guardando...');
        });
    });
</script>

<style>
    .icono-item {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .icono-item:hover {
        background-color: #e9ecef;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .modal-dialog-scrollable .modal-body {
        max-height: 500px;
    }
    #listaIconos {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endsection