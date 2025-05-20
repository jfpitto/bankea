<?php
require_once 'conexion.php';

// Verificar si se ha enviado una solicitud POST con el ID del usuario y el nuevo perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['perfil'])) {
    // Obtener el ID del usuario y el nuevo perfil
    $id = $_POST['id'];
    $nuevoPerfil = $_POST['perfil'];

    // Actualizar el perfil del usuario en la base de datos
    $query = "UPDATE usuarios SET perfil = '$nuevoPerfil' WHERE id = $id";
    $result = mysqli_query($conexion, $query);

    // Verificar si la actualización fue exitosa
    if ($result) {
        // Redirigir al usuario a la página de listado de usuarios o a otra página de tu elección
        header('Location: listar_usuario.php');
        exit();
    } else {
        echo "Error al actualizar el perfil del usuario.";
    }
} else {
    // Si se intenta acceder directamente a este archivo sin enviar una solicitud POST, redirigir a otra página
    header('Location: index.php');
    exit();
}

mysqli_close($conexion);
?>
