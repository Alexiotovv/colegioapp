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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            background: white;
        }
        
        .container {
            width: 100%;
            padding: 15px;
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
            max-width: 150px;
            max-height: 180px;
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
            padding: 6px;
        }
        
        .label {
            background: #c9c9c9;
            font-weight: bold;
            width: 27%;
        }
        
        h1 {
            text-align: center;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        /* TABLAS */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
        }
        
        th {
            background: #c9c9c9;
            text-align: center;
        }
        
        /* Tabla de notas */
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
        }
        
        /* Tabla de apreciaciones */
        .tabla-apreciaciones {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        .tabla-apreciaciones th,
        .tabla-apreciaciones td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
            vertical-align: top;
        }
        
        .tabla-apreciaciones th {
            background: #c9c9c9;
            text-align: center;
        }
        
        .tabla-apreciaciones td:first-child {
            text-align: center;
            font-weight: bold;
        }
        
        /* Tabla de evaluación del padre de familia */
        .tabla-evaluacion-padres {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        .tabla-evaluacion-padres th,
        .tabla-evaluacion-padres td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
        }
        
        .tabla-evaluacion-padres th {
            background: #c9c9c9;
            font-weight: bold;
        }
        
        .tabla-evaluacion-padres td:first-child {
            text-align: left;
            font-weight: normal;
        }
        
        /* DOS COLUMNAS */
        .two-columns {
            display: flex;
            gap: 10px;
        }
        
        .column {
            width: 50%;
        }
        
        .column h5 {
            background: #c9c9c9;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            border-bottom: none;
            margin: 0;
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
        
        .firma img {
            max-height: 90px;
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
        
        .logo_encabezados {
            width: 90px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                padding: 0;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    
    <div class="container">
        @if($tipo == 'aula')
            @foreach($matriculas as $matricula)
                @include('libretas.partials.libreta-alumno', [
                    'matricula' => $matricula, 
                    'periodos' => $periodos, 
                    'configLibreta' => $configLibreta, 
                    'configInstitucion' => $configInstitucion
                ])
                @if(!$loop->last)
                    <div style="page-break-after: always;"></div>
                @endif
            @endforeach
        @else
            @include('libretas.partials.libreta-alumno', [
                'matricula' => $matricula, 
                'periodos' => $periodos, 
                'configLibreta' => $configLibreta, 
                'configInstitucion' => $configInstitucion
            ])
        @endif
    </div>
</body>
</html>