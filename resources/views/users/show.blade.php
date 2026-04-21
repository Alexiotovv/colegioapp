{{-- resources/views/users/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('css')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    .detail-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .avatar-circle {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .avatar-circle i {
        font-size: 50px;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-user-circle me-2"></i>
            Detalle de Usuario
        </h4>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i> Editar
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="detail-card">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
                <h5 class="mt-2">{{ $user->name }}</h5>
                <span class="badge bg-{{ $user->role->nombre == 'admin' ? 'danger' : 'info' }}">
                    {{ ucfirst($user->role->nombre) }}
                </span>
            </div>
            
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-label">Nombre de Usuario</div>
                        <div class="detail-value">{{ $user->username }}</div>
                        
                        <div class="detail-label">Correo Electrónico</div>
                        <div class="detail-value">{{ $user->email }}</div>
                        
                        <div class="detail-label">Estado</div>
                        <div class="detail-value">
                            @if($user->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="detail-label">Rol</div>
                        <div class="detail-value">{{ ucfirst($user->role->nombre) }}</div>
                        
                        <div class="detail-label">Último Acceso</div>
                        <div class="detail-value">{{ $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i:s') : 'Nunca' }}</div>
                        
                        <div class="detail-label">Fecha de Registro</div>
                        <div class="detail-value">{{ $user->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection