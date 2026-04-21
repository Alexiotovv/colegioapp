<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Libreta de Notas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            width: 100%;
            padding: 15px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo-left img, .logo-right img {
            max-height: 70px;
            max-width: 100px;
        }
        
        .titulo-ministerio {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .titulo-ministerio h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        
        .titulo-ministerio h3 {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }
        
        .info-institucion {
            text-align: center;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 5px;
        }
        
        .info-institucion p {
            margin: 2px 0;
            font-size: 10px;
        }
        
        /* Tabla de datos del estudiante */
        .info-estudiante {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }
        
        .info-estudiante td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }
        
        .info-estudiante td:first-child {
            width: 30%;
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        /* Tabla de notas */
        .tabla-notas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .tabla-notas th, .tabla-notas td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        
        .tabla-notas th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .tabla-notas td:first-child {
            text-align: left;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        /* Tabla de apreciaciones */
        .tabla-apreciaciones {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .tabla-apreciaciones th, .tabla-apreciaciones td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        
        .tabla-apreciaciones th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        /* Tabla de evaluación de padres */
        .tabla-evaluacion-padres {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .tabla-evaluacion-padres th, .tabla-evaluacion-padres td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }
        
        .tabla-evaluacion-padres th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .tabla-evaluacion-padres td:first-child {
            text-align: left;
        }
        
        /* Dos columnas */
        .two-columns {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .column {
            flex: 1;
        }
        
        .column h5 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: center;
            background-color: #f0f0f0;
            padding: 4px;
        }
        
        /* Firmas */
        .firmas {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        
        .firma {
            width: 30%;
        }
        
        .firma img {
            max-height: 50px;
            margin-bottom: 5px;
        }
        
        .linea {
            border-top: 1px solid #333;
            width: 80%;
            margin: 8px auto 0;
        }
        
        /* Pie de página */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        
        /* Responsive */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header con logos -->
        <div class="logo-container">
            <div class="logo-left">
                @if($configLibreta->logo_pais && Storage::disk('public')->exists($configLibreta->logo_pais))
                    <img src="{{ public_path('storage/' . $configLibreta->logo_pais) }}" alt="Logo País">
                @endif
            </div>
            <div class="logo-right">
                @if($configLibreta->logo_institucion && Storage::disk('public')->exists($configLibreta->logo_institucion))
                    <img src="{{ public_path('storage/' . $configLibreta->logo_institucion) }}" alt="Logo Institución">
                @endif
            </div>
        </div>
        
        <div class="titulo-ministerio">
            <h2>MINISTERIO DE EDUCACIÓN</h2>
            <h3>{{ $configLibreta->titulo ?? 'Libreta de Notas' }}</h3>
            @if($configLibreta->subtitulo)
                <p>{{ $configLibreta->subtitulo }}</p>
            @endif
        </div>
        
        <div class="info-institucion">
            <p><strong>DRE:</strong> {{ $configLibreta->dre ?? '' }} | <strong>UGEL:</strong> {{ $configLibreta->ugel ?? '' }}</p>
            <p><strong>Institución Educativa:</strong> {{ $configInstitucion->nombre ?? '' }}</p>
            <p><strong>Código Modular:</strong> {{ $configInstitucion->ruc ?? '' }}</p>
        </div>
        
        @if($tipo == 'aula')
            @foreach($matriculas as $matricula)
                @include('libretas.partials.libreta-alumno', ['matricula' => $matricula, 'periodos' => $periodos, 'configLibreta' => $configLibreta, 'configInstitucion' => $configInstitucion])
                @if(!$loop->last)
                    <div style="page-break-after: always;"></div>
                @endif
            @endforeach
        @else
            @include('libretas.partials.libreta-alumno', ['matricula' => $matricula, 'periodos' => $periodos, 'configLibreta' => $configLibreta, 'configInstitucion' => $configInstitucion])
        @endif
    </div>
</body>
</html>