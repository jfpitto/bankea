<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;
$valido = $_POST['valido'] ?? null;

if (!$id || !in_array($valido, ['SI', 'NO'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = $conexion->prepare("UPDATE operaciones SET valido = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sii", $valido, $id, $_SESSION['idUsuario']);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se afectaron filas']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}