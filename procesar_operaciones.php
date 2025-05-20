<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';
require 'vendor/autoload.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido. Se requiere POST");
    }

    if (!isset($_FILES['archivoOperaciones']) || $_FILES['archivoOperaciones']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No se recibió ningún archivo válido");
    }

    $file = $_FILES['archivoOperaciones'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['xlsx', 'xls', 'csv'];

    if (!in_array($ext, $allowed)) {
        throw new Exception("Formato no soportado. Use Excel (.xlsx, .xls) o CSV");
    }

    // Cargar archivo
    if ($ext === 'xlsx') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    } elseif ($ext === 'xls') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    }

    $spreadsheet = $reader->load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (count($rows) <= 1) {
        throw new Exception("El archivo no contiene datos válidos");
    }

    // Identificar dónde comienzan las órdenes (para ignorar esa sección)
    $ordersStart = null;
    foreach ($rows as $index => $row) {
        if (isset($row[0]) && strpos($row[0], 'Órdenes') !== false) {
            $ordersStart = $index;
            break;
        }
    }

    // Procesar solo las operaciones (antes de la sección de órdenes)
    $dataRows = ($ordersStart !== null) ? array_slice($rows, 1, $ordersStart - 1) : array_slice($rows, 1);

    // Preparar consultas SQL
    $sql = "INSERT INTO operaciones (
                fecha_hora_apertura, posicion, simbolo, tipo, volumen, precio, 
                stop_loss, take_profit, fecha_hora_cierre, precio_cierre, 
                comision, swap, beneficio, valido, id_usuario
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    $insertados = 0;
    $errores = [];
    $idUsuario = $_SESSION['idUsuario'];

    foreach ($dataRows as $index => $row) {
        try {
            // Saltar filas vacías o incompletas
            if (empty($row[0]) || empty($row[2]) || empty($row[3])) {
                continue;
            }

            // Convertir fecha de apertura (formato YYYY.MM.DD HH:MM:SS)
            $fechaApertura = DateTime::createFromFormat('Y.m.d H:i:s', trim($row[0]));
            if (!$fechaApertura) {
                throw new Exception("Fecha apertura inválida en fila " . ($index + 2) . ": " . $row[0]);
            }
            $fechaApertura = $fechaApertura->format('Y-m-d H:i:s');

            // Convertir fecha de cierre (si existe)
            $fechaCierre = null;
            if (!empty($row[8])) {
                $fechaCierre = DateTime::createFromFormat('Y.m.d H:i:s', trim($row[8]));
                if ($fechaCierre) {
                    $fechaCierre = $fechaCierre->format('Y-m-d H:i:s');
                }
            }

            // Preparar datos
            $posicion = !empty($row[1]) ? trim($row[1]) : null;
            $simbolo = trim($row[2]);
            $tipo = strtolower(trim($row[3]));
            $volumen = !empty($row[4]) ? floatval($row[4]) : 0;
            $precio = !empty($row[5]) ? floatval($row[5]) : 0;
            $stopLoss = !empty($row[6]) ? floatval($row[6]) : null;
            $takeProfit = !empty($row[7]) ? floatval($row[7]) : null;
            $precioCierre = !empty($row[9]) ? floatval($row[9]) : null;
            $comision = !empty($row[10]) ? floatval($row[10]) : 0;
            $swap = !empty($row[11]) ? floatval($row[11]) : 0;
            $beneficio = !empty($row[12]) ? floatval($row[12]) : 0;
            $valido = "sí";

            // Validaciones básicas
            if (!in_array($tipo, ['buy', 'sell'])) {
                throw new Exception("Tipo inválido en fila " . ($index + 2) . ": $tipo");
            }

            if ($volumen <= 0) {
                throw new Exception("Volumen inválido en fila " . ($index + 2) . ": $volumen");
            }

            // Insertar en la base de datos
            $stmt->bind_param(
                "ssssdddddsdddsi",
                $fechaApertura,
                $posicion,
                $simbolo,
                $tipo,
                $volumen,
                $precio,
                $stopLoss,
                $takeProfit,
                $fechaCierre,
                $precioCierre,
                $comision,
                $swap,
                $beneficio,
                $valido,
                $idUsuario
            );

            if ($stmt->execute()) {
                $insertados++;
            } else {
                throw new Exception("Error al insertar fila " . ($index + 2) . ": " . $stmt->error);
            }
        } catch (Exception $e) {
            $errores[] = $e->getMessage();
        }
    }

    echo json_encode([
        'success' => $insertados > 0,
        'message' => "Archivo procesado. Registros insertados: $insertados",
        'errores' => $errores,
        'refresh' => true
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
        'refresh' => false
    ]);
}