@extends('layouts.app')

@section('title', 'Configuración del Sistema')

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cuadros-tab" data-bs-toggle="tab" data-bs-target="#cuadros" type="button" role="tab">
                    <i class="fas fa-th-large me-2"></i>Cuadros de Libreta
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="caracteres-tab" data-bs-toggle="tab" data-bs-target="#caracteres" type="button" role="tab">
                    <i class="fas fa-keyboard me-2"></i>Límite de Caracteres
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="competencias-transversales-tab" data-bs-toggle="tab" data-bs-target="#competencias-transversales" type="button" role="tab">
                    <i class="fas fa-users-cog me-2"></i>Asignar Comp. Transversales
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
                            <label for="firma_tutor" class="form-label">Firma del Subdirector 1</label>
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
                            <label for="nombre_tutor" class="form-label">Nombre del Subdirector 1</label>
                            <input type="text" class="form-control" id="nombre_tutor" name="nombre_tutor" value="{{ old('nombre_tutor', $configLibreta->nombre_tutor) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cargo_tutor" class="form-label">Cargo del subdirector 1</label>
                            <input type="text" class="form-control" id="cargo_tutor" name="cargo_tutor" value="{{ old('cargo_tutor', $configLibreta->cargo_tutor) }}">
                        </div>
                    </div>
                    
                    <!-- Firma del Subdirector -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firma_subdirector" class="form-label">Firma del Subdirector 2</label>
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
                            <label for="nombre_subdirector" class="form-label">Nombre del Subdirector 2</label>
                            <input type="text" class="form-control" id="nombre_subdirector" name="nombre_subdirector" 
                                value="{{ old('nombre_subdirector', $configLibreta->nombre_subdirector) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cargo_subdirector" class="form-label">Cargo del Subdirector 2</label>
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

            <!-- ==================== TAB CUADROS DE LIBRETA ==================== -->
            <div class="tab-pane fade" id="cuadros" role="tabpanel">
                <form method="POST" action="{{ route('admin.configuracion.save-libreta-cuadros') }}">
                    @csrf

                    @php
                        $availableCuadros = [
                            'cursos_competencias' => 'Cursos y Competencias',
                            'competencias_transversales' => 'Competencias Transversales',
                            'apreciaciones_tutor' => 'Apreciaciones del Tutor',
                            'evaluacion_padre' => 'Evaluación al Padre de Familia',
                            'evaluaciones_actitudinales' => 'Evaluaciones Actitudinales',
                            'inasistencias' => 'Inasistencias',
                            'otras_evaluaciones' => 'Comportamiento y Otras Evaluaciones',
                            'orden_merito' => 'Orden de Mérito',
                            'cuadros_dinamicos' => 'Cuadros Dinámicos',
                        ];
                    @endphp

                    <div class="row">
                        <div class="col-12 mb-3">
                            <p class="text-muted">Seleccione qué cuadros se deben mostrar en la previsualización de la libreta por cada nivel.</p>
                        </div>
                    </div>

                    @foreach($niveles as $nivel)
                        @php
                            $selected = $cuadrosPorNivel[$nivel->id] ?? null;
                        @endphp
                        <form method="POST" action="{{ route('admin.configuracion.save-libreta-cuadros') }}">
                            @csrf
                            <input type="hidden" name="nivel_id" value="{{ $nivel->id }}">
                            <div class="config-card mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">{{ $nivel->nombre }}</h5>
                                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        @foreach($availableCuadros as $key => $label)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="cuadros[]" id="{{ $nivel->id }}_{{ $key }}" value="{{ $key }}" {{ is_array($selected) && in_array($key, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $nivel->id }}_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </form>
            </div>

            <!-- ==================== TAB CARACTERES ==================== -->
            <div class="tab-pane fade" id="caracteres" role="tabpanel">
                <form method="POST" action="{{ route('admin.configuracion.update-caracteres') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure la cantidad máxima de caracteres permitidos para las conclusiones descriptivas y apreciaciones en el sistema.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="conclusiones_caracteres_max" class="form-label required-field">Caracteres máx. en Conclusiones Descriptivas (Notas)</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('conclusiones_caracteres_max') is-invalid @enderror" 
                                       id="conclusiones_caracteres_max" name="conclusiones_caracteres_max" 
                                       value="{{ old('conclusiones_caracteres_max', $caracteresConfig['conclusiones_caracteres_max']) }}" 
                                       min="100" max="5000" required>
                                <span class="input-group-text">caracteres</span>
                                @error('conclusiones_caracteres_max')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-2">Rango: 100 - 5000 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="competencias_transversales_caracteres_max" class="form-label required-field">Caracteres máx. en Competencias Transversales</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('competencias_transversales_caracteres_max') is-invalid @enderror" 
                                       id="competencias_transversales_caracteres_max" name="competencias_transversales_caracteres_max" 
                                       value="{{ old('competencias_transversales_caracteres_max', $caracteresConfig['competencias_transversales_caracteres_max']) }}" 
                                       min="100" max="5000" required>
                                <span class="input-group-text">caracteres</span>
                                @error('competencias_transversales_caracteres_max')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-2">Rango: 100 - 5000 caracteres</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="apreciaciones_caracteres_max" class="form-label required-field">Caracteres máx. en Apreciaciones del Tutor</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('apreciaciones_caracteres_max') is-invalid @enderror" 
                                       id="apreciaciones_caracteres_max" name="apreciaciones_caracteres_max" 
                                       value="{{ old('apreciaciones_caracteres_max', $caracteresConfig['apreciaciones_caracteres_max']) }}" 
                                       min="100" max="5000" required>
                                <span class="input-group-text">caracteres</span>
                                @error('apreciaciones_caracteres_max')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted d-block mt-2">Rango: 100 - 5000 caracteres</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Nota:</strong> Estos límites se aplicarán inmediatamente en los formularios de registro y mostrará un contador de caracteres disponibles.
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ==================== TAB ASIGNAR COMPETENCIAS TRANSVERSALES ==================== -->
            <div class="tab-pane fade" id="competencias-transversales" role="tabpanel">
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="text-muted">Configure qué profesores pueden ver cada competencia transversal en el Registro de Competencias Transversales. Los administradores ven todas las competencias sin restricciones.</p>
                    </div>
                </div>

                @foreach($competenciasTransversales->groupBy('nivel.nombre') as $nivelNombre => $competencias)
                    <div class="config-card mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-layer-group me-2"></i>
                            {{ $nivelNombre }}
                        </h5>

                        @foreach($competencias as $competencia)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $competencia->nombre }}</strong>
                                        @if($competencia->descripcion)
                                            <br><small class="text-muted">{{ $competencia->descripcion }}</small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editarAsignacion(this, {{ $competencia->id }}, '{{ addslashes($competencia->nombre) }}')"
                                            data-asignados="{{ $competencia->usuariosAsignados->pluck('id')->join(',') }}">
                                        <i class="fas fa-edit me-1"></i> Asignar Profesores
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted">Profesores asignados:</small>
                                        <div class="mt-1">
                                            @if($competencia->usuariosAsignados->count() > 0)
                                                @foreach($competencia->usuariosAsignados as $usuario)
                                                    <span class="badge bg-success text-white me-1 mb-1">
                                                        {{ $usuario->name }}
                                                        <a href="javascript:void(0)" class="text-white ms-2" title="Eliminar profesor" onclick="eliminarAsignacionProfesor({{ $competencia->id }}, {{ $usuario->id }}, '{{ addslashes($usuario->name) }}')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Ningún profesor asignado (solo administradores verán esta competencia)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar Profesores -->
