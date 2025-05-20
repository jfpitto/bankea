<?php
require 'conexion.php';

// Función para obtener datos del mes
function obtenerDatosMes($conexion, $mes, $año) {
    $primer_dia = "$año-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
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

// Obtener mes y año actuales o desde GET
$mes_actual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$año_actual = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
$mes_actual = max(1, min(12, $mes_actual));
$año_actual = max(2020, min(2100, $año_actual));

// Obtener datos del mes
$datos_mes = obtenerDatosMes($conexion, $mes_actual, $año_actual);
$nombre_mes = date('F', mktime(0, 0, 0, $mes_actual, 1));
$dias_mes = date('t', strtotime("$año_actual-$mes_actual-01"));
$primer_dia_semana = date('N', strtotime("$año_actual-$mes_actual-01")); // 1 (Lun) - 7 (Dom)

// Array para meses en español
$meses_es = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Calendario de Operaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        cursor: pointer;
    }

    .calendar-day.empty {
        background: transparent;
        border: none;
        cursor: default;
    }

    .calendar-day.today {
        border: 2px solid #0d6efd;
        background-color: #e7f1ff;
    }

    .calendar-day:hover:not(.empty) {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .day-profit {
        border-bottom: 4px solid #198754; /* Bootstrap success */
        background-color: #d1e7dd;
    }

    .day-loss {
        border-bottom: 4px solid #dc3545; /* Bootstrap danger */
        background-color: #f8d7da;
    }

    .day-neutral {
        border-bottom: 4px solid #0d6efd; /* Bootstrap primary */
        background-color: #cfe2ff;
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
        width: 15px;
        height: 15px;
        border-radius: 3px;
        margin-right: 6px;
    }
    </style>
</head>
<body>
<div class="container my-4">

    <h3 class="mb-4 text-center">Calendario de Operaciones</h3>

    <!-- Selector mes y año -->
    <form method="GET" class="d-flex justify-content-center align-items-center mb-3 gap-3">
        <select name="mes" class="form-select w-auto">
            <?php foreach ($meses_es as $key => $mes_nombre): ?>
                <option value="<?= $key + 1 ?>" <?= ($mes_actual == $key + 1) ? 'selected' : '' ?>><?= $mes_nombre ?></option>
            <?php endforeach; ?>
        </select>

        <select name="ano" class="form-select w-auto">
            <?php 
            for ($y = 2020; $y <= 2100; $y++) {
                $selected = ($año_actual == $y) ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
        </select>

        <button type="submit" class="btn btn-primary">Ver</button>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary prev-month me-2" type="button">
                    &lt;
                </button>
                <h5 class="mb-0 fw-bold">
                    <?= $meses_es[$mes_actual-1] ?> <?= $año_actual ?>
                </h5>
                <button class="btn btn-sm btn-outline-secondary next-month ms-2" type="button">
                    &gt;
                </button>
            </div>
            <button class="btn btn-sm btn-primary btn-hoy" type="button">
                Hoy
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
                // Días vacíos al inicio para alinear al día de la semana
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
                    echo '<div class="calendar-day '.($es_hoy ? 'today ' : '').$clase_beneficio.'" data-dia="'.$dia.'" tabindex="0" role="button" aria-label="Operaciones del día '.$dia.'">';
                    echo '<div class="day-number">'.$dia.'</div>';
                    
                    if ($datos_dia) {
                        echo '<div class="day-info">';
                        echo '<div class="d-flex justify-content-between small">';
                        echo '<span class="badge bg-success" title="Compras">'.$datos_dia['compras'].'</span>';
                        echo '<span class="badge bg-danger" title="Ventas">'.$datos_dia['ventas'].'</span>';
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

                // Rellenar la última fila con vacíos si es necesario
                $ultimo_dia_semana = ($dias_mes + $primer_dia_semana - 1) % 7;
                if ($ultimo_dia_semana != 0) {
                    for ($i = $ultimo_dia_semana + 1; $i <= 7; $i++) {
                        echo '<div class="col p-1"><div class="calendar-day empty"></div></div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="mt-3 d-flex justify-content-center gap-4 small text-muted">
        <div class="legend-item"><span class="legend-color" style="background-color:#198754;"></span> Beneficio positivo</div>
        <div class="legend-item"><span class="legend-color" style="background-color:#dc3545;"></span> Pérdida</div>
        <div class="legend-item"><span class="legend-color" style="background-color:#0d6efd;"></span> Beneficio cero</div>
        <div class="legend-item"><span class="legend-color border border-2 border-primary"></span> Día actual</div>
    </div>
</div>

<script>
$(function() {
    // Navegar meses con botones
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

    $('.btn-hoy').click(function() {
        window.location.href = `?mes=<?= date('n') ?>&ano=<?= date('Y') ?>`;
    });
});
</script>

</body>
</html>
