{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('css')
<style>
    .tabs-wrapper {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    .btn-action {
        padding: 5px 10px;
        margin: 0 2px;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .role-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .tab-content-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    .required-field::after {
        content: '*';
        color: red;
        margin-left: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-users me-2"></i>
            Gestión de Usuarios
        </h4>
    </div>

    <div class="tabs-wrapper mb-4">
        <ul class="nav nav-tabs" id="usersTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios-pane" type="button" role="tab">
                    <i class="fas fa-users me-1"></i> Usuarios
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab">
                    <i class="fas fa-user-shield me-1"></i> Roles
                </button>
            </li>
        </ul>

        <div class="tab-content pt-4">
            <div class="tab-pane fade show active" id="usuarios-pane" role="tabpanel">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-md-6 col-lg-5">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Buscar por nombre, correo o usuario"
                                value="{{ $search ?? request('search') }}"
                            >
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(!empty($search ?? request('search')))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-6 col-lg-7 text-md-end">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Nuevo Usuario
                        </a>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <i class="fas fa-user-circle me-1"></i>
                                        {{ $user->username }}
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->role->nombre == 'admin' ? 'danger' : ($user->role->nombre == 'docente' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($user->role->nombre) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->activo)
                                            <span class="status-badge bg-success text-white">
                                                <i class="fas fa-check-circle me-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="status-badge bg-danger text-white">
                                                <i class="fas fa-ban me-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Nunca' }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning btn-action" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($user->id != auth()->id())
                                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-{{ $user->activo ? 'secondary' : 'success' }} btn-action" title="{{ $user->activo ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $user->activo ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" id="deleteForm{{ $user->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete('deleteForm{{ $user->id }}', '¿Eliminar usuario {{ $user->name }}?')" class="btn btn-sm btn-danger btn-action" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay usuarios registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="roles-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i> Gestión de Roles</h5>
                        <small class="text-muted">Crear y actualizar roles </small>
                    </div>
                    <button class="btn btn-primary" id="btnNuevoRol">
                        <i class="fas fa-plus me-2"></i> Nuevo Rol
                    </button>
                </div>

                <div class="tab-content-box">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Usuarios</th>
                                    <th>Estado</th>
                                    <th>Creado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Cargando roles...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="roleForm">
                @csrf
                <input type="hidden" id="role_id" name="role_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role_nombre" class="form-label required-field">Nombre</label>
                            <input type="text" class="form-control" id="role_nombre" name="nombre" maxlength="50" required>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="role_activo">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="role_descripcion" name="descripcion" rows="3" maxlength="200"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarRol">
                        <i class="fas fa-save me-2"></i> Guardar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const rolesDataUrl = '{{ route('admin.users.roles.data') }}';
        const rolesStoreUrl = '{{ route('admin.users.roles.store') }}';
        const rolesBaseUrl = '{{ url('/admin/users/roles') }}';
        const roleModalEl = document.getElementById('roleModal');
        const roleModal = new bootstrap.Modal(roleModalEl);

        function badgeEstado(activo) {
            return activo
                ? '<span class="role-badge bg-success text-white"><i class="fas fa-check-circle"></i> Activo</span>'
                : '<span class="role-badge bg-secondary text-white"><i class="fas fa-ban"></i> Inactivo</span>';
        }

        function loadRoles() {
            $('#rolesTableBody').html('<tr><td colspan="7" class="text-center text-muted">Cargando roles...</td></tr>');

            $.ajax({
                url: rolesDataUrl,
                method: 'GET',
                success: function(response) {
                    const roles = response.roles || [];

                    if (!roles.length) {
                        $('#rolesTableBody').html('<tr><td colspan="7" class="text-center text-muted">No hay roles registrados</td></tr>');
                        return;
                    }

                    let html = '';
                    roles.forEach(role => {
                        html += `
                            <tr data-id="${role.id}">
                                <td>${role.id}</td>
                                <td><strong>${role.nombre}</strong></td>
                                <td>${role.descripcion || '<span class="text-muted">Sin descripción</span>'}</td>
                                <td><span class="badge bg-info">${role.users_count}</span></td>
                                <td class="role-estado">${badgeEstado(role.activo)}</td>
                                <td>${role.created_at || '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action btnEditarRol" data-role="${encodeURIComponent(JSON.stringify(role))}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-${role.activo ? 'secondary' : 'success'} btn-action btnToggleRol" data-id="${role.id}" data-activo="${role.activo ? 1 : 0}" title="${role.activo ? 'Desactivar' : 'Activar'}">
                                        <i class="fas fa-${role.activo ? 'ban' : 'check'}"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#rolesTableBody').html(html);
                },
                error: function() {
                    $('#rolesTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar roles</td></tr>');
                }
            });
        }

        function resetRoleForm() {
            $('#roleForm')[0].reset();
            $('#role_id').val('');
            $('#role_activo').prop('checked', true);
            $('#roleModalTitle').text('Nuevo Rol');
            $('#btnGuardarRol').html('<i class="fas fa-save me-2"></i> Guardar Rol');
        }

        $('#roles-tab').on('shown.bs.tab', function() {
            loadRoles();
        });

        $('#btnNuevoRol').on('click', function() {
            resetRoleForm();
            roleModal.show();
        });

        $(document).on('click', '.btnEditarRol', function() {
            const role = $(this).data('role');
            const parsedRole = JSON.parse(decodeURIComponent(role));

            $('#role_id').val(parsedRole.id);
            $('#role_nombre').val(parsedRole.nombre);
            $('#role_descripcion').val(parsedRole.descripcion || '');
            $('#role_activo').prop('checked', !!parsedRole.activo);
            $('#roleModalTitle').text('Editar Rol');
            $('#btnGuardarRol').html('<i class="fas fa-save me-2"></i> Actualizar Rol');
            roleModal.show();
        });

        $('#roleForm').on('submit', function(e) {
            e.preventDefault();

            const roleId = $('#role_id').val();
            const url = roleId ? `${rolesBaseUrl}/${roleId}` : rolesStoreUrl;
            const method = roleId ? 'PUT' : 'POST';

            $('#btnGuardarRol').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');

            $.ajax({
                url: url,
                method: method,
                data: {
                    nombre: $('#role_nombre').val(),
                    descripcion: $('#role_descripcion').val(),
                    activo: $('#role_activo').is(':checked') ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        roleModal.hide();
                        loadRoles();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'No se pudo guardar el rol';
                    Swal.fire('Error', message, 'error');
                },
                complete: function() {
                    $('#btnGuardarRol').prop('disabled', false);
                    const roleId = $('#role_id').val();
                    $('#btnGuardarRol').html(roleId ? '<i class="fas fa-save me-2"></i> Actualizar Rol' : '<i class="fas fa-save me-2"></i> Guardar Rol');
                }
            });
        });

        $(document).on('click', '.btnToggleRol', function() {
            const roleId = $(this).data('id');

            $.ajax({
                url: `${rolesBaseUrl}/${roleId}/toggle-active`,
                method: 'PATCH',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        loadRoles();
                        Swal.fire('Éxito', response.message, 'success');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo actualizar el estado del rol', 'error');
                }
            });
        });

        loadRoles();
    });
</script>
@endsection