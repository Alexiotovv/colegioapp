@extends('layouts.app')

@section('title', 'Importar Pagos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-file-excel me-2" style="color: var(--primary-color);"></i>
            Importar Pagos de Alumnos
        </h4>
        <a href="{{ route('admin.pagos-importados-resumen.resumen') }}" class="btn btn-info btn-sm">
            <i class="fas fa-chart-bar me-1"></i> Ver Resumen
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.pagos-importados.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="anio_emision" class="form-label">Año de emisión</label>
                    <input type="number" name="anio_emision" id="anio_emision" class="form-control" value="{{ old('anio_emision', date('Y')) }}" min="2000" max="2100">
                </div>
                <div class="col-md-5">
                    <label for="archivo" class="form-label required-field">Archivo Excel</label>
                    <input type="file" name="archivo" id="archivo" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i> Importar
                    </button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">Se leen las filas desde la 5. La fila 4 se toma como encabezado.</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pagos-importados.index') }}" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label for="search" class="form-label">Buscar por estudiante, DNI o facturación</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="Estudiante, DNI Est., Doc. Facturación o Nombre Facturación">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search me-2"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Estudiante</th>
                        <th>DNI Est.</th>
                        <th>Doc. Facturación</th>
                        <th>Nombre Facturación</th>
                        <th>Nivel</th>
                        <th>Grado</th>
                        <th>Sección</th>
                        <th>Marzo</th>
                        <th>Abril</th>
                        <th>Mayo</th>
                        <th>Junio</th>
                        <th>Julio</th>
                        <th>Agosto</th>
                        <th>Setiembre</th>
                        <th>Octubre</th>
                        <th>Noviembre</th>
                        <th>Diciembre</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr>
                            <td>{{ $pago->numero_fila ?? $loop->iteration }}</td>
                            <td>{{ $pago->estudiante }}</td>
                            <td>{{ $pago->dni_est }}</td>
                            <td>{{ $pago->doc_facturacion_dni }}</td>
                            <td>{{ $pago->nombre_facturacion }}</td>
                            <td>{{ $pago->nivel }}</td>
                            <td>{{ $pago->grado }}</td>
                            <td>{{ $pago->seccion }}</td>
                            <td>{{ $pago->marzo }}</td>
                            <td>{{ $pago->abril }}</td>
                            <td>{{ $pago->mayo }}</td>
                            <td>{{ $pago->junio }}</td>
                            <td>{{ $pago->julio }}</td>
                            <td>{{ $pago->agosto }}</td>
                            <td>{{ $pago->setiembre }}</td>
                            <td>{{ $pago->octubre }}</td>
                            <td>{{ $pago->noviembre }}</td>
                            <td>{{ $pago->diciembre }}</td>
                            <td>{{ $pago->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center text-muted">No hay registros importados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $pagos->links() }}
        </div>
    </div>
</div>
@endsection
