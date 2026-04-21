{{-- resources/views/tipos-otras-evaluaciones-jerarquico/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Otras Evaluaciones')

@section('css')
<style>
    .tree-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .tree-level-1 {
        margin-left: 0;
        border-left: 3px solid #2c5031;
        padding-left: 15px;
        margin-bottom: 20px;
    }
    
    .tree-level-2 {
        margin-left: 30px;
        border-left: 2px solid #c8a951;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    
    .tree-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .tree-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .tree-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }
    
    .tree-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .toggle-icon {
        cursor: pointer;
        transition: transform 0.3s;
        width: 24px;
        text-align: center;
    }
    
    .toggle-icon.rotated {
        transform: rotate(90deg);
    }
    
    .tree-children {
        margin-top: 10px;
        margin-left: 25px;
        display: none;
    }
    
    .tree-children.show {
        display: block;
    }
    
    .badge-numerico {
        background-color: #28a745;
        color: white;
    }
    
    .badge-literal {
        background-color: #17a2b8;
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-clipboard-list me-2" style="color: var(--primary-color);"></i>
            Gestión de Otras Evaluaciones
        </h4>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTipoOtraEvaluacion" onclick="resetTipoForm()">
                <i class="fas fa-plus me-2"></i> Nuevo Tipo
            </button>
        </div>
    </div>
    
    <div class="tree-container" id="treeContainer">
        <div id="treeContent">
            @include('tipos-otras-evaluaciones-jerarquico.partials.tree', ['niveles' => $data])
        </div>
    </div>
</div>

<!-- Modal Tipo Otra Evaluación -->
@include('tipos-otras-evaluaciones-jerarquico.modals.tipo-modal')
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    window.toggleChildren = function(element) {
        let parent = $(element).closest('.tree-item');
        let children = parent.siblings('.tree-children');
        let icon = $(element).find('.toggle-icon');
        
        children.toggleClass('show');
        icon.toggleClass('rotated');
    };
    
    window.showTipoModal = function(tipoId, nivelId) {
        resetTipoForm();
        
        if (tipoId) {
            $('#modalTipoTitle').text('Editar Tipo de Evaluación');
            $('#btnSaveTipo').html('<i class="fas fa-save me-2"></i> Actualizar');
            
            $.ajax({
                url: '/admin/tipos-otras-evaluaciones-jerarquico/tipo/' + tipoId,
                method: 'GET',
                success: function(response) {
                    $('#tipo_id').val(response.id);
                    $('#tipo_nombre').val(response.nombre);
                    $('#tipo_descripcion').val(response.descripcion);
                    $('#tipo_tipo_dato').val(response.tipo_dato);
                    $('#tipo_orden').val(response.orden);
                    $('#tipo_nivel_id').val(response.nivel_id);
                    $('#tipo_activo').prop('checked', response.activo);
                    
                    if (response.tipo_dato === 'NUMERICO') {
                        $('#tipo_min_valor').val(response.min_valor || 1);
                        $('#tipo_max_valor').val(response.max_valor || 40);
                        $('.literal-fields').hide();
                        $('.numeric-fields').show();
                    } else {
                        let opciones = response.opciones_literales ? response.opciones_literales.join(', ') : '';
                        $('#tipo_opciones_literales').val(opciones);
                        $('.numeric-fields').hide();
                        $('.literal-fields').show();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
                }
            });
        } else {
            $('#modalTipoTitle').text('Nuevo Tipo de Evaluación');
            $('#btnSaveTipo').html('<i class="fas fa-save me-2"></i> Guardar');
            $('#tipo_nivel_id').val(nivelId);
            $('#formTipo')[0].reset();
            $('#tipo_activo').prop('checked', true);
            $('.literal-fields').hide();
            $('.numeric-fields').show();
        }
        
        $('#modalTipoOtraEvaluacion').modal('show');
    };
    
    window.resetTipoForm = function() {
        $('#formTipo')[0].reset();
        $('#tipo_id').val('');
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
    };
    
    window.deleteTipo = function(tipoId) {
        Swal.fire({
            title: '¿Eliminar tipo?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/tipos-otras-evaluaciones-jerarquico/tipo/' + tipoId,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    };
    
    window.toggleTipo = function(tipoId) {
        $.ajax({
            url: '/admin/tipos-otras-evaluaciones-jerarquico/tipo/' + tipoId + '/toggle',
            method: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    location.reload();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
            }
        });
    };
    
    $('#tipo_tipo_dato').on('change', function() {
        if ($(this).val() === 'NUMERICO') {
            $('.numeric-fields').show();
            $('.literal-fields').hide();
        } else {
            $('.numeric-fields').hide();
            $('.literal-fields').show();
        }
    });
    
    $('#formTipo').on('submit', function(e) {
        e.preventDefault();
        let tipoId = $('#tipo_id').val();
        let url = tipoId ? '/admin/tipos-otras-evaluaciones-jerarquico/tipo/' + tipoId : '{{ route("admin.tipos-otras-evaluaciones-jerarquico.store") }}';
        let method = tipoId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#modalTipoOtraEvaluacion').modal('hide');
                    Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    for (let field in errors) {
                        $(`#tipo_${field}_error`).text(errors[field][0]);
                        $(`#tipo_${field}`).addClass('is-invalid');
                    }
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                }
            }
        });
    });
    
    function bindTreeEvents() {
        $('.tree-level-1 .toggle-icon').addClass('rotated');
        $('.tree-level-1 .tree-children').addClass('show');
    }
    
    bindTreeEvents();
});
</script>
@endsection