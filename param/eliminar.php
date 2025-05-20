<?php
session_start();
require '../conexion.php'; // Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    // Eliminar la categoría seleccionada
    $idCategoria = $_POST['id'];

    // Eliminar la categoría de la base de datos
    $query = "DELETE FROM categorias WHERE id = '$idCategoria'";
    $resultado = $conexion->query($query);

    if ($resultado) {
        // Redirigir a la página actual después de la eliminación exitosa
        header('Location: categoria.php');
        exit();
    } else {
        // Mostrar mensaje de error en caso de fallo en la eliminación
        $errorMensaje = 'Hubo un error al eliminar la categoría';
    }
} else {
    // Redirigir a la página de categorías en caso de solicitud incorrecta
    header('Location: categoria.php');
    exit();
}
?>
