{{-- resources/views/libretas/previsualizar.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualizar Libreta</title>
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: Arial, Helvetica, sans-serif;
    font-size:11px;
    color:#000;
    background:#e9ecef;
    padding:20px;
}

.container{
    width:100%;
    max-width:1050px;
    margin:auto;
    background:#fff;
    box-shadow:0 0 15px rgba(0,0,0,.15);
}

.toolbar{
    background:#003366;
    color:white;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.toolbar h4{
    font-size:16px;
}

.btn{
    border:none;
    padding:8px 14px;
    border-radius:5px;
    cursor:pointer;
    font-size:13px;
}

.btn-print{
    background:#28a745;
    color:white;
}

.btn-back{
    background:#6c757d;
    color:white;
}

.libreta-content{
    padding:20px;
}

/* TITULOS */
h1,h2,h3,h4,h5{
    margin-bottom:6px;
}

h5{
    background:#003366;
    color:white;
    padding:5px;
    font-size:11px;
    text-align:center;
}

/* TABLAS */
table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:12px;
}

table th,
table td{
    border:1px solid #000;
    padding:4px;
    vertical-align:middle;
}

table th{
    background:#d9e2f3;
    text-align:center;
    font-weight:bold;
}

.info-estudiante td{
    padding:5px;
}

.tabla-notas td{
    font-size:10px;
}

.tabla-notas td:first-child{
    width:42%;
}

.tabla-notas th{
    font-size:10px;
}

/* DOS COLUMNAS */
.two-columns{
    display:flex;
    gap:10px;
}

.column{
    width:50%;
}

/* FIRMAS */
.firmas{
    margin-top:35px;
    display:flex;
    justify-content:space-between;
    gap:40px;
}

.firma{
    width:45%;
    text-align:center;
}

.linea{
    border-top:1px solid #000;
    margin-top:35px;
    margin-bottom:5px;
}

/* FOOTER */
.footer{
    margin-top:15px;
    text-align:center;
    font-size:10px;
}

/* PDF */
@media print{
    body{
        background:white;
        padding:0;
    }

    .toolbar{
        display:none;
    }

    .container{
        box-shadow:none;
    }

    table{
        page-break-inside:auto;
    }

    tr{
        page-break-inside:avoid;
    }

    .two-columns{
        display:flex;
    }
}
</style>
</head>
<body>
    <div class="container">
        <div class="toolbar">
            <h4><i class="fas fa-eye"></i> Previsualización de Libreta</h4>
            <div>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir / Exportar PDF
                </button>
                <button class="btn btn-back" onclick="window.close()">
                    <i class="fas fa-arrow-left"></i> Cerrar
                </button>
            </div>
        </div>
        <div class="libreta-content">
            @if($matricula)
                @include('libretas.partials.libreta-alumno', [
                    'matricula' => $matricula, 
                    'periodos' => $periodos, 
                    'configLibreta' => $configLibreta, 
                    'configInstitucion' => $configInstitucion
                ])
            @else
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
            @endif
        </div>
    </div>
</body>
</html>