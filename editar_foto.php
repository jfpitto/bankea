<?php
session_start();
require 'conexion.php'; // Asegúrate de incluir el archivo de conexión

// Crear conexión
$conexion = mysqli_connect($host, $user, $password, $database);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || ($_SESSION['perfilNombre'] !== 'admin' && $_SESSION['perfilNombre'] !== 'user')) {
    header('Location: login.php');
    exit();
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha seleccionado un archivo
    if (isset($_FILES['nuevaFoto']) && $_FILES['nuevaFoto']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['nuevaFoto'];

        // Verificar que el archivo sea una imagen
        $mimeTypes = ['image/jpeg', 'image/png'];
        if (in_array($archivo['type'], $mimeTypes)) {
            // Mover el archivo a una ubicación deseada
            $rutaDestino = 'foto_perfil/' . $_SESSION['idUsuario'] . '.jpg'; // Reemplaza 'ruta/donde/guardar/la/foto/' por la ruta deseada
            move_uploaded_file($archivo['tmp_name'], $rutaDestino);

            // Actualizar la ruta de la imagen en la sesión
            $_SESSION['imagenPerfil'] = $rutaDestino;

            // Actualizar la ruta de la imagen en la tabla 'usuarios'
            $idUsuario = $_SESSION['idUsuario'];
            $rutaFoto = mysqli_real_escape_string($conexion, $rutaDestino);

            $queryUpdate = "UPDATE usuarios SET imagen_perfil = '$rutaFoto' WHERE id = $idUsuario";
            $resultado = $conexion->query($queryUpdate);

            if ($resultado) {
                // Redirigir al dashboard o a otra página de éxito
                if ($_SESSION['perfilNombre'] === 'admin') {
                    header('Location: dashboard_admin.php');
                } else {
                    header('Location: dashboard_usuario.php');
                }
                exit();
            } else {
                $error = 'Error al guardar la foto en la tabla usuarios.';
            }
        } else {
            $error = 'El archivo seleccionado no es una imagen válida.';
        }
    } else {
        $error = 'No se ha seleccionado un archivo.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Editar foto de perfil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <div class="row mt-4">
      <div class="col-md-6 offset-md-3">
        <h2>Editar foto de perfil</h2>
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="editar_foto.php" enctype="multipart/form-data">
          <div class="form-group">
            <label for="nuevaFoto">Seleccionar nueva foto:</label>
            <input type="file" class="form-control-file" id="nuevaFoto" name="nuevaFoto">
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
