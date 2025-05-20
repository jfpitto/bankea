<?php
session_start();
require 'conexion.php'; // Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar el formulario de guardado de categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']) && isset($_POST['descripcion'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    // Insertar la categoría en la base de datos
    $query = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
    $resultado = $conexion->query($query);

    if ($resultado) {
        // Redirigir a la página de listar categorías con un mensaje de éxito
        header('Location: listar_categorias.php?mensaje=La categoría se ha guardado correctamente');
        exit();
    } else {
        // Redirigir a la página de listar categorías con un mensaje de error
        header('Location: listar_categorias.php?error=Hubo un error al guardar la categoría');
        exit();
    }
} else {
    // Redirigir a la página de listar categorías si se accede directamente a este archivo sin enviar el formulario
    header('Location: listar_categorias.php');
    exit();
}
?>
