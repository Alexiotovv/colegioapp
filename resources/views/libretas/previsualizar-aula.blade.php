{{-- resources/views/libretas/previsualizar-aula.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Previsualizar Libretas - Aula {{ $aula->nombre }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            background: #ececec;
            color: #000; /* Ensure readable text color */
        }

        .container {
            width: 1350px;
            margin: auto;
            background: white;
            padding: 15px;
        }

        .toolbar {
            background: #003366;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn {
            border: none;
            padding: 8px 14px;
            cursor: pointer;
            color: white;
            border-radius: 4px;
        }

        .btn-print {
            background: #28a745;
        }

        .btn-back {
            background: #6c757d;
        }

        h1 {
            text-align: center;
            font-size: 11px;
            margin-bottom: 8px;
        }

        /* ENCABEZADO */
        .header-box {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: center;
        }

        .logo-side {
            width: 18%;
            text-align: center;
        }

        .logo-side img {
            max-width: 100px;
            max-height: 120px;
        }

        .center-box {
            width: 64%;
        }

        .info-top {
            width: 100%;
            border-collapse: collapse;
        }

        .info-top td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .label {
            background: #c9c9c9;
            font-weight: bold;
            width: 27%;
        }

        /* TABLAS */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 7px
        }

        th {
            background: #c9c9c9;
            text-align: center;
        }

        .tabla-notas td {
            font-size: 8px;
        }

        .tabla-notas td:first-child {
            text-align: left;
            width: 42%;
        }

        /* DOS COLUMNAS */
        .two-columns {
            display: flex;
            gap: 10px;
        }

        .column {
            width: 50%;
        }

        .subtitle {
            background: #c9c9c9;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            border-bottom: none;
        }

        /* FIRMAS */
        .firmas {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .firma {
            width: 40%;
            text-align: center;
        }

        .linea {
            border-top: 1px solid #000;
            margin-top: 45px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }

        @media print {
            .toolbar {
                display: none;
            }

            body {
                background: white;
            }

            .container {
                width: 100%;
            }
        }


        .tabla-notas {
            font-size: 10px;
        }

        .tabla-notas th {
            background: #c9c9c9;
            text-align: center;
            font-weight: bold;
        }

        .tabla-notas td {
            padding: 4px;
            vertical-align: middle;
        }

        .tabla-notas td:first-child {
            font-weight: normal;
        }

        .tabla-notas tr {
            page-break-inside: avoid;
        }
        .logo_encabezados{
            width: 90px;
        }

        /* Asegurar que los colores de fondo se impriman */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .tabla-notas th {
                background-color: #c9c9c9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .fila-promedio-area td {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .fila-promedio-general {
                background-color: #e8f0fe !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .tabla-notas td:first-child,
            .tabla-notas td:nth-child(2) {
                background-color: #f9f9f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Estilos para la tabla mejorada */
        .tabla-notas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
            page-break-inside: avoid;
        }

        .tabla-notas th, 
        .tabla-notas td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: center;
        }

        .tabla-notas th {
            background-color: #c9c9c9;
            text-align: center;
        }

        .tabla-notas td {
            vertical-align: middle;
        }

        .tabla-notas td:first-child,
        .tabla-notas td:nth-child(2) {
            background-color: #f9f9f9;
            width: 10%;
        }

        .fila-promedio-area td {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 6px;
        }

        .fila-promedio-general {
            background-color: #e8f0fe;
        }
        
        .libreta-content {
            page-break-after: always;
        }

        .libreta-content:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="toolbar">
            <div>
                <strong>Previsualización de Libretas</strong> - Aula: {{ $aula->nombre }} - Total: {{ $matriculas->count() }} alumnos
            </div>
            <div>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Imprimir Todo
                </button>
                <button class="btn btn-back" onclick="window.close()">
                    <i class="fas fa-times me-2"></i> Cerrar
                </button>
            </div>
        </div>
        <h1>INFORME DE PROGRESO DE LAS COMPETENCIAS DEL ESTUDIANTE - 2025</h1>
        
        @foreach($matriculas as $index => $matricula)
            <div class="header-box">

                <div class="logo-side">
                    <img src="{{ Storage::url($configLibreta->logo_pais) }}" class="logo_encabezados" alt="Logo País">
                </div>

                <div class="center-box">

                    <table class="info-top">
                        <tr>
                            <td class="label">DRE:</td>
                            <td>{{ $configLibreta->dre }}</td>
                            <td class="label">UGEL:</td>
                            <td>{{ $configLibreta->ugel }}</td>
                        </tr>

                        
                        <tr>
                            <td class="label">Institución educativa:</td>
                            <td>{{ $configInstitucion->nombre ?? '' }}</td>
                            <td class="label">Nivel:</td>
                            <td>{{ $matricula->aula->grado->nivel->nombre ?? '' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Grado:</td>
                            <td>{{ $matricula->aula->grado->nombre ?? '' }}</td>
                            <td class="label">Sección:</td>
                            <td>{{ $matricula->aula->seccion->nombre ?? '' }}</td>
                        </tr>

                        <tr>
                            <td class="label">Apellidos y nombres del estudiante:</td>
                            <td colspan="3">
                                {{ $matricula->alumno->apellido_paterno }}
                                {{ $matricula->alumno->apellido_materno }},
                                {{ $matricula->alumno->nombres }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label">Código del estudiante:</td>
                            <td>{{ $matricula->alumno->codigo_estudiante }}</td>
                            <td class="label">DNI:</td>
                            <td>{{ $matricula->alumno->dni }}</td>
                        </tr>

                        <tr>
                            <td class="label">Docente o tutor:</td>
                            <td colspan="3">{{ $matricula->aula->docente->name ?? '' }}</td>
                        </tr>
                    </table>

                </div>

                <div class="logo-side">
                        <img class="logo_encabezados" src="{{ Storage::url($configLibreta->logo_institucion) }}"">
                </div>

            </div>
            <div class="libreta-content">
                @include('libretas.partials.libreta-alumno', [
                    'matricula' => $matricula,
                    'periodos' => $periodos,
                    'configLibreta' => $configLibreta,
                    'configInstitucion' => $configInstitucion
                ])
            </div>
        @endforeach
    </div>
</body>
</html>