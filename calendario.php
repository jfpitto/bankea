<?php
require 'conexion.php';

// Función optimizada para obtener datos del mes
function obtenerDatosMes($conexion, $mes, $año) {
    $primer_dia = "$año-$mes-01";
    $ultimo_dia = date("Y-m-t", strtotime($primer_dia));
    
    $query = "SELECT 
                DAY(fecha_hora_apertura) as dia,
                COUNT(*) as total,
                SUM(CASE WHEN tipo = 'buy' THEN 1 ELSE 0 END) as compras,
                SUM(CASE WHEN tipo = 'sell' THEN 1 ELSE 0 END) as ventas,
                SUM(beneficio) as beneficio,
                GROUP_CONCAT(DISTINCT simbolo) as simbolos
              FROM operaciones
              WHERE fecha_hora_apertura BETWEEN ? AND ?
              GROUP BY dia
              ORDER BY dia";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $primer_dia, $ultimo_dia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[$row['dia']] = $row;
    }
    return $datos;
}

// Obtener mes y año actual
$mes_actual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$año_actual = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
$mes_actual = max(1, min(12, $mes_actual));
$año_actual = max(2020, min(2100, $año_actual));

// Obtener datos del mes
$datos_mes = obtenerDatosMes($conexion, $mes_actual, $año_actual);
$nombre_mes = date('F', mktime(0, 0, 0, $mes_actual, 1));
$dias_mes = date('t', strtotime("$año_actual-$mes_actual-01"));
$primer_dia_semana = date('N', strtotime("$año_actual-$mes_actual-01"));
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-secondary prev-month me-2">
                <i class="fas fa-chevron-left"></i>
            </button>
            <h5 class="mb-0 fw-bold">
                <?= ucfirst($nombre_mes) ?> <?= $año_actual ?>
            </h5>
            <button class="btn btn-sm btn-outline-secondary next-month ms-2">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <button class="btn btn-sm btn-primary btn-hoy">
            <i class="fas fa-calendar-day me-1"></i> Hoy
        </button>
    </div>
    
    <div class="card-body p-2">
        <!-- Días de la semana -->
        <div class="row g-1 mb-2 text-center small text-muted fw-bold">
            <div class="col p-1">Lun</div>
            <div class="col p-1">Mar</div>
            <div class="col p-1">Mié</div>
            <div class="col p-1">Jue</div>
            <div class="col p-1">Vie</div>
            <div class="col p-1">Sáb</div>
            <div class="col p-1">Dom</div>
        </div>
        
        <!-- Días del mes -->
        <div class="row g-1">
            <?php
            // Días vacíos al inicio
            for ($i = 1; $i < $primer_dia_semana; $i++) {
                echo '<div class="col p-1"><div class="calendar-day empty"></div></div>';
            }
            
            // Días del mes
            for ($dia = 1; $dia <= $dias_mes; $dia++) {
                $datos_dia = $datos_mes[$dia] ?? null;
                $es_hoy = ($dia == date('j') && $mes_actual == date('n') && $año_actual == date('Y'));
                
                // Determinar clase según el beneficio
                $clase_beneficio = '';
                if ($datos_dia && $datos_dia['beneficio'] > 0) {
                    $clase_beneficio = 'day-profit';
                } elseif ($datos_dia && $datos_dia['beneficio'] < 0) {
                    $clase_beneficio = 'day-loss';
                } elseif ($datos_dia) {
                    $clase_beneficio = 'day-neutral';
                }
                
                echo '<div class="col p-1">';
                echo '<div class="calendar-day '.($es_hoy ? 'today ' : '').$clase_beneficio.'" data-dia="'.$dia.'">';
                echo '<div class="day-number">'.$dia.'</div>';
                
                if ($datos_dia) {
                    echo '<div class="day-info">';
                    echo '<div class="d-flex justify-content-between small">';
                    echo '<span class="badge bg-success">'.$datos_dia['compras'].'</span>';
                    echo '<span class="badge bg-danger">'.$datos_dia['ventas'].'</span>';
                    echo '</div>';
                    
                    if ($datos_dia['beneficio'] != 0) {
                        $signo = $datos_dia['beneficio'] > 0 ? '+' : '';
                        $clase = $datos_dia['beneficio'] > 0 ? 'text-success' : 'text-danger';
                        echo '<div class="day-benefit '.$clase.'">'.$signo.number_format($datos_dia['beneficio'], 2).'</div>';
                    }
                    
                    echo '</div>';
                }
                
                echo '</div></div>';
                
                // Nueva fila cada 7 días
                if (($dia + $primer_dia_semana - 1) % 7 == 0) {
                    echo '</div><div class="row g-1">';
                }
            }
            ?>
        </div>
    </div>
    
    <div class="card-footer bg-white small">
        <div class="d-flex justify-content-center">
            <div class="legend-item mx-2">
                <span class="legend-color bg-success"></span>
                <span class="legend-text">Día positivo</span>
            </div>
            <div class="legend-item mx-2">
                <span class="legend-color bg-danger"></span>
                <span class="legend-text">Día negativo</span>
            </div>
            <div class="legend-item mx-2">
                <span class="legend-color bg-primary"></span>
                <span class="legend-text">Día neutral</span>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    border-radius: 6px;
    padding: 6px;
    height: 100%;
    min-height: 80px;
    background-color: #f8f9fa;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    border: 1px solid #e9ecef;
}

.calendar-day.empty {
    background: transparent;
    border: none;
}

.calendar-day.today {
    border: 2px solid #0d6efd;
    background-color: #e7f1ff;
}

.calendar-day:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.day-profit {
    border-bottom: 3px solid #28a745;
}

.day-loss {
    border-bottom: 3px solid #dc3545;
}

.day-neutral {
    border-bottom: 3px solid #0d6efd;
}

.day-number {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 0.9rem;
}

.day-info {
    margin-top: auto;
}

.day-benefit {
    font-weight: 600;
    font-size: 0.8rem;
    margin-top: 3px;
    text-align: center;
}

.legend-item {
    display: flex;
    align-items: center;
}

.legend-color {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 6px;
}
</style>

<script>
$(document).ready(function() {
    // Navegación entre meses
    $('.prev-month').click(function() {
        let mes = <?= $mes_actual ?>;
        let ano = <?= $año_actual ?>;
        
        mes--;
        if (mes < 1) {
            mes = 12;
            ano--;
        }
        
        window.location.href = `?mes=${mes}&ano=${ano}`;
    });
    
    $('.next-month').click(function() {
        let mes = <?= $mes_actual ?>;
        let ano = <?= $año_actual ?>;
        
        mes++;
        if (mes > 12) {
            mes = 1;
            ano++;
        }
        
        window.location.href = `?mes=${mes}&ano=${ano}`;
    });
    
    // Botón Hoy
    $('.btn-hoy').click(function() {
        window.location.href = `?mes=${new Date().getMonth() + 1}&ano=${new Date().getFullYear()}`;
    });
    
    // Click en día con operaciones
    $(document).on('click', '.calendar-day:not(.empty)', function() {
        const dia = $(this).data('dia');
        const mes = <?= $mes_actual ?>;
        const ano = <?= $año_actual ?>;
        
        // Aquí puedes implementar lo que ocurre al hacer clic
        console.log(`Mostrar operaciones del ${dia}/${mes}/${ano}`);
        // Ejemplo: abrir un modal con las operaciones de ese día
    });
});
</script>