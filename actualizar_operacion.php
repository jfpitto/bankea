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
$valido = strtolower(trim($_POST['valido'] ?? ''));

// Validar que el valor sea 'sí' o 'no'
if (!$id || !in_array($valido, ['sí', 'no'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos: se espera "sí" o "no"']);
    exit;
}

try {
    // Verificar que el registro existe y pertenece al usuario
    $check = $conexion->prepare("SELECT id FROM operaciones WHERE id = ? AND id_usuario = ?");
    $check->bind_param("ii", $id, $_SESSION['idUsuario']);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El registro no existe o no pertenece al usuario']);
        exit;
    }

    // Ejecutar actualización
    $stmt = $conexion->prepare("UPDATE operaciones SET valido = ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("sii", $valido, $id, $_SESSION['idUsuario']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
    } else {
        echo json_encode(['success' => true, 'message' => 'No se realizaron cambios (el valor ya era el mismo)']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
