<?php
// verify_database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

try {
    // 1. Verificar que la tabla existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'operaciones'");
    if ($stmt->rowCount() === 0) {
        die("La tabla 'operaciones' no existe");
    }

    // 2. Verificar estructura de la tabla
    $stmt = $conexion->query("DESCRIBE operaciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Estructura de la tabla 'operaciones'</h3>";
    echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 3. Verificar permisos de inserción
    try {
        $testData = [
            'fecha_hora_apertura' => date('Y-m-d H:i:s'),
            'simbolo' => 'TEST',
            'tipo' => 'buy',
            'volumen' => 0.1,
            'precio_apertura' => 100,
            'usuario_id' => 1
        ];
        
        $sql = "INSERT INTO operaciones (fecha_hora_apertura, simbolo, tipo, volumen, precio_apertura, usuario_id) 
                VALUES (:fecha, :simbolo, :tipo, :volumen, :precio, :usuario_id)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':fecha' => $testData['fecha_hora_apertura'],
            ':simbolo' => $testData['simbolo'],
            ':tipo' => $testData['tipo'],
            ':volumen' => $testData['volumen'],
            ':precio' => $testData['precio_apertura'],
            ':usuario_id' => $testData['usuario_id']
        ]);
        
        echo "<p style='color:green;'>Prueba de inserción exitosa</p>";
        
        // Limpiar el registro de prueba
        $conexion->query("DELETE FROM operaciones WHERE simbolo = 'TEST'");
        
    } catch (PDOException $e) {
        die("<p style='color:red;'>Error en prueba de inserción: " . $e->getMessage() . "</p>");
    }

} catch (PDOException $e) {
    die("Error al verificar la base de datos: " . $e->getMessage());
}
?>