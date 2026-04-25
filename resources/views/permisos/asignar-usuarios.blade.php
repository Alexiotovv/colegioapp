{{-- resources/views/permisos/asignar-usuarios.blade.php --}}
@extends('layouts.app')

@section('title', 'Asignar Módulos Extras a Usuarios')

@section('css')
<style>
    .permiso-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .modulos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }
    
    .modulo-item {
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s;
    }
    
    .modulo-item:hover {
        background: #e9ecef;
    }
    
    .info-usuario {
        background: #e8f0fe;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-plus me-2" style="color: var(--primary-color);"></i>
            Asignar Módulos Extras a Usuarios
        </h4>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="permiso-card">
                <h5 class="mb-3">
                    <i class="fas fa-users me-2"></i>
                    Seleccionar Usuario
                </h5>
                <select class="form-select" id="usuarioSelect">
                    <option value="">Seleccionar usuario</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" 
                                data-nombre="{{ $usuario->name }}"
                                data-rol="{{ ucfirst($usuario->role->nombre) }}">
                            {{ $usuario->name }} ({{ ucfirst($usuario->role->nombre) }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="permiso-card" id="modulosContainer" style="display: none;">
                <h5 class="mb-3">
                    <i class="fas fa-cubes me-2"></i>
                    Módulos Extras
                    <span id="usuarioInfo" class="text-muted"></span>
                </h5>
                <div class="info-usuario" id="infoUsuario"></div>
                <div id="modulosLista" class="modulos-grid"></div>
                <div class="text-end mt-3">
                    <button class="btn btn-primary" id="btnGuardarAsignacion">
                        <i class="fas fa-save me-2"></i> Guardar Asignación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentUsuarioId = null;
let modulosData = @json($modulos);
let asignaciones = @json($asignaciones);

$('#usuarioSelect').on('change', function() {
    currentUsuarioId = $(this).val();
    let usuarioNombre = $(this).find('option:selected').data('nombre');
    let usuarioRol = $(this).find('option:selected').data('rol');
    
    if (currentUsuarioId) {
        let asignados = asignaciones[currentUsuarioId] || [];
        
        let infoHtml = `
            <strong><i class="fas fa-user me-2"></i>${usuarioNombre}</strong><br>
            <small><i class="fas fa-tag me-2"></i>Rol principal: ${usuarioRol}</small>
            <small class="d-block mt-2 text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Los módulos seleccionados se agregarán además de los que ya tiene por su rol.
            </small>
        `;
        $('#infoUsuario').html(infoHtml);
        
        let html = '';
        for (let modulo of modulosData) {
            let checked = asignados.includes(modulo.id) ? 'checked' : '';
            html += `
                <div class="modulo-item">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${modulo.id}" id="mod_${modulo.id}" ${checked}>
                        <label class="form-check-label" for="mod_${modulo.id}">
                            <i class="fas ${modulo.icono || 'fa-cube'} me-2"></i>
                            ${modulo.nombre}
                            <small class="text-muted d-block">${modulo.ruta || ''}</small>
                        </label>
                    </div>
                </div>
            `;
        }
        
        $('#modulosLista').html(html);
        $('#usuarioInfo').text(`- ${usuarioNombre}`);
        $('#modulosContainer').show();
    } else {
        $('#modulosContainer').hide();
    }
});

$('#btnGuardarAsignacion').on('click', function() {
    if (!currentUsuarioId) return;
    
    let selectedModulos = [];
    $('input[type="checkbox"]:checked').each(function() {
        selectedModulos.push($(this).val());
    });
    
    $.ajax({
        url: '{{ route("admin.permisos.guardar-usuario") }}',
        method: 'POST',
        data: {
            usuario_id: currentUsuarioId,
            modulos: selectedModulos,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toast.success(response.message);
                asignaciones[currentUsuarioId] = selectedModulos;
            }
        },
        error: function() {
            toast.error('Error al guardar');
        }
    });
});
</script>
@endsection