<div class="modal fade" id="modalAsignarProfesores" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog me-2"></i>
                    Asignar Profesores - <span id="competenciaNombre"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAsignarProfesores">
                    @csrf
                    <input type="hidden" id="competencia_id" name="competencia_id">
                    
                    <div class="mb-3">
                        <label for="selectProfesores" class="form-label">Seleccionar Profesores</label>
                        <select class="form-select" id="selectProfesores" name="profesores[]" multiple="multiple" style="width: 100%;">
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->id }}">
                                    {{ $profesor->name }}
                                    @if($profesor->docente)
                                        - {{ $profesor->docente->codigo }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Los profesores seleccionados podrán ver esta competencia en el Registro de Competencias Transversales.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarAsignacion">
                    <i class="fas fa-save me-2"></i> Guardar Asignación
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

// ==================== ASIGNAR COMPETENCIAS TRANSVERSALES ====================

function editarAsignacion(button, competenciaId, competenciaNombre) {
    // Establecer valores en el formulario
    $('#competencia_id').val(competenciaId);
    $('#competenciaNombre').text(competenciaNombre);

    // Cargar profesores asignados si los hay
    let asignados = $(button).data('asignados') || '';
    let selectedProfesores = [];
    if (asignados) {
        selectedProfesores = asignados.toString().split(',').filter(function(item) {
            return item !== '';
        });
    }
    $('#selectProfesores').val(selectedProfesores).trigger('change');

    // Mostrar el modal
    let modal = new bootstrap.Modal(document.getElementById('modalAsignarProfesores'));
    modal.show();
}

function eliminarAsignacionProfesor(competenciaId, userId, userName) {
    Swal.fire({
        title: 'Eliminar profesor asignado',
        text: `¿Deseas quitar a ${userName} de esta competencia transversal?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.configuracion.eliminar-asignacion-competencia-transversal") }}',
                method: 'POST',
                data: {
                    competencia_id: competenciaId,
                    user_id: userId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success').then(function() {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Error al eliminar la asignación';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
}

$(document).ready(function() {
    // Inicializar Select2 para el modal
    $('#selectProfesores').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar y seleccionar profesores...',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron profesores";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });
    
    // Guardar asignación de profesores
    $('#btnGuardarAsignacion').on('click', function() {
        let competenciaId = $('#competencia_id').val();
        let profesoresSeleccionados = $('#selectProfesores').val() || [];
        
        if (competenciaId && profesoresSeleccionados.length >= 0) {
            $.ajax({
                url: '{{ route("admin.configuracion.asignar-competencias-transversales") }}',
                method: 'POST',
                data: {
                    competencia_id: competenciaId,
                    profesores: profesoresSeleccionados,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(function() {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Error al guardar la asignación';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});
</script>
@endsection