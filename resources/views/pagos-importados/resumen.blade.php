@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Resumen de Pagos</h2>
            </div>
        </div>

        <!-- Filtro de Año -->
        <div class="row mb-4">
            <div class="col-md-4">
                <form method="GET" action="{{ route('admin.pagos-importados-resumen.resumen') }}" class="form-inline">
                    <label for="anio_emision" class="mr-2">Seleccionar Año:</label>
                    <select name="anio_emision" id="anio_emision" class="form-control mr-2">
                        <option value="">Seleccione un año</option>
                        @foreach ($anosDisponibles as $ano)
                            <option value="{{ $ano }}" {{ $anioEmision == $ano ? 'selected' : '' }}>
                                {{ $ano }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>
        </div>

        @if ($anioEmision && count($resumen) > 0)
            <!-- Resumen por Niveles, Grados y Secciones -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4>Resumen Detallado - Año {{ $anioEmision }}</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Total Estudiantes</th>
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resumen as $nivel => $grados)
                                    @foreach ($grados as $grado => $secciones)
                                        @foreach ($secciones as $seccion => $datos)
                                            <tr>
                                                <td><strong>{{ $nivel }}</strong></td>
                                                <td>{{ $grado }}</td>
                                                <td>{{ $seccion }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-info">{{ $datos['total_estudiantes'] }}</span>
                                                </td>
                                                @foreach ($datos['pagos_por_mes'] as $mes => $cantidad)
                                                    <td>
                                                        <span class="badge {{ $cantidad == $datos['total_estudiantes'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                                            {{ $cantidad }}/{{ $datos['total_estudiantes'] }}
                                                        </span>
                                                        <div class="small text-muted mt-1">
                                                            S/ {{ number_format((float) ($datos['monto_por_mes'][$mes] ?? 0), 2) }}
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Resumen -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4>Visualización de Pagos por Grado y Sección</h4>
                    <div style="position: relative; height: 400px;">
                        <canvas id="graficoResumen"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico por Nivel -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4>Resumen por Nivel</h4>
                    <div style="position: relative; height: 300px;">
                        <canvas id="graficoNiveles"></canvas>
                    </div>
                </div>
            </div>
        @elseif ($anioEmision)
            <div class="alert alert-warning">
                No hay datos de importación para el año {{ $anioEmision }}.
            </div>
        @else
            <div class="alert alert-info">
                Seleccione un año para ver el resumen de pagos.
            </div>
        @endif
    </div>

    <!-- Script para gráficos con Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($anioEmision && count($resumen) > 0)
                // Preparar datos para gráfico principal
                const resumen = @json($resumen);
                const meses = ['Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre',
                    'Noviembre', 'Diciembre'
                ];
                const mesKeys = ['marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre',
                    'noviembre', 'diciembre'
                ];

                // Gráfico 1: Por Grado y Sección
                let labels = [];
                let datasets = [];
                const colores = [
                    'rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(201, 203, 207, 0.7)',
                    'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ];

                let colorIndex = 0;
                for (const [nivel, grados] of Object.entries(resumen)) {
                    for (const [grado, secciones] of Object.entries(grados)) {
                        for (const [seccion, datos] of Object.entries(secciones)) {
                            labels.push(`${grado}-${seccion}`);

                            let data = [];
                            let porcentajes = [];
                            for (const mes of mesKeys) {
                                const porcentaje = (datos.pagos_por_mes[mes] /
                                    datos.total_estudiantes) * 100;
                                porcentajes.push(porcentaje);
                            }

                            datasets.push({
                                label: `${nivel} ${grado}-${seccion}`,
                                data: porcentajes,
                                borderColor: colores[colorIndex % colores.length],
                                backgroundColor: colores[colorIndex % colores.length].replace(
                                    '0.7', '0.2'),
                                borderWidth: 2,
                                tension: 0.1
                            });

                            colorIndex++;
                        }
                    }
                }

                const ctx1 = document.getElementById('graficoResumen').getContext('2d');
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: meses,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Porcentaje de Pagos por Mes y Grado',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });

                // Gráfico 2: Resumen por Nivel
                let nivelLabels = [];
                let totalEstudiantesPorNivel = {};
                let pagosPorMesPorNivel = {};

                for (const [nivel, grados] of Object.entries(resumen)) {
                    if (!totalEstudiantesPorNivel[nivel]) {
                        totalEstudiantesPorNivel[nivel] = 0;
                        pagosPorMesPorNivel[nivel] = {};
                        for (const mes of mesKeys) {
                            pagosPorMesPorNivel[nivel][mes] = 0;
                        }
                    }

                    for (const [grado, secciones] of Object.entries(grados)) {
                        for (const [seccion, datos] of Object.entries(secciones)) {
                            totalEstudiantesPorNivel[nivel] += datos.total_estudiantes;
                            for (const mes of mesKeys) {
                                pagosPorMesPorNivel[nivel][mes] += datos.pagos_por_mes[mes];
                            }
                        }
                    }
                }

                nivelLabels = Object.keys(totalEstudiantesPorNivel);
                let nivelDatasets = [];

                for (const mes of mesKeys) {
                    let mesData = [];
                    for (const nivel of nivelLabels) {
                        const porcentaje = (pagosPorMesPorNivel[nivel][mes] /
                            totalEstudiantesPorNivel[nivel]) * 100;
                        mesData.push(porcentaje);
                    }

                    const mesIndex = mesKeys.indexOf(mes);
                    nivelDatasets.push({
                        label: meses[mesIndex],
                        data: mesData,
                        backgroundColor: colores[mesIndex % colores.length]
                    });
                }

                const ctx2 = document.getElementById('graficoNiveles').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: nivelLabels,
                        datasets: nivelDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Porcentaje de Pagos por Nivel y Mes',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@endsection
