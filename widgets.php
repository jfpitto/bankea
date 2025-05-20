<?php
include 'conexion.php';
include 'funcionesgenerales.php';

$funcionesGenerales = new FuncionesGenerales($conexion);

// Obtener estadísticas de operaciones
function obtenerEstadisticasOperaciones($conexion) {
    $stats = [];
    
    // Total de operaciones
    $query = "SELECT COUNT(*) as total FROM operaciones";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_operaciones'] = $result->fetch_assoc()['total'];
    
    // Operaciones ganadoras (beneficio > 0)
    $query = "SELECT COUNT(*) as ganadoras FROM operaciones WHERE beneficio > 0";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['operaciones_ganadoras'] = $result->fetch_assoc()['ganadoras'];
    
    // Operaciones perdedoras (beneficio <= 0)
    $stats['operaciones_perdedoras'] = $stats['total_operaciones'] - $stats['operaciones_ganadoras'];
    
    // Winrate
    $stats['winrate'] = $stats['total_operaciones'] > 0 ? 
        round(($stats['operaciones_ganadoras'] / $stats['total_operaciones']) * 100, 2) : 0;
    
    // Beneficio total
    $query = "SELECT SUM(beneficio) as beneficio_total FROM operaciones";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['beneficio_total'] = $result->fetch_assoc()['beneficio_total'] ?? 0;
    
    // Operaciones por tipo (buy/sell)
    $query = "SELECT tipo, COUNT(*) as cantidad FROM operaciones GROUP BY tipo";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['operaciones_por_tipo'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['operaciones_por_tipo'][$row['tipo']] = $row['cantidad'];
    }
    
    // Operaciones diarias (últimos 30 días)
    $query = "SELECT DATE(fecha_hora_apertura) as fecha, COUNT(*) as cantidad 
              FROM operaciones 
              WHERE fecha_hora_apertura >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
              GROUP BY DATE(fecha_hora_apertura) 
              ORDER BY fecha";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['operaciones_diarias'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['operaciones_diarias'][$row['fecha']] = $row['cantidad'];
    }
    
    // Operaciones semanales y mensuales
    $stats['operaciones_semanales'] = obtenerOperacionesPorPeriodo($conexion, 'WEEK');
    $stats['operaciones_mensuales'] = obtenerOperacionesPorPeriodo($conexion, 'MONTH');
    
    // Calcular mejor/peor semana
    $stats['mejor_semana'] = calcularMejorPeorPeriodo($stats['operaciones_semanales']);
    $stats['peor_semana'] = calcularMejorPeorPeriodo($stats['operaciones_semanales'], false);
    
    // Calcular tendencia mensual
    $stats['tendencia_mensual'] = calcularTendenciaMensual($stats['operaciones_mensuales']);
    
    return $stats;
}

function obtenerOperacionesPorPeriodo($conexion, $periodo) {
    $query = "SELECT 
                DATE_FORMAT(fecha_hora_apertura, ?) as periodo,
                COUNT(*) as cantidad,
                SUM(CASE WHEN beneficio > 0 THEN 1 ELSE 0 END) as ganadoras,
                SUM(CASE WHEN beneficio <= 0 THEN 1 ELSE 0 END) as perdedoras,
                SUM(beneficio) as beneficio_total,
                AVG(beneficio) as beneficio_promedio
              FROM operaciones 
              GROUP BY periodo
              ORDER BY fecha_hora_apertura";
    
    $format = $periodo === 'WEEK' ? '%Y-%u' : '%Y-%m';
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $format);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['periodo']] = $row;
    }
    return $data;
}

