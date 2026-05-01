@extends('layouts.app')

@section('title', 'Cuadros Dinámicos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-th-large me-2"></i>Cuadros Dinámicos</h4>
        <div>
            <form method="GET" class="d-inline-block me-2">
                <select name="nivel_id" class="form-select d-inline-block" style="width:200px;" onchange="this.form.submit()">
                    <option value="">Todos los niveles</option>
                    @foreach($niveles as $n)
                        <option value="{{ $n->id }}" {{ (string)($nivelId) === (string)$n->id ? 'selected' : '' }}>{{ $n->nombre }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.cuadros-dinamicos.create') }}" class="btn btn-primary">Crear Cuadro</a>
        </div>
    </div>

    <div class="card p-3">
        @if($cuadros->count())
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>Nivel</th><th>Tipo</th><th>Ancho</th><th>Activo</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach($cuadros as $c)
                        <tr>
                            <td>{{ $c->nombre }}</td>
                            <td>{{ $c->nivel_id ? (\App\Models\Nivel::find($c->nivel_id)->nombre ?? 'N/D') : 'Todos' }}</td>
                            <td>{{ $c->tipo }}</td>
                            <td>{{ $c->ancho }}</td>
                            <td>{{ $c->activo ? 'Sí' : 'No' }}</td>
                            <td>
                                <a href="{{ route('admin.cuadros-dinamicos.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form method="POST" action="{{ route('admin.cuadros-dinamicos.destroy', $c) }}" class="d-inline-block" onsubmit="return confirm('Eliminar cuadro dinámico?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No hay cuadros dinámicos creados.</p>
        @endif
    </div>
</div>
@endsection
