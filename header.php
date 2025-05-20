<?php
session_start();
require 'conexion.php'; // Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Obtener el nombre completo del administrador desde la sesión
$nombreCompleto = $_SESSION['nombreCompleto'];

// Obtener la imagen de perfil del usuario desde la sesión
$imagenPerfil = isset($_SESSION['imagenPerfil']) ? $_SESSION['imagenPerfil'] : 'ruta-a-imagen-por-defecto.jpg'; // Reemplaza 'ruta-a-imagen-por-defecto.jpg' con la ruta de una imagen de perfil por defecto

// Consultar la ruta de la foto de perfil en la tabla foto_perfil
$idUsuario = $_SESSION['idUsuario'];
$query = "SELECT rutaFoto FROM foto_perfil WHERE idUsuario = $idUsuario";

$resultado = $conexion->query($query);

if ($resultado && $resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();
    $rutaFoto = $row['rutaFoto'];
} else {
    $rutaFoto = 'ruta-a-imagen-por-defecto.jpg'; // Reemplaza 'ruta-a-imagen-por-defecto.jpg' con la ruta de una imagen de perfil por defecto
}

// Procesar el formulario de edición de foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nuevaFoto'])) {
    $fotoTmp = $_FILES['nuevaFoto']['tmp_name'];

    // Guardar la nueva foto de perfil en el directorio adecuado (por ejemplo, 'fotos_perfil/')
    $rutaFoto = 'foto_perfil/' . $_SESSION['idUsuario'] . '.jpg';
    move_uploaded_file($fotoTmp, $rutaFoto);

    // Actualizar la ruta de la imagen de perfil en la sesión
    $_SESSION['imagenPerfil'] = $rutaFoto;

    // Redirigir a la página actual para reflejar los cambios de la foto de perfil
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!-- Resto del código HTML -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="dashboard_admin.php">Dashboard-APP</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class=""></a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="#">Widget 2</a>
      </li>
      <!-- Agrega más elementos de menú según sea necesario -->
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <div class="dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" id="perfilDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="<?php echo $imagenPerfil; ?>" alt="Foto de perfil" class="rounded-circle" style="width: 40px; height: 40px;">
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="perfilDropdown">
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editarFotoModal">Editar foto</a>
            <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</nav>

<!-- Modal para editar foto -->
<div class="modal fade" id="editarFotoModal" tabindex="-1" role="dialog" aria-labelledby="editarFotoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarFotoModalLabel">Editar foto de perfil</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Formulario para editar la foto de perfil -->
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
</div>

<!-- Modal para registro de usuario -->
<div class="modal fade" id="registroUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="registroUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registroUsuarioModalLabel">Crear nuevo usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php
          include_once "registro_usuario.php";
        ?>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
