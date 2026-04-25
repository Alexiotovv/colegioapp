{{-- resources/views/permisos/asignar-roles.blade.php --}}
@extends('layouts.app')

@section('title', 'Asignar Módulos a Roles')

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
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
</style>
@endsection

@section('content')
@include('partials.toast')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-link me-2" style="color: var(--primary-color);"></i>
            Asignar Módulos a Roles
        </h4>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="permiso-card">
                <h5 class="mb-3">
                    <i class="fas fa-tags me-2"></i>
                    Seleccionar Rol
                </h5>
                <select class="form-select" id="rolSelect">
                    <option value="">Seleccionar rol</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}">
                            {{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="permiso-card" id="modulosContainer" style="display: none;">
                <h5 class="mb-3">
                    <i class="fas fa-cubes me-2"></i>
                    Módulos Disponibles
                    <span id="rolNombre" class="text-muted"></span>
                </h5>
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
let currentRolId = null;
let modulosData = @json($modulos);
let asignaciones = @json($asignaciones);

$('#rolSelect').on('change', function() {
    currentRolId = $(this).val();
    let rolNombre = $(this).find('option:selected').data('nombre');
    
    if (currentRolId) {
        let asignados = asignaciones[currentRolId] || [];
        
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
        $('#rolNombre').text(`- ${rolNombre}`);
        $('#modulosContainer').show();
    } else {
        $('#modulosContainer').hide();
    }
});

$('#btnGuardarAsignacion').on('click', function() {
    if (!currentRolId) return;
    
    let selectedModulos = [];
    $('input[type="checkbox"]:checked').each(function() {
        selectedModulos.push($(this).val());
    });
    
    $.ajax({
        url: '{{ route("admin.permisos.guardar-rol") }}',
        method: 'POST',
        data: {
            rol_id: currentRolId,
            modulos: selectedModulos,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toast.success(response.message);
                // Actualizar asignaciones locales
                asignaciones[currentRolId] = selectedModulos;
            }
        },
        error: function() {
            toast.error('Error al guardar');
        }
    });
});
</script>
@endsection