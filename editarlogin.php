<?php
session_start();
require_once 'conexion.php';

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
    if (isset($_FILES['nuevoLogo']) && $_FILES['nuevoLogo']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['nuevoLogo'];

        // Verificar que el archivo sea una imagen
        $mimeTypes = ['image/jpeg', 'image/png'];
        if (in_array($archivo['type'], $mimeTypes)) {
            // Eliminar el logo actual si existe
            $queryDelete = "DELETE FROM logos";
            $resultadoDelete = $conexion->query($queryDelete);

            if (!$resultadoDelete) {
                $error = 'Error al eliminar el logo actual: ' . mysqli_error($conexion);
            } else {
                // Mover el archivo a la carpeta deseada
                $rutaDestino = 'foto_login/' . $archivo['name']; // Reemplaza 'ruta/donde/guardar/la/foto/' por la ruta deseada
                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    // Actualizar la ruta del logo en la base de datos
                    $rutaLogo = mysqli_real_escape_string($conexion, $rutaDestino);

                    $queryInsert = "INSERT INTO logos (ruta_logo) VALUES ('$rutaLogo')";
                    $resultado = $conexion->query($queryInsert);

                    if ($resultado) {
                        $success = 'El logo se ha guardado correctamente.';
                    } else {
                        $error = 'Error al guardar el logo en la base de datos: ' . mysqli_error($conexion);
                    }
                } else {
                    $error = 'Error al mover el archivo a la carpeta deseada.';
                }
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
  <title>Editar Logo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <div class="row mt-4">
      <div class="col-md-6 offset-md-3">
        <h2>Editar Logo</h2>
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="editarlogin.php" enctype="multipart/form-data">
          <?php
            // Verificar si existe un logo en la base de datos
            $querySelect = "SELECT ruta_logo FROM logos LIMIT 1";
            $resultadoSelect = $conexion->query($querySelect);
            $hayLogo = $resultadoSelect->num_rows > 0;
          ?>
          <?php if ($hayLogo): ?>
            <div class="form-group">
              <label>Logo Actual:</label>
              <img src="<?php echo $resultadoSelect->fetch_assoc()['ruta_logo']; ?>" alt="Logo actual" width="150">
            </div>
          <?php endif; ?>
          <div class="form-group">
            <label for="nuevoLogo">Seleccionar nuevo logo:</label>
            <input type="file" class="form-control-file" id="nuevoLogo" name="nuevoLogo">
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a class="btn btn-secondary" href="dashboard_admin.php">Regresar</a>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
