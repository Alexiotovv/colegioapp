@extends('layouts.app')

@section('title', 'Editar Cuadro Dinámico')

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('admin.cuadros-dinamicos.update', $cuadro) }}">
        @csrf
        @method('PUT')
        @include('cuadros_dinamicos._form')
    </form>

    @if(isset($cuadro))
        <form method="POST" action="{{ route('admin.cuadros-dinamicos.destroy', $cuadro) }}" class="mt-3" onsubmit="return confirm('¿Eliminar cuadro? Esta acción no se puede deshacer');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    @endif
</div>
@endsection
