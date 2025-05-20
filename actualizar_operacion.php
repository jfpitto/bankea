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

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$valido = strtolower(trim($_POST['valido'] ?? ''));

if ($valido === 'si') {
    $valido = 'sí'; // Ajustar a ENUM correcto
}

if (!$id || !in_array($valido, ['sí', 'no'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Ya no validamos por id_usuario
    $check = $conexion->prepare("SELECT id FROM operaciones WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El registro no existe']);
        exit;
    }

    // Actualizar sin validar usuario
    $stmt = $conexion->prepare("UPDATE operaciones SET valido = ? WHERE id = ?");
    $stmt->bind_param("si", $valido, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hubo cambios en el registro']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
