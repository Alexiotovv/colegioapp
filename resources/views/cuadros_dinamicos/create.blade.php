@extends('layouts.app')

@section('title', 'Crear Cuadro Dinámico')

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('admin.cuadros-dinamicos.store') }}">
        @csrf
        @include('cuadros_dinamicos._form')
    </form>
</div>
@endsection
