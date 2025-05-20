<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$valido = strtolower(trim($_POST['valido'] ?? ''));

if (!$id || !in_array($valido, ['sí', 'no'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// DEBUG: Mostrar datos recibidos
error_log("ID recibido: " . $id);
error_log("Usuario sesión: " . $_SESSION['idUsuario']);
error_log("Valido recibido: " . $valido);

try {
    // Verifica si el registro existe con ese id y pertenece al usuario
    $check = $conexion->prepare("SELECT id_usuario FROM operaciones WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El ID no existe en la base de datos.']);
        exit;
    }

    $row = $result->fetch_assoc();
    $id_usuario_bd = $row['id_usuario'];

    // Comparar con el usuario en sesión
    if ($id_usuario_bd != $_SESSION['idUsuario']) {
        echo json_encode([
            'success' => false,
            'message' => 'El registro existe, pero pertenece a otro usuario.',
            'id_usuario_bd' => $id_usuario_bd,
            'id_usuario_sesion' => $_SESSION['idUsuario']
        ]);
        exit;
    }

    // Hacer el update
    $stmt = $conexion->prepare("UPDATE operaciones SET valido = ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("sii", $valido, $id, $_SESSION['idUsuario']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Actualización exitosa']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Sin cambios. Valor ya era igual.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
