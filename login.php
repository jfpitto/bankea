<?php
session_start();
require_once 'conexion.php';

// Obtener la ruta del logo desde la base de datos
$queryLogo = "SELECT ruta_logo FROM logos LIMIT 1";
$resultLogo = mysqli_query($conexion, $queryLogo);

if ($resultLogo && mysqli_num_rows($resultLogo) === 1) {
    $rowLogo = mysqli_fetch_assoc($resultLogo);
    $rutaLogo = $rowLogo['ruta_logo'];
} else {
    // Ruta predeterminada del logo en caso de que no se encuentre en la base de datos
    $rutaLogo = 'foto_login/loginmaster.png';
}


// Verificar si el usuario ya ha iniciado sesión y redirigir al panel correspondiente
if (isset($_SESSION['idUsuario'])) {
    if ($_SESSION['perfilNombre'] === 'user') {
        header('Location: dashboard_usuario.php');
    } elseif ($_SESSION['perfilNombre'] === 'admin') {
        header('Location: dashboard_admin.php');
    }    elseif ($_SESSION['perfilNombre'] === 'finanzas') {
        header('Location: dashboard_finanzas.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'conexion.php';

    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Consultar la base de datos para obtener el usuario y la contraseña
    $query = "SELECT id, nombre_completo, perfil_id, contrasena, imagen_perfil FROM usuarios WHERE usuario = '$usuario'";
    $result = mysqli_query($conexion, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $idUsuario = $row['id'];
        $nombreCompleto = $row['nombre_completo'];
        $perfilId = $row['perfil_id'];
        $contrasenaHash = $row['contrasena'];
        $imagenPerfil = $row['imagen_perfil']; // Obtener la ruta de la imagen de perfil

        // Verificar la contraseña
        if (password_verify($contrasena, $contrasenaHash)) {
            // Guardar la información del usuario en la sesión
            $_SESSION['idUsuario'] = $idUsuario;
            $_SESSION['nombreCompleto'] = $nombreCompleto;
            $_SESSION['imagenPerfil'] = $imagenPerfil; // Guardar la ruta de la imagen de perfil

            // Obtener el nombre del perfil desde la base de datos
            $queryPerfil = "SELECT nombre FROM perfiles WHERE id = $perfilId";
            $resultPerfil = mysqli_query($conexion, $queryPerfil);

            if ($resultPerfil && mysqli_num_rows($resultPerfil) === 1) {
                $rowPerfil = mysqli_fetch_assoc($resultPerfil);
                $perfilNombre = $rowPerfil['nombre'];

                $_SESSION['perfilNombre'] = $perfilNombre;

                // Consultar la tabla de perfiles_paneles para obtener la información dinámicamente
                $queryPerfilesPaneles = "SELECT perfil_nombre, panel_ruta FROM perfiles_paneles";
                $resultPerfilesPaneles = mysqli_query($conexion, $queryPerfilesPaneles);

                if ($resultPerfilesPaneles) {
                    $perfilesPaneles = array();
                    while ($rowPerfilesPaneles = mysqli_fetch_assoc($resultPerfilesPaneles)) {
                        $perfilesPaneles[$rowPerfilesPaneles['perfil_nombre']] = $rowPerfilesPaneles['panel_ruta'];
                    }

                    // Verificar si el perfil existe en el arreglo y redirigir al panel correspondiente
                    if (array_key_exists($perfilNombre, $perfilesPaneles)) {
                        $panel = $perfilesPaneles[$perfilNombre];
                        header('Location: ' . $panel);
                    } else {
                        echo "Perfil no reconocido.";
                    }
                } else {
                    echo "Error al obtener la información de perfiles y paneles.";
                }
            } else {
                echo "Error al obtener el perfil de usuario.";
            }
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elegant Dashboard | Sign In</title>
  <!-- Favicon -->
  <link rel="shortcut icon" href="./img/svg/logo.svg" type="image/x-icon">
  <!-- Custom styles -->
  <link rel="stylesheet" href="./css/style.min.css">
</head>
<body>
  <div class="layer"></div>
  <main class="page-center">
    <article class="sign-up">
      <img src="<?php echo $rutaLogo; ?>" alt="Logo" class="logo">
      <h1 class="sign-up__title">Iniciar sesión</h1>
      <form id="login-form" class="sign-up-form form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <label class="form-label-wrapper">
          <p class="form-label">Usuario</p>
          <input class="form-input" type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
        </label>
        <label class="form-label-wrapper">
          <p class="form-label">Contraseña</p>
          <input class="form-input" type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
        </label>
        <a class="link-info forget-link" href="##">¿Olvidaste tu contraseña?</a>
        <label class="form-checkbox-wrapper">
          <input class="form-checkbox" type="checkbox">
          <span class="form-checkbox-label">Recordarme</span>
        </label>
        <button type="submit" class="form-btn primary-default-btn transparent-btn" id="login-btn">Iniciar sesión</button>
        <div class="progress">
          <div class="progress-bar" role="progressbar"></div>
        </div>
       
        <div class="g-signin2" data-onsuccess="onSignIn"></div>
      </form>
    </article>
  </main>
  

  <script src="https://apis.google.com/js/platform.js" async defer></script>
  <script>
    function onSignIn(googleUser) {
      var profile = googleUser.getBasicProfile();
      var id_token = googleUser.getAuthResponse().id_token;

      $.ajax({
        url: 'verificar_google.php',
        type: 'POST',
        data: { id_token: id_token },
        success: function(response) {
          console.log(response);
          if (response === "Éxito") {
            window.location.href = 'dashboard.php';
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }
  </script>
  
  <!-- Chart library -->
  <script src="./plugins/chart.min.js"></script>
  <!-- Icons library -->
  <script src="plugins/feather.min.js"></script>
  <!-- Custom scripts -->
  <script src="js/script.js"></script>
</body>
</html>
