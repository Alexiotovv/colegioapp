@extends('layouts.app')

@section('title', 'Exportar Orden de Mérito')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .export-box {
        background: white;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .helper-text {
        color: #6c757d;
        font-size: 13px;
    }

    .orden-multiple {
        min-height: 220px;
    }

    .orden-multiple option {
        padding: 4px 6px;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 42px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.15rem 0.25rem;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-file-excel me-2" style="color: var(--primary-color);"></i>
            Exportar Orden de Mérito
        </h4>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="export-box">
        <form method="POST" action="{{ route('admin.libretas.orden-merito-reporte.exportar') }}" class="row g-3 align-items-end" id="formOrdenMeritoExport">
            @csrf
            <div class="col-md-2">
                <label for="anio_id" class="form-label required-field">Año académico</label>
                <select id="anio_id" name="anio_id" class="form-select" required>
                    <option value="">Seleccionar año académico</option>
                    @foreach($anios as $anio)
                        <option value="{{ $anio->id }}" {{ old('anio_id', $anioActivo?->id) == $anio->id ? 'selected' : '' }}>
                            {{ $anio->anio }}{{ $anio->activo ? ' (Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="nivel_id" class="form-label required-field">Nivel</label>
                <select id="nivel_id" name="nivel_id" class="form-select" required disabled>
                    <option value="todos" {{ old('nivel_id') === 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="">Seleccionar nivel</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="orden_merito" class="form-label required-field">Orden</label>
                <select id="orden_merito" name="orden_merito[]" class="form-select orden-multiple select2" multiple required disabled>
                    @foreach($ordenesDisponibles as $orden)
                        <option value="{{ $orden }}" {{ in_array($orden, old('orden_merito', [])) ? 'selected' : '' }}>
                            {{ $orden }}° lugar
                        </option>
                    @endforeach
                </select>
                
            </div>

            <div class="col-md-2 text-end">
                <button type="submit" class="btn btn-success w-100" id="btnExportarOrden" disabled>
                    <i class="fas fa-file-excel me-2"></i> Exportar a Excel
                </button>
            </div>
        </form>

        <div class="mt-3 helper-text">
            Selecciona el año académico, nivel y número de orden de mérito para exportar los registros filtrados.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const $anio = $('#anio_id');
    const $nivel = $('#nivel_id');
    const $orden = $('#orden_merito');
    const $boton = $('#btnExportarOrden');

    $orden.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Seleccionar',
        closeOnSelect: false,
        dropdownAutoWidth: true
    });

    $nivel.prop('disabled', !$anio.val());
    $orden.prop('disabled', !$nivel.val());

    function actualizarBoton() {
        const anioSeleccionado = !!$anio.val();
        const nivelSeleccionado = !!$nivel.val();
        const ordenesSeleccionadas = ($orden.val() || []).length > 0;
        $boton.prop('disabled', !(anioSeleccionado && nivelSeleccionado && ordenesSeleccionadas));
    }

    function limpiarOrdenes() {
        $orden.val([]);
        $orden.find('option').prop('selected', false);
        $orden.trigger('change.select2');
    }

    $anio.on('change', function() {
        $nivel.val('');
        $nivel.prop('disabled', !$(this).val());
        limpiarOrdenes();
        $orden.prop('disabled', true);
        actualizarBoton();
    });

    $nivel.on('change', function() {
        limpiarOrdenes();
        $orden.prop('disabled', !$(this).val());
        actualizarBoton();
    });

    $orden.on('change', actualizarBoton);

    actualizarBoton();
});
</script>
@endsection