function calcularMejorPeorPeriodo($periodos, $mejor = true) {
    if (empty($periodos)) return ['periodo' => 'N/A', 'beneficio_total' => 0];
    
    $filtered = array_filter($periodos, function($p) {
        return $p['beneficio_total'] != 0;
    });
    
    if (empty($filtered)) return ['periodo' => 'N/A', 'beneficio_total' => 0];
    
    usort($filtered, function($a, $b) use ($mejor) {
        return $mejor ? 
            $b['beneficio_total'] <=> $a['beneficio_total'] : 
            $a['beneficio_total'] <=> $b['beneficio_total'];
    });
    
    return $filtered[0];
}

function calcularTendenciaMensual($meses) {
    if (count($meses) < 2) return 0;
    
    $meses = array_values($meses);
    $ultimo = end($meses);
    $penultimo = prev($meses);
    
    if ($penultimo['beneficio_total'] == 0) return 0;
    
    return round((($ultimo['beneficio_total'] - $penultimo['beneficio_total']) / abs($penultimo['beneficio_total'])) * 100, 2);
}

$stats = obtenerEstadisticasOperaciones($conexion);

?>

    <?php include 'calendario.php'; ?>

<style>
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        width: 100%;
    }
    .stats-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .stats-value {
        font-size: 24px;
        font-weight: bold;
        margin: 10px 0;
    }
    .stats-label {
        color: #666;
        font-size: 14px;
    }
    .positive {
        color: #28a745;
    }
    .negative {
        color: #dc3545;
    }
    /* Estilos para la tabla con scroll */
    .table-responsive.scroll-table {
        max-height: 500px;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    /* Estilo para la barra de scroll */
    .table-responsive.scroll-table::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .table-responsive.scroll-table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .table-responsive.scroll-table::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .table-responsive.scroll-table::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    /* Cabecera fija */
    .fixed-header thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }
    .badge-tendencia {
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 15px;
    }

    square-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
    margin-left: 10px;
    padding: 0;
}

.square-btn i {
    font-size: 20px;
}
</style>

<div class="container">
    <div class="row mt-4">
        <div class="col-md-12">
           
            
            <div class="row">
                <!-- Tarjeta de total de operaciones -->
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-label">Total Operaciones</div>
                        <div class="stats-value"><?= $stats['total_operaciones'] ?></div>
                    </div>
                </div>
                
                <!-- Tarjeta de operaciones ganadoras -->
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-label">Operaciones Ganadoras</div>
                        <div class="stats-value positive"><?= $stats['operaciones_ganadoras'] ?></div>
                    </div>
                </div>
                
                <!-- Tarjeta de operaciones perdedoras -->
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-label">Operaciones Perdedoras</div>
                        <div class="stats-value negative"><?= $stats['operaciones_perdedoras'] ?></div>
                    </div>
                </div>
                
                <!-- Tarjeta de winrate -->
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-label">Winrate</div>
                        <div class="stats-value"><?= $stats['winrate'] ?>%</div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Gráfico de operaciones por tipo -->
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Operaciones por Tipo (Buy/Sell)</h5>
                        <div class="chart-container">
                            <canvas id="tipoOperacionesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de operaciones diarias -->
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Operaciones Diarias (Últimos 30 días)</h5>
                        <div class="chart-container">
                            <canvas id="operacionesDiariasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Gráfico de operaciones semanales -->
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Operaciones Semanales</h5>
                        <div class="chart-container">
                            <canvas id="operacionesSemanalesChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de operaciones mensuales -->
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Operaciones Mensuales</h5>
                        <div class="chart-container">
                            <canvas id="operacionesMensualesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen de rendimiento -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="stats-card">
                        <h5>Resumen de Rendimiento</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-label">Mejor Semana</div>
                                <div class="stats-value positive"><?= number_format($stats['mejor_semana']['beneficio_total'] ?? 0, 2) ?></div>
                                <small>Semana <?= $stats['mejor_semana']['periodo'] ?? 'N/A' ?></small>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-label">Peor Semana</div>
                                <div class="stats-value negative"><?= number_format($stats['peor_semana']['beneficio_total'] ?? 0, 2) ?></div>
                                <small>Semana <?= $stats['peor_semana']['periodo'] ?? 'N/A' ?></small>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-label">Tendencia Mensual</div>
                                <div class="stats-value <?= ($stats['tendencia_mensual'] >= 0 ? 'positive' : 'negative') ?>">
                                    <?= number_format($stats['tendencia_mensual'] ?? 0, 2) ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de operaciones recientes con scroll -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="stats-card">
                        <h5>Operaciones Recientes</h5>
                        <div class="table-responsive scroll-table">
                            <table class="table table-striped fixed-header">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Símbolo</th>
                                        <th>Tipo</th>
                                        <th>Volumen</th>
                                        <th>Resultado</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT id, fecha_hora_apertura, simbolo, tipo, volumen, beneficio, valido 
                                              FROM operaciones 
                                              ORDER BY fecha_hora_apertura DESC 
                                              LIMIT 50";
                                    $stmt = $conexion->prepare($query);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        $color = $row['beneficio'] > 0 ? 'positive' : 'negative';
                                        echo "<tr>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_hora_apertura'])) . "</td>";
                                        echo "<td>" . $row['simbolo'] . "</td>";
                                        echo "<td>" . strtoupper($row['tipo']) . "</td>";
                                        echo "<td>" . $row['volumen'] . "</td>";
                                        echo "<td class='$color'>" . number_format($row['beneficio'], 2) . "</td>";
                                        echo "<td>" . $row['valido'] . "</td>";
                                        echo "<td>
                                                <button class='btn btn-sm btn-primary editar-operacion' data-id='{$row['id']}'>Editar</button>
                                              </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar operación -->
