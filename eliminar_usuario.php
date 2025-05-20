<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el usuario de la base de datos
    $query = "DELETE FROM usuarios WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        echo "El usuario ha sido eliminado correctamente.";
    } else {
        echo "Error al eliminar el usuario: " . mysqli_error($conexion);
    }
} else {
    echo "Método no permitido o ID de usuario no proporcionado.";
}

mysqli_close($conexion);
?>
