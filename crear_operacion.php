<?php
include 'conexion.php';
header('Content-Type: application/json');

try {
    // Validar datos de entrada
    $requiredFields = ['simbolo', 'tipo', 'volumen', 'beneficio', 'fecha_hora_apertura', 'valido'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Primero eliminar temporalmente la restricción de clave foránea
    $conexion->query("ALTER TABLE operaciones DROP FOREIGN KEY operaciones_ibfk_1");

    // Preparar la consulta original
    $query = "INSERT INTO operaciones (simbolo, tipo, volumen, beneficio, fecha_hora_apertura, valido) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Bind parameters
    $stmt->bind_param(
        "ssddss", 
        $_POST['simbolo'],
        $_POST['tipo'],
        $_POST['volumen'],
        $_POST['beneficio'],
        $_POST['fecha_hora_apertura'],
        $_POST['valido']
    );
    
    // Ejecutar
    if ($stmt->execute()) {
        // Restaurar la restricción después de la inserción
        $conexion->query("ALTER TABLE operaciones ADD CONSTRAINT operaciones_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios(id)");
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
} catch (Exception $e) {
    // Restaurar la restricción en caso de error
    if (isset($conexion)) {
        $conexion->query("ALTER TABLE operaciones ADD CONSTRAINT operaciones_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios(id)");
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>