<div class="modal fade" id="editarOperacionModal" tabindex="-1" role="dialog" aria-labelledby="editarOperacionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarOperacionModalLabel">Editar Operación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarOperacion">
                    <input type="hidden" id="operacionId">
                    <div class="form-group">
                        <label for="operacionValido">¿Operación válida?</label>
                        <select class="form-control" id="operacionValido" name="valido">
                            <option value="SI">Sí</option>
                            <option value="NO">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Botón para abrir modal de creación (añadir esto cerca del título) -->
<button class="btn btn-success square-btn" data-toggle="modal" data-target="#crearOperacionModal">
    <span style="font-size: 18px;">+</span> 
</button>
</h3>

<!-- Modal para crear operación (añadir al final, junto al modal de edición) -->
<div class="modal fade" id="crearOperacionModal" tabindex="-1" role="dialog" aria-labelledby="crearOperacionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearOperacionModalLabel">Nueva Operación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCrearOperacion">
                    <div class="form-group">
                        <label for="nuevoSimbolo">Símbolo</label>
                        <input type="text" class="form-control" id="nuevoSimbolo" name="simbolo" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevoTipo">Tipo</label>
                        <select class="form-control" id="nuevoTipo" name="tipo" required>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nuevoVolumen">Volumen</label>
                        <input type="number" step="0.01" class="form-control" id="nuevoVolumen" name="volumen" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevoBeneficio">Beneficio</label>
                        <input type="number" step="0.01" class="form-control" id="nuevoBeneficio" name="beneficio" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevoFecha">Fecha Apertura</label>
                        <input type="datetime-local" class="form-control" id="nuevoFecha" name="fecha_hora_apertura" required>
                    </div>
                    <div class="form-group">
                        <label for="nuevoValido">¿Operación válida?</label>
                        <select class="form-control" id="nuevoValido" name="valido" required>
                            <option value="SI">Sí</option>
                            <option value="NO">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de operaciones por tipo
    const tipoCtx = document.getElementById('tipoOperacionesChart').getContext('2d');
    const tipoChart = new Chart(tipoCtx, {
        type: 'pie',
        data: {
            labels: ['Buy', 'Sell'],
            datasets: [{
                data: [
                    <?= $stats['operaciones_por_tipo']['buy'] ?? 0 ?>,
                    <?= $stats['operaciones_por_tipo']['sell'] ?? 0 ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Gráfico de operaciones diarias
    const diariasCtx = document.getElementById('operacionesDiariasChart').getContext('2d');
    const diariasChart = new Chart(diariasCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($stats['operaciones_diarias'])) ?>,
            datasets: [{
                label: 'Operaciones por día',
                data: <?= json_encode(array_values($stats['operaciones_diarias'])) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Gráfico de operaciones semanales
    const semanalCtx = document.getElementById('operacionesSemanalesChart').getContext('2d');
    const semanalChart = new Chart(semanalCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($stats['operaciones_semanales'])) ?>,
            datasets: [{
                label: 'Beneficio Total',
                data: <?= json_encode(array_column($stats['operaciones_semanales'], 'beneficio_total')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Winrate %',
                data: <?= json_encode(array_map(function($week) {
                    return $week['cantidad'] > 0 ? 
                        round(($week['ganadoras'] / $week['cantidad']) * 100, 2) : 0;
                }, $stats['operaciones_semanales'])) ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    title: { display: true, text: 'Beneficio' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    max: 100,
                    title: { display: true, text: 'Winrate %' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
    
    // Gráfico de operaciones mensuales
    const mensualCtx = document.getElementById('operacionesMensualesChart').getContext('2d');
    const mensualChart = new Chart(mensualCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($stats['operaciones_mensuales'])) ?>,
            datasets: [{
                label: 'Beneficio Total',
                data: <?= json_encode(array_column($stats['operaciones_mensuales'], 'beneficio_total')) ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Operaciones',
                data: <?= json_encode(array_column($stats['operaciones_mensuales'], 'cantidad')) ?>,
                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    title: { display: true, text: 'Beneficio' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: { display: true, text: 'N° Operaciones' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
    
    // Manejar clic en botones de edición
    document.querySelectorAll('.editar-operacion').forEach(btn => {
        btn.addEventListener('click', function() {
            const operacionId = this.getAttribute('data-id');
            document.getElementById('operacionId').value = operacionId;
            $('#editarOperacionModal').modal('show');
        });
    });
    
   // Manejar envío del formulario de edición
document.getElementById('formEditarOperacion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('id', document.getElementById('operacionId').value);
    
    fetch('actualizar_operacion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text()) // Cambiar a text() para depurar
    .then(text => {
        console.log('Respuesta del servidor:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Respuesta no válida JSON');
        }
        console.log('Respuesta parseada:', data);
        if (data.success) {
            alert('Operación actualizada correctamente');
            $('#editarOperacionModal').modal('hide');
            location.reload();
        } else {
            alert('Error al actualizar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la operación: ' + error.message);
    });
});


    // Hacer que la cabecera de la tabla sea fija al hacer scroll
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.querySelector('.scroll-table');
        const thead = document.querySelector('.fixed-header thead');
        
        if (tableContainer && thead) {
            tableContainer.addEventListener('scroll', function() {
                const scrollTop = tableContainer.scrollTop;
                thead.style.transform = `translateY(${scrollTop}px)`;
            });
        }
    });
    // Manejar envío del formulario de creación
document.getElementById('formCrearOperacion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Establecer la fecha actual si no se proporciona
    if (!formData.get('fecha_hora_apertura')) {
        formData.set('fecha_hora_apertura', new Date().toISOString().slice(0, 16));
    }
    
    fetch('crear_operacion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Operación creada correctamente');
            $('#crearOperacionModal').modal('hide');
            location.reload();
        } else {
            alert('Error al crear: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear la operación');
    });
});

// Establecer fecha actual por defecto
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('nuevoFecha');
    if (fechaInput) {
        const now = new Date();
        // Ajustar el desfase de la zona horaria
        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0, 16);
        fechaInput.value = localISOTime;
    }
});
</script>