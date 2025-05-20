<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar datos
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$valido = $_POST['valido'] ?? null;

if (!$id || !in_array($valido, ['SI', 'NO'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Preparar y ejecutar la consulta de actualización
    $stmt = $conexion->prepare("UPDATE operaciones SET valido = ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("sii", $valido, $id, $_SESSION['idUsuario']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el registro o no hubo cambios']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
