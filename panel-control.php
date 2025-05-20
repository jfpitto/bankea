<?php
$host = 'localhost';
$user = 'planetmusic';
$password = 'planetmusic2024%';
$database = 'planetmusic';

// Crear conexión
$conexion = new mysqli($host, $user, $password, $database);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Inicializar el estado de mantenimiento
$mantenimientoActivado = false;

// Procesar el formulario de habilitar/deshabilitar mantenimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estadoMantenimiento = isset($_POST['habilitar']) ? 1 : 0;

    // Actualizar la configuración en la base de datos
    $stmt = $conexion->prepare("UPDATE configuracion SET mantenimiento = ?");
    $stmt->bind_param("i", $estadoMantenimiento);
    $stmt->execute();
    $stmt->close();

    echo '<p>Estado de mantenimiento actualizado: ' . ($estadoMantenimiento ? 'habilitado' : 'deshabilitado') . '</p>';
}

// Obtener el estado de mantenimiento actual desde la base de datos
$result = $conexion->query("SELECT mantenimiento FROM configuracion LIMIT 1");
if ($result->num_rows > 0) {
    $fila = $result->fetch_assoc();
    $mantenimientoActivado = (bool) $fila['mantenimiento'];
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
</head>
<body>
    <h1>Panel de Control</h1>

    <form method="post">
        <label>
            <input type="checkbox" name="habilitar" <?php echo $mantenimientoActivado ? 'checked' : ''; ?>>
            Habilitar Mantenimiento
        </label>
        <br>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
