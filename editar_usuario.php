<?php
include("header.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Editar Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .form-group {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mt-3">Editar Usuario</h1>

    <?php
    require_once 'conexion.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener los datos enviados por el formulario de edición
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo'];
        $cedula = $_POST['cedula'];
        $telefono = $_POST['telefono'];
        $perfil_id = $_POST['perfil'];
        $usuario = $_POST['usuario'];
        $contrasena = $_POST['contrasena'];

        // Verificar si se ingresó una nueva contraseña
        if (!empty($contrasena)) {
            // Hash de la nueva contraseña
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Realizar la actualización en la base de datos, incluyendo la nueva contraseña
            $query = "UPDATE usuarios SET nombre_completo = '$nombre', apellido_completo = '$apellido', correo = '$correo', cedula = '$cedula', telefono = '$telefono', perfil_id = '$perfil_id', usuario = '$usuario', contrasena = '$contrasenaHash' WHERE id = $id";
        } else {
            // Si no se ingresó una nueva contraseña, mantener la contraseña actual en la base de datos
            $query = "UPDATE usuarios SET nombre_completo = '$nombre', apellido_completo = '$apellido', correo = '$correo', cedula = '$cedula', telefono = '$telefono', perfil_id = '$perfil_id', usuario = '$usuario' WHERE id = $id";
        }

        $result = mysqli_query($conexion, $query);

        if ($result) {
            echo "<div class='alert alert-success mt-3'>Usuario actualizado correctamente.</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Error al actualizar el usuario.</div>";
        }
    }

    // Obtener el ID del usuario a editar
    $id = $_GET['id'];

    // Consultar el usuario específico por su ID
    $query = "SELECT usuarios.id, usuarios.nombre_completo, usuarios.apellido_completo, usuarios.correo, usuarios.cedula, usuarios.telefono, usuarios.perfil_id, usuarios.usuario, perfiles.nombre AS perfil FROM usuarios INNER JOIN perfiles ON usuarios.perfil_id = perfiles.id WHERE usuarios.id = $id";
    $result = mysqli_query($conexion, $query);
    $row = mysqli_fetch_assoc($result);
    ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" value="<?php echo $row['nombre_completo']; ?>" required>
      </div>
      <div class="form-group">
        <label for="apellido">Apellido:</label>
        <input type="text" class="form-control form-control-sm" id="apellido" name="apellido" value="<?php echo $row['apellido_completo']; ?>" required>
      </div>
      <div class="form-group">
        <label for="correo">Correo:</label>
        <input type="email" class="form-control form-control-sm" id="correo" name="correo" value="<?php echo $row['correo']; ?>" required>
      </div>
      <div class="form-group">
        <label for="cedula">Cédula:</label>
        <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" value="<?php echo $row['cedula']; ?>" required>
      </div>
      <div class="form-group">
        <label for="telefono">Teléfono:</label>
        <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" value="<?php echo $row['telefono']; ?>" required>
      </div>
      <div class="form-group">
        <label for="perfil">Perfil:</label>
        <select class="form-control form-control-sm" id="perfil" name="perfil" required>
          <?php
          // Consultar la tabla de perfiles
          $query_perfiles = "SELECT * FROM perfiles";
          $result_perfiles = mysqli_query($conexion, $query_perfiles);

          while ($perfil = mysqli_fetch_assoc($result_perfiles)) {
              $selected = ($perfil['id'] == $row['perfil_id']) ? "selected" : "";
              echo "<option value='{$perfil['id']}' $selected>{$perfil['nombre']}</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="usuario">Usuario:</label>
        <input type="text" class="form-control form-control-sm" id="usuario" name="usuario" value="<?php echo $row['usuario']; ?>" required>
      </div>
      <div class="form-group">
        <label for="contrasena">Contraseña:</label>
        <div class="input-group">
          <input type="password" class="form-control form-control-sm" id="contrasena" name="contrasena" placeholder="Dejar en blanco para mantener la contraseña actual">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
          </div>
        </div>
      </div>
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
      <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
      <a href="dashboard_admin.php" class="btn btn-secondary btn-sm">Volver</a>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
  <script>
    // Función para mostrar u ocultar la contraseña
    function togglePassword() {
      var passwordInput = document.getElementById("contrasena");
      var toggleBtn = document.getElementById("togglePassword");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleBtn.innerHTML = "<i class='fas fa-eye-slash'></i>";
      } else {
        passwordInput.type = "password";
        toggleBtn.innerHTML = "<i class='fas fa-eye'></i>";
      }
    }

    // Asignar el evento de clic al botón de mostrar/ocultar contraseña
    var toggleBtn = document.getElementById("togglePassword");
    toggleBtn.addEventListener("click", togglePassword);
  </script>
  
</body>
</html>
