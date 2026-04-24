{{-- resources/views/configuracion-notas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Configuración de Notas')

@section('css')
<style>
    .config-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 20px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 12px 24px;
        transition: all 0.3s;
    }
    
    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        background: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
        background: transparent;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        overflow-x: auto;
    }
    
    .badge-numerico {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-literal {
        background-color: #28a745;
        color: white;
    }
    
    .badge-activo {
        background-color: #28a745;
        color: white;
    }
    
    .badge-inactivo {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @include('partials.toast')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-cog me-2" style="color: var(--primary-color);"></i>
            Configuración de Notas
        </h4>
    </div>
    
    <div class="config-card">
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tipos-notas-tab" data-bs-toggle="tab" data-bs-target="#tipos-notas" type="button" role="tab">
                    <i class="fas fa-tag me-2"></i>Tipos de Notas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="asignacion-tab" data-bs-toggle="tab" data-bs-target="#asignacion" type="button" role="tab">
                    <i class="fas fa-link me-2"></i>Asignación por Módulo
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="configTabsContent">
            
            <!-- ==================== TAB TIPOS DE NOTAS ==================== -->
            <div class="tab-pane fade show active" id="tipos-notas" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" onclick="openTipoNotaModal()">
                        <i class="fas fa-plus me-2"></i>Nuevo Tipo de Nota
                    </button>
                </div>
                
                <div class="table-container">
                    <table class="table table-hover" id="tablaTiposNotas">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Valor Numérico</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tiposNotas as $tipo)
                            <tr data-id="{{ $tipo->id }}">
                                <td class="codigo"><strong>{{ $tipo->codigo }}</strong></td>
                                <td class="nombre">{{ $tipo->nombre }}</td>
                                <td class="tipo_dato">
                                    @if($tipo->tipo_dato === 'NUMERICO')
                                        <span class="badge badge-numerico">Numérico</span>
                                    @else
                                        <span class="badge badge-literal">Literal</span>
                                    @endif
                                </td>
                                <td class="valor_numerico">{{ $tipo->valor_numerico ?? '-' }}</td>
                                <td class="orden">{{ $tipo->orden }}</td>
                                <td class="estado">
                                    @if($tipo->activo)
                                        <span class="badge badge-activo">Activo</span>
                                    @else
                                        <span class="badge badge-inactivo">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editTipoNota({{ $tipo->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $tipo->activo ? 'secondary' : 'success' }}" onclick="toggleTipoNota({{ $tipo->id }})">
                                        <i class="fas fa-{{ $tipo->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTipoNota({{ $tipo->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ==================== TAB ASIGNACIÓN POR MÓDULO ==================== -->
            <div class="tab-pane fade" id="asignacion" role="tabpanel">
                <div class="row">
                    <div class="col-md-4">
                        <label for="modulo_select" class="form-label">Seleccionar Módulo</label>
                        <select class="form-select" id="modulo_select">
                            <option value="">Seleccionar módulo</option>
                            @foreach($modulos as $modulo)
                                <option value="{{ $modulo->id }}" data-codigo="{{ $modulo->codigo }}">
                                    {{ $modulo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Tipos de Nota Asignados</label>
                        <div id="tiposNotasAsignados" class="border rounded p-3" style="min-height: 200px;">
                            <p class="text-muted text-center">Seleccione un módulo para ver sus tipos de nota asignados</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary" id="btnGuardarAsignacion" style="display: none;">
                            <i class="fas fa-save me-2"></i>Guardar Asignación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tipo de Nota -->
<div class="modal fade" id="modalTipoNota" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTipoNotaTitle">Nuevo Tipo de Nota</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipoNota">
                <div class="modal-body">
                    <input type="hidden" id="tipo_nota_id" name="tipo_nota_id">
                    
                    <div class="mb-3">
                        <label for="codigo" class="form-label required-field">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" maxlength="10" required>
                        <small class="text-muted">Ej: AD, A, B, C, EXO, 20, 19, etc.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label required-field">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_dato" class="form-label required-field">Tipo de Dato</label>
                            <select class="form-select" id="tipo_dato" name="tipo_dato" required>
                                <option value="LITERAL">Literal</option>
                                <option value="NUMERICO">Numérico</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valor_numerico" class="form-label">Valor Numérico (para ordenamiento)</label>
                            <input type="number" step="0.01" class="form-control" id="valor_numerico" name="valor_numerico">
                            <small class="text-muted">Solo para referencia en reportes</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveTipoNota">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Asignación -->
<div class="modal fade" id="modalAsignacion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Asignar Tipos de Nota</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="asignacionLista"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarAsignacionModal">
                    <i class="fas fa-save me-2"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {

    let currentModuloId = null;
    let currentModuloCodigo = null;
    let tiposNotasDisponibles = [];
    // Opciones de notas - valor por defecto mientras se cargan
    let opcionesNotasConf = ['AD', 'A', 'B', 'C', 'CND', 'EXO'];

        function cargarOpcionesNotas() {
        // Ya no declaras opcionesNotas aquí, solo la modificas
        $.ajax({
            url: '{{ route("admin.notas.opciones") }}',
            method: 'GET',
            async: false,
            success: function(response) {
                if (response && response.length > 0) {
                    opcionesNotasConf = response; // ← ahora modifica la del scope externo
                }
            },
            error: function(xhr) {
                console.error('Error al cargar opciones de notas:', xhr);
            }
        });
    }

    cargarOpcionesNotas();
    // ========== FUNCIONES PARA TIPOS DE NOTAS ==========
    window.openTipoNotaModal = function(id = null) {
        $('#modalTipoNotaTitle').text(id ? 'Editar Tipo de Nota' : 'Nuevo Tipo de Nota');
        $('#formTipoNota')[0].reset();
        $('#tipo_nota_id').val('');
        $('#activo').prop('checked', true);
        $('.is-invalid').removeClass('is-invalid');
        
        if (id) {
            $.get('/admin/configuracion-notas/tipo-nota/' + id, function(response) {
                $('#tipo_nota_id').val(response.id);
                $('#codigo').val(response.codigo);
                $('#nombre').val(response.nombre);
                $('#descripcion').val(response.descripcion);
                $('#tipo_dato').val(response.tipo_dato);
                $('#valor_numerico').val(response.valor_numerico);
                $('#orden').val(response.orden);
                $('#activo').prop('checked', response.activo);
            });
        }
        
        $('#modalTipoNota').modal('show');
    };
    
    window.editTipoNota = function(id) {
        openTipoNotaModal(id);
    };
    
    window.toggleTipoNota = function(id) {
        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'Esta acción cambiará el estado del tipo de nota',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/configuracion-notas/tipo-nota/' + id + '/toggle',
                    method: 'PATCH',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            toast.success(response.message);
                            // Actualizar la fila sin recargar
                            let row = $(`tr[data-id="${id}"]`);
                            let estadoTd = row.find('.estado');
                            let botonesTd = row.find('td:last');
                            
                            if (response.activo) {
                                estadoTd.html('<span class="badge badge-activo">Activo</span>');
                                botonesTd.find('.btn-secondary').removeClass('btn-secondary').addClass('btn-secondary');
                                botonesTd.find('.btn-secondary i').removeClass('fa-check').addClass('fa-ban');
                                botonesTd.find('.btn-secondary').attr('onclick', `toggleTipoNota(${id})`);
                            } else {
                                estadoTd.html('<span class="badge badge-inactivo">Inactivo</span>');
                                botonesTd.find('.btn-secondary').removeClass('btn-secondary').addClass('btn-success');
                                botonesTd.find('.btn-success i').removeClass('fa-ban').addClass('fa-check');
                                botonesTd.find('.btn-success').attr('onclick', `toggleTipoNota(${id})`);
                            }
                        }
                    },
                    error: function() {
                        toast.error('Error al cambiar estado');
                    }
                });
            }
        });
    };
    
    $('#formTipoNota').on('submit', function(e) {
        e.preventDefault();
        let id = $('#tipo_nota_id').val();
        let url = id ? '/admin/configuracion-notas/tipo-nota/' + id : '{{ route("admin.configuracion-notas.tipo-nota.store") }}';
        let method = id ? 'PUT' : 'POST';
        
        let submitBtn = $('#btnSaveTipoNota');
        let originalHtml = submitBtn.html();
        
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span> Guardando...');
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    $('#modalTipoNota').modal('hide');
                    toast.success(response.message);
                    
                    if (id) {
                        // Actualizar la fila existente
                        let row = $(`tr[data-id="${id}"]`);
                        if (row.length) {
                            row.find('.codigo').html(`<strong>${response.tipo_nota?.codigo || $('#codigo').val()}</strong>`);
                            row.find('.nombre').html($('#nombre').val());
                            row.find('.tipo_dato').html($('#tipo_dato').val() === 'NUMERICO' ? '<span class="badge badge-numerico">Numérico</span>' : '<span class="badge badge-literal">Literal</span>');
                            row.find('.valor_numerico').html($('#valor_numerico').val() || '-');
                            row.find('.orden').html($('#orden').val());
                            row.find('.estado').html($('#activo').is(':checked') ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>');
                        } else {
                            location.reload();
                        }
                    } else {
                        // Agregar nueva fila
                        agregarFilaTabla(response.tipo_nota);
                    }
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    for (let field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        if ($(`#${field}`).next('.invalid-feedback').length === 0) {
                            $(`#${field}`).after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        } else {
                            $(`#${field}`).next('.invalid-feedback').text(errors[field][0]);
                        }
                    }
                } else {
                    toast.error(xhr.responseJSON?.message || 'Error al guardar');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html(originalHtml);
            }
        });
    });
    
    function agregarFilaTabla(tipoNota) {
        let newRow = `
            <tr data-id="${tipoNota.id}">
                <td class="codigo"><strong>${tipoNota.codigo}</strong></td>
                <td class="nombre">${tipoNota.nombre}</small></td>
                <td class="tipo_dato">
                    ${tipoNota.tipo_dato === 'NUMERICO' ? '<span class="badge badge-numerico">Numérico</span>' : '<span class="badge badge-literal">Literal</span>'}
                </td>
                <td class="valor_numerico">${tipoNota.valor_numerico || '-'}</td>
                <td class="orden">${tipoNota.orden}</td>
                <td class="estado">
                    ${tipoNota.activo ? '<span class="badge badge-activo">Activo</span>' : '<span class="badge badge-inactivo">Inactivo</span>'}
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editTipoNota(${tipoNota.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-${tipoNota.activo ? 'secondary' : 'success'}" onclick="toggleTipoNota(${tipoNota.id})">
                        <i class="fas fa-${tipoNota.activo ? 'ban' : 'check'}"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTipoNota(${tipoNota.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tablaTiposNotas tbody').append(newRow);
    }

    window.deleteTipoNota = function(id) {
        Swal.fire({
            title: '¿Eliminar tipo de nota?',
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
                    url: '/admin/configuracion-notas/tipo-nota/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            toast.success(response.message);
                            $(`tr[data-id="${id}"]`).fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    },
                    error: function() {
                        toast.error('Error al eliminar');
                    }
                });
            }
        });
    };

    // ========== FUNCIONES PARA ASIGNACIÓN ==========
    $('#modulo_select').on('change', function() {
        currentModuloId = $(this).val();
        currentModuloCodigo = $(this).find('option:selected').data('codigo');
        
        if (currentModuloId) {
            $.ajax({
                url: '{{ route("admin.configuracion-notas.tipos-by-modulo") }}',
                method: 'GET',
                data: { modulo_codigo: currentModuloCodigo },
                success: function(response) {
                    if (response.tipos_notas && response.tipos_notas.length > 0) {
                        let html = '<div class="row">';
                        for (let tipo of response.tipos_notas) {
                            html += `
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${tipo.id}" id="tipo_${tipo.id}" checked>
                                        <label class="form-check-label" for="tipo_${tipo.id}">
                                            <strong>${tipo.codigo}</strong> - ${tipo.nombre}
                                            <span class="badge ${tipo.tipo_dato === 'NUMERICO' ? 'badge-numerico' : 'badge-literal'} ms-1">${tipo.tipo_dato === 'NUMERICO' ? 'Numérico' : 'Literal'}</span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        }
                        html += '</div>';
                        $('#tiposNotasAsignados').html(html);
                        $('#btnGuardarAsignacion').show();
                    } else {
                        $('#tiposNotasAsignados').html('<p class="text-muted text-center">No hay tipos de nota asignados a este módulo</p>');
                        $('#btnGuardarAsignacion').show();
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    $('#tiposNotasAsignados').html('<p class="text-muted text-center">Error al cargar los tipos de nota</p>');
                }
            });
        } else {
            $('#tiposNotasAsignados').html('<p class="text-muted text-center">Seleccione un módulo para ver sus tipos de nota asignados</p>');
            $('#btnGuardarAsignacion').hide();
        }
    });
    
    $('#btnGuardarAsignacion').on('click', function() {
        if (!currentModuloId) return;
        
        // Obtener todos los tipos de nota disponibles
        $.ajax({
            url: '{{ route("admin.configuracion-notas.tipos-nota-todos") }}',
            method: 'GET',
            success: function(tipos) {
                // Obtener los tipos ya asignados para marcar como checked
                $.ajax({
                    url: '{{ route("admin.configuracion-notas.tipos-by-modulo") }}',
                    method: 'GET',
                    data: { modulo_codigo: currentModuloCodigo },
                    success: function(asignadosResponse) {
                        let asignadosIds = asignadosResponse.tipos_notas?.map(t => t.id) || [];
                        
                        let html = '<div class="row">';
                        for (let tipo of tipos) {
                            let checked = asignadosIds.includes(tipo.id) ? 'checked' : '';
                            html += `
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${tipo.id}" id="asign_tipo_${tipo.id}" ${checked}>
                                        <label class="form-check-label" for="asign_tipo_${tipo.id}">
                                            <strong>${tipo.codigo}</strong> - ${tipo.nombre}
                                            <span class="badge ${tipo.tipo_dato === 'NUMERICO' ? 'badge-numerico' : 'badge-literal'} ms-1">${tipo.tipo_dato === 'NUMERICO' ? 'Numérico' : 'Literal'}</span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        }
                        html += '</div>';
                        $('#asignacionLista').html(html);
                        $('#modalAsignacion').modal('show');
                    }
                });
            },
            error: function() {
                toast.error('Error al cargar los tipos de nota');
            }
        });
    });
    
    $('#btnGuardarAsignacionModal').on('click', function() {
        let selectedTipos = [];
        $('input[id^="asign_tipo_"]:checked').each(function() {
            selectedTipos.push($(this).val());
        });
        
        $.ajax({
            url: '{{ route("admin.configuracion-notas.asignar") }}',
            method: 'POST',
            data: {
                modulo_id: currentModuloId,
                tipos_notas: selectedTipos,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toast.success(response.message);
                    $('#modalAsignacion').modal('hide');
                    // Recargar la vista de asignación
                    $('#modulo_select').trigger('change');
                }
            },
            error: function(xhr) {
                console.error(xhr);
                toast.error(xhr.responseJSON?.message || 'Error al guardar la asignación');
            }
        });
    });


});
</script>
@endsection