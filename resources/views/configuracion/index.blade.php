@extends('layouts.app')

@section('title', 'Configuración del Sistema')

@section('css')
<style>
    .config-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 25px;
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
    
    .image-preview {
        width: 150px;
        height: 100px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
    }
    
    .image-preview-small {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
    }
    
    .preview-container {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }
    
    .delete-image {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .delete-image:hover {
        background: #c82333;
        transform: scale(1.1);
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
            <i class="fas fa-cog me-2" style="color: var(--primary-color);"></i>
            Configuración del Sistema
        </h4>
    </div>
    
    <div class="config-card">
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="institucion-tab" data-bs-toggle="tab" data-bs-target="#institucion" type="button" role="tab">
                    <i class="fas fa-building me-2"></i>Institución
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="libreta-tab" data-bs-toggle="tab" data-bs-target="#libreta" type="button" role="tab">
                    <i class="fas fa-address-book me-2"></i>Libreta de Notas
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="configTabsContent">
            
            <!-- ==================== TAB INSTITUCIÓN ==================== -->
            <div class="tab-pane fade show active" id="institucion" role="tabpanel">
                <form method="POST" action="{{ route('admin.configuracion.update-institucion') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label required-field">Nombre de la Institución</label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" name="nombre" value="{{ old('nombre', $configInstitucion->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control @error('ruc') is-invalid @enderror" 
                                   id="ruc" name="ruc" value="{{ old('ruc', $configInstitucion->ruc) }}">
                            @error('ruc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                   id="direccion" name="direccion" value="{{ old('direccion', $configInstitucion->direccion) }}">
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono', $configInstitucion->telefono) }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="telefono2" class="form-label">Teléfono 2</label>
                            <input type="text" class="form-control @error('telefono2') is-invalid @enderror" 
                                   id="telefono2" name="telefono2" value="{{ old('telefono2', $configInstitucion->telefono2) }}">
                            @error('telefono2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $configInstitucion->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="web" class="form-label">Sitio Web</label>
                            <input type="url" class="form-control @error('web') is-invalid @enderror" 
                                   id="web" name="web" value="{{ old('web', $configInstitucion->web) }}">
                            @error('web')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="logo_login" class="form-label">Logo para Login</label>
                            <input type="file" class="form-control" id="logo_login" name="logo_login" accept="image/*">
                            @if($configInstitucion->logo_login)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configInstitucion->logo_login) }}" class="image-preview" alt="Logo Login">
                                    <div class="delete-image" onclick="deleteImage('logo_login', 'institucion')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                            <small class="text-muted">Recomendado: 200x200px</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="logo_dashboard" class="form-label">Logo para Dashboard</label>
                            <input type="file" class="form-control" id="logo_dashboard" name="logo_dashboard" accept="image/*">
                            @if($configInstitucion->logo_dashboard)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configInstitucion->logo_dashboard) }}" class="image-preview" alt="Logo Dashboard">
                                    <div class="delete-image" onclick="deleteImage('logo_dashboard', 'institucion')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                            <small class="text-muted">Recomendado: 150x50px</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="favicon" class="form-label">Favicon</label>
                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                            @if($configInstitucion->favicon)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configInstitucion->favicon) }}" class="image-preview-small" alt="Favicon">
                                    <div class="delete-image" onclick="deleteImage('favicon', 'institucion')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                            <small class="text-muted">Recomendado: 32x32px</small>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- ==================== TAB LIBRETA DE NOTAS ==================== -->
            <div class="tab-pane fade" id="libreta" role="tabpanel">
                <form method="POST" action="{{ route('admin.configuracion.update-libreta') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="titulo" class="form-label">Título de la Libreta</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="{{ old('titulo', $configLibreta->titulo) }}">
                            <small class="text-muted">Ej: Libreta de Notas - Año Escolar 2025</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subtitulo" class="form-label">Subtítulo</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="{{ old('subtitulo', $configLibreta->subtitulo) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dre" class="form-label">DRE (Dirección Regional de Educación)</label>
                            <input type="text" class="form-control" id="dre" name="dre" value="{{ old('dre', $configLibreta->dre) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="ugel" class="form-label">UGEL</label>
                            <input type="text" class="form-control" id="ugel" name="ugel" value="{{ old('ugel', $configLibreta->ugel) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Logos para la Libreta</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="logo_pais">Logo del País</label>
                                    <input type="file" class="form-control" id="logo_pais" name="logo_pais" accept="image/*">
                                    @if($configLibreta->logo_pais)
                                        <div class="preview-container">
                                            <img src="{{ Storage::url($configLibreta->logo_pais) }}" class="image-preview" alt="Logo País">
                                            <div class="delete-image" onclick="deleteLibretaImage('logo_pais')">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label for="logo_region">Logo de la Región</label>
                                    <input type="file" class="form-control" id="logo_region" name="logo_region" accept="image/*">
                                    @if($configLibreta->logo_region)
                                        <div class="preview-container">
                                            <img src="{{ Storage::url($configLibreta->logo_region) }}" class="image-preview" alt="Logo Región">
                                            <div class="delete-image" onclick="deleteLibretaImage('logo_region')">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label for="logo_institucion">Logo de la Institución</label>
                                    <input type="file" class="form-control" id="logo_institucion" name="logo_institucion" accept="image/*">
                                    @if($configLibreta->logo_institucion)
                                        <div class="preview-container">
                                            <img src="{{ Storage::url($configLibreta->logo_institucion) }}" class="image-preview" alt="Logo Institución">
                                            <div class="delete-image" onclick="deleteLibretaImage('logo_institucion')">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firma_director" class="form-label">Firma del Director</label>
                            <input type="file" class="form-control" id="firma_director" name="firma_director" accept="image/*">
                            @if($configLibreta->firma_director)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configLibreta->firma_director) }}" class="image-preview-small" alt="Firma Director">
                                    <div class="delete-image" onclick="deleteLibretaImage('firma_director')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nombre_director" class="form-label">Nombre del Director</label>
                            <input type="text" class="form-control" id="nombre_director" name="nombre_director" value="{{ old('nombre_director', $configLibreta->nombre_director) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cargo_director" class="form-label">Cargo del Director</label>
                            <input type="text" class="form-control" id="cargo_director" name="cargo_director" value="{{ old('cargo_director', $configLibreta->cargo_director) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firma_tutor" class="form-label">Firma del Tutor</label>
                            <input type="file" class="form-control" id="firma_tutor" name="firma_tutor" accept="image/*">
                            @if($configLibreta->firma_tutor)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configLibreta->firma_tutor) }}" class="image-preview-small" alt="Firma Tutor">
                                    <div class="delete-image" onclick="deleteLibretaImage('firma_tutor')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nombre_tutor" class="form-label">Nombre del Tutor</label>
                            <input type="text" class="form-control" id="nombre_tutor" name="nombre_tutor" value="{{ old('nombre_tutor', $configLibreta->nombre_tutor) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cargo_tutor" class="form-label">Cargo del Tutor</label>
                            <input type="text" class="form-control" id="cargo_tutor" name="cargo_tutor" value="{{ old('cargo_tutor', $configLibreta->cargo_tutor) }}">
                        </div>
                    </div>
                    
                    <!-- Firma del Subdirector -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Firma del Subdirector</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firma_subdirector" class="form-label">Firma del Subdirector</label>
                            <input type="file" class="form-control" id="firma_subdirector" name="firma_subdirector" accept="image/*">
                            @if($configLibreta->firma_subdirector)
                                <div class="preview-container">
                                    <img src="{{ Storage::url($configLibreta->firma_subdirector) }}" class="image-preview-small" alt="Firma Subdirector">
                                    <div class="delete-image" onclick="deleteLibretaImage('firma_subdirector')">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nombre_subdirector" class="form-label">Nombre del Subdirector</label>
                            <input type="text" class="form-control" id="nombre_subdirector" name="nombre_subdirector" 
                                value="{{ old('nombre_subdirector', $configLibreta->nombre_subdirector) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cargo_subdirector" class="form-label">Cargo del Subdirector</label>
                            <input type="text" class="form-control" id="cargo_subdirector" name="cargo_subdirector" 
                                value="{{ old('cargo_subdirector', $configLibreta->cargo_subdirector) }}">
                        </div>
                    </div>



                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="texto_pie" class="form-label">Texto al Pie de la Libreta</label>
                            <textarea class="form-control" id="texto_pie" name="texto_pie" rows="3">{{ old('texto_pie', $configLibreta->texto_pie) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="mostrar_en_libreta" id="mostrar_en_libreta" value="1" {{ $configLibreta->mostrar_en_libreta ? 'checked' : '' }}>
                                <label class="form-check-label" for="mostrar_en_libreta">
                                    Mostrar esta configuración en la libreta de notas
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function deleteImage(campo, tipo) {
    Swal.fire({
        title: '¿Eliminar imagen?',
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
                url: '{{ route("admin.configuracion.delete-logo") }}',
                method: 'POST',
                data: {
                    campo: campo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar la imagen', 'error');
                }
            });
        }
    });
}

function deleteLibretaImage(campo) {
    Swal.fire({
        title: '¿Eliminar imagen?',
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
                url: '{{ route("admin.configuracion.delete-libreta-image") }}',
                method: 'POST',
                data: {
                    campo: campo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al eliminar la imagen', 'error');
                }
            });
        }
    });
}
</script>
@endsection