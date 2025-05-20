<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];

    // Actualizar los datos del usuario en la base de datos
    $query = "UPDATE usuarios SET nombre_completo = '$nombre', apellido_completo = '$apellido', correo = '$correo' WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        echo "Los datos del usuario se han actualizado correctamente.";
    } else {
        echo "Error al actualizar los datos del usuario: " . mysqli_error($conexion);
    }
} else {
    echo "Método no permitido.";
}

mysqli_close($conexion);
?>
