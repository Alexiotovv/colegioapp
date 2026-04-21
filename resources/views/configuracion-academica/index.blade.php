@extends('layouts.app')

@section('title', 'Configuración Académica')

@section('css')
<style>
    .tab-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
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
        overflow: hidden;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-action {
        padding: 4px 8px;
        margin: 0 2px;
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .modal-header {
        background: var(--primary-color);
        color: white;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
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
            <i class="fas fa-sliders-h me-2" style="color: var(--primary-color);"></i>
            Configuración Académica
        </h4>
    </div>
    
    <div class="tab-container">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="niveles-tab" data-bs-toggle="tab" data-bs-target="#niveles" type="button" role="tab">
                    <i class="fas fa-layer-group me-2"></i>Niveles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="grados-tab" data-bs-toggle="tab" data-bs-target="#grados" type="button" role="tab">
                    <i class="fas fa-chalkboard me-2"></i>Grados
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="secciones-tab" data-bs-toggle="tab" data-bs-target="#secciones" type="button" role="tab">
                    <i class="fas fa-users-viewfinder me-2"></i>Secciones
                </button>
            </li>
        </ul>
        
        <!-- Tab content -->
        <div class="tab-content" id="configTabsContent">
            
            <!-- ==================== TAB NIVELES ==================== -->
            <div class="tab-pane fade show active" id="niveles" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" onclick="openNivelModal()">
                        <i class="fas fa-plus me-2"></i>Nuevo Nivel
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaNiveles">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Grados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="nivelesBody">
                            @foreach($niveles as $nivel)
                            <tr data-id="{{ $nivel->id }}">
                                <td>{{ $nivel->id }}</td>
                                <td><strong>{{ $nivel->nombre }}</strong></td>
                                <td>{{ $nivel->descripcion ?? '—' }}</td>
                                <td>{{ $nivel->orden }}</td>
                                <td>
                                    @if($nivel->activo)
                                        <span class="status-badge bg-success text-white">Activo</span>
                                    @else
                                        <span class="status-badge bg-secondary text-white">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $nivel->grados->count() }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action" onclick="editNivel({{ $nivel->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $nivel->activo ? 'secondary' : 'success' }} btn-action" onclick="toggleNivel({{ $nivel->id }})" title="{{ $nivel->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $nivel->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteNivel({{ $nivel->id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ==================== TAB GRADOS ==================== -->
            <div class="tab-pane fade" id="grados" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" onclick="openGradoModal()">
                        <i class="fas fa-plus me-2"></i>Nuevo Grado
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaGrados">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="gradosBody">
                            @foreach($grados as $grado)
                            <tr data-id="{{ $grado->id }}">
                                <td>{{ $grado->id }}</td>
                                <td>{{ $grado->nivel->nombre ?? 'N/A' }}</td>
                                <td><strong>{{ $grado->nombre }}</strong></td>
                                <td>{{ $grado->orden }}</td>
                                <td>
                                    @if($grado->activo)
                                        <span class="status-badge bg-success text-white">Activo</span>
                                    @else
                                        <span class="status-badge bg-secondary text-white">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action" onclick="editGrado({{ $grado->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $grado->activo ? 'secondary' : 'success' }} btn-action" onclick="toggleGrado({{ $grado->id }})" title="{{ $grado->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $grado->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteGrado({{ $grado->id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ==================== TAB SECCIONES ==================== -->
            <div class="tab-pane fade" id="secciones" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" onclick="openSeccionModal()">
                        <i class="fas fa-plus me-2"></i>Nueva Sección
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaSecciones">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sección</th>
                                <th>Turno</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="seccionesBody">
                            @foreach($secciones as $seccion)
                            <tr data-id="{{ $seccion->id }}">
                                <td>{{ $seccion->id }}</td>
                                <td><strong>{{ $seccion->nombre }}</strong></td>
                                <td>{{ $seccion->turno }}</td>
                                <td>
                                    @if($seccion->activo)
                                        <span class="status-badge bg-success text-white">Activo</span>
                                    @else
                                        <span class="status-badge bg-secondary text-white">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action" onclick="editSeccion({{ $seccion->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-{{ $seccion->activo ? 'secondary' : 'success' }} btn-action" onclick="toggleSeccion({{ $seccion->id }})" title="{{ $seccion->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas fa-{{ $seccion->activo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteSeccion({{ $seccion->id }})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL NIVEL ==================== -->
<div class="modal fade" id="modalNivel" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNivelTitle">Nuevo Nivel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNivel">
                    <input type="hidden" id="nivel_id" name="nivel_id">
                    <div class="mb-3">
                        <label for="nivel_nombre" class="form-label required-field">Nombre</label>
                        <input type="text" class="form-control" id="nivel_nombre" name="nombre" required>
                        <div class="invalid-feedback" id="nivel_nombre_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="nivel_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="nivel_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nivel_orden" class="form-label">Orden</label>
                        <input type="number" class="form-control" id="nivel_orden" name="orden" value="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="nivel_activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="nivel_activo">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveNivel">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL GRADO ==================== -->
<div class="modal fade" id="modalGrado" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGradoTitle">Nuevo Grado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formGrado">
                    <input type="hidden" id="grado_id" name="grado_id">
                    <div class="mb-3">
                        <label for="grado_nivel_id" class="form-label required-field">Nivel</label>
                        <select class="form-select" id="grado_nivel_id" name="nivel_id" required>
                            <option value="">Seleccionar nivel</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="grado_nivel_id_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="grado_nombre" class="form-label required-field">Grado</label>
                        <input type="text" class="form-control" id="grado_nombre" name="nombre" placeholder="Ej: 1ro, 2do, 3ro" required>
                        <div class="invalid-feedback" id="grado_nombre_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="grado_orden" class="form-label">Orden</label>
                        <input type="number" class="form-control" id="grado_orden" name="orden" value="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="grado_activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="grado_activo">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveGrado">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL SECCION ==================== -->
<div class="modal fade" id="modalSeccion" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSeccionTitle">Nueva Sección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSeccion">
                    <input type="hidden" id="seccion_id" name="seccion_id">
                    <div class="mb-3">
                        <label for="seccion_nombre" class="form-label required-field">Sección</label>
                        <input type="text" class="form-control" id="seccion_nombre" name="nombre" placeholder="Ej: A, B, C" maxlength="2" required>
                        <div class="invalid-feedback" id="seccion_nombre_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="seccion_turno" class="form-label required-field">Turno</label>
                        <select class="form-select" id="seccion_turno" name="turno" required>
                            <option value="">Seleccionar turno</option>
                            <option value="MAÑANA">Mañana</option>
                            <option value="TARDE">Tarde</option>
                            <option value="NOCHE">Noche</option>
                        </select>
                        <div class="invalid-feedback" id="seccion_turno_error"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="seccion_activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="seccion_activo">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveSeccion">Guardar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let nivelesData = @json($niveles);
let gradosData = @json($grados);
let seccionesData = @json($secciones);

$(document).ready(function() {
    // Inicializar tooltips
    $('[title]').tooltip();
});

// ==================== FUNCIONES NIVELES ====================
function openNivelModal(id = null) {
    $('#modalNivelTitle').text(id ? 'Editar Nivel' : 'Nuevo Nivel');
    $('#formNivel')[0].reset();
    $('#nivel_id').val('');
    $('.invalid-feedback').text('');
    $('.form-control, .form-select').removeClass('is-invalid');
    $('#nivel_activo').prop('checked', true);
    
    if (id) {
        $.get(`/admin/configuracion/niveles/${id}`, function(data) {
            $('#nivel_id').val(data.id);
            $('#nivel_nombre').val(data.nombre);
            $('#nivel_descripcion').val(data.descripcion);
            $('#nivel_orden').val(data.orden);
            $('#nivel_activo').prop('checked', data.activo);
        });
    }
    
    $('#modalNivel').modal('show');
}

function editNivel(id) {
    openNivelModal(id);
}

function toggleNivel(id) {
    $.post(`/admin/configuracion/niveles/${id}/toggle`, {
        _token: '{{ csrf_token() }}',
        _method: 'PATCH'
    }, function(response) {
        if (response.success) {
            Swal.fire('Éxito', response.message, 'success');
            location.reload();
        }
    }).fail(function(xhr) {
        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
    });
}

function deleteNivel(id) {
    Swal.fire({
        title: '¿Eliminar nivel?',
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
                url: `/admin/configuracion/niveles/${id}`,
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
}

// ==================== FUNCIONES GRADOS ====================
function openGradoModal(id = null) {
    $('#modalGradoTitle').text(id ? 'Editar Grado' : 'Nuevo Grado');
    $('#formGrado')[0].reset();
    $('#grado_id').val('');
    $('.invalid-feedback').text('');
    $('.form-control, .form-select').removeClass('is-invalid');
    $('#grado_activo').prop('checked', true);
    
    if (id) {
        $.get(`/admin/configuracion/grados/${id}`, function(data) {
            $('#grado_id').val(data.id);
            $('#grado_nivel_id').val(data.nivel_id);
            $('#grado_nombre').val(data.nombre);
            $('#grado_orden').val(data.orden);
            $('#grado_activo').prop('checked', data.activo);
        });
    }
    
    $('#modalGrado').modal('show');
}

function editGrado(id) {
    openGradoModal(id);
}

function toggleGrado(id) {
    $.post(`/admin/configuracion/grados/${id}/toggle`, {
        _token: '{{ csrf_token() }}',
        _method: 'PATCH'
    }, function(response) {
        if (response.success) {
            Swal.fire('Éxito', response.message, 'success');
            location.reload();
        }
    }).fail(function(xhr) {
        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
    });
}

function deleteGrado(id) {
    Swal.fire({
        title: '¿Eliminar grado?',
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
                url: `/admin/configuracion/grados/${id}`,
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
}

// ==================== FUNCIONES SECCIONES ====================
function openSeccionModal(id = null) {
    $('#modalSeccionTitle').text(id ? 'Editar Sección' : 'Nueva Sección');
    $('#formSeccion')[0].reset();
    $('#seccion_id').val('');
    $('.invalid-feedback').text('');
    $('.form-control, .form-select').removeClass('is-invalid');
    $('#seccion_activo').prop('checked', true);
    
    if (id) {
        $.get(`/admin/configuracion/secciones/${id}`, function(data) {
            $('#seccion_id').val(data.id);
            $('#seccion_nombre').val(data.nombre);
            $('#seccion_turno').val(data.turno);
            $('#seccion_activo').prop('checked', data.activo);
        });
    }
    
    $('#modalSeccion').modal('show');
}

function editSeccion(id) {
    openSeccionModal(id);
}

function toggleSeccion(id) {
    $.post(`/admin/configuracion/secciones/${id}/toggle`, {
        _token: '{{ csrf_token() }}',
        _method: 'PATCH'
    }, function(response) {
        if (response.success) {
            Swal.fire('Éxito', response.message, 'success');
            location.reload();
        }
    }).fail(function(xhr) {
        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar estado', 'error');
    });
}

function deleteSeccion(id) {
    Swal.fire({
        title: '¿Eliminar sección?',
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
                url: `/admin/configuracion/secciones/${id}`,
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
}

// ==================== ENVÍO DE FORMULARIOS ====================

// Guardar Nivel
$('#btnSaveNivel').on('click', function() {
    let id = $('#nivel_id').val();
    let url = id ? `/admin/configuracion/niveles/${id}` : '/admin/configuracion/niveles';
    let method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: $('#formNivel').serialize() + '&_token={{ csrf_token() }}',
        success: function(response) {
            if (response.success) {
                $('#modalNivel').modal('hide');
                Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) {
                for (let field in errors) {
                    $(`#nivel_${field}_error`).text(errors[field][0]);
                    $(`#nivel_${field}`).addClass('is-invalid');
                }
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        }
    });
});

// Guardar Grado
$('#btnSaveGrado').on('click', function() {
    let id = $('#grado_id').val();
    let url = id ? `/admin/configuracion/grados/${id}` : '/admin/configuracion/grados';
    let method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: $('#formGrado').serialize() + '&_token={{ csrf_token() }}',
        success: function(response) {
            if (response.success) {
                $('#modalGrado').modal('hide');
                Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) {
                for (let field in errors) {
                    $(`#grado_${field}_error`).text(errors[field][0]);
                    $(`#grado_${field}`).addClass('is-invalid');
                }
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        }
    });
});

// Guardar Sección
$('#btnSaveSeccion').on('click', function() {
    let id = $('#seccion_id').val();
    let url = id ? `/admin/configuracion/secciones/${id}` : '/admin/configuracion/secciones';
    let method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: $('#formSeccion').serialize() + '&_token={{ csrf_token() }}',
        success: function(response) {
            if (response.success) {
                $('#modalSeccion').modal('hide');
                Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) {
                for (let field in errors) {
                    $(`#seccion_${field}_error`).text(errors[field][0]);
                    $(`#seccion_${field}`).addClass('is-invalid');
                }
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        }
    });
});
</script>
@endsection