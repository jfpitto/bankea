<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nuevaContrasena = $_POST['nueva_contrasena'];

    // Actualizar la contraseña del usuario en la base de datos
    $query = "UPDATE usuarios SET contrasena = '$nuevaContrasena' WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        echo "La contraseña del usuario se ha actualizado correctamente.";
    } else {
        echo "Error al actualizar la contraseña del usuario: " . mysqli_error($conexion);
    }
} else {
    echo "Método no permitido.";
}

mysqli_close($conexion);
?>
