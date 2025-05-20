<?php

/**
 * DASHBOARD DE ADMINISTRACIÓN - ARCHIVO PRINCIPAL
 * 
 * Este archivo maneja la interfaz principal del panel de administración
 * con autenticación, gestión de perfil y funcionalidades administrativas.
 */

// 1. INICIO DE SESIÓN Y SEGURIDAD
session_start();
require 'conexion.php'; // Archivo de conexión a la base de datos

// Verificar autenticación y permisos
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
  header('Location: login.php');
  exit();
}

// 2. OBTENER DATOS DEL USUARIO
$nombreCompleto = $_SESSION['nombreCompleto'];
$imagenPerfil = isset($_SESSION['imagenPerfil']) ? $_SESSION['imagenPerfil'] : './img/avatar/avatar-illustrated-01.png';

// Consultar foto de perfil en la base de datos
$idUsuario = $_SESSION['idUsuario'];
$query = "SELECT rutaFoto FROM foto_perfil WHERE idUsuario = $idUsuario";
$resultado = $conexion->query($query);

$rutaFoto = ($resultado && $resultado->num_rows > 0)
  ? $resultado->fetch_assoc()['rutaFoto']
  : './img/avatar/avatar-illustrated-01.png';

// 3. MANEJO DE ACTUALIZACIÓN DE FOTO DE PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nuevaFoto'])) {
  $fotoTmp = $_FILES['nuevaFoto']['tmp_name'];
  $rutaFoto = 'foto_perfil/' . $_SESSION['idUsuario'] . '.jpg';
  move_uploaded_file($fotoTmp, $rutaFoto);
  $_SESSION['imagenPerfil'] = $rutaFoto;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <!-- ================================================
        META TAGS Y ENLACES A RECURSOS EXTERNOS
    ================================================== -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>bankea NESTOR ERAZO </title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="./img/svg/logo.svg" type="image/x-icon">

  <!-- Hojas de estilo -->
  <link rel="stylesheet" href="./css/style.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">

  <!-- Estilos personalizados -->
  <style>
    /* ========== ESTILOS GENERALES ========== */
    .sidebar-body-menu li a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 15px;
    }

    /* ========== ESTILOS PARA ICONOS MENU ========== */
    .sidebar-body-menu li a .icon,
    .sidebar-body-menu li i[data-feather],
    [data-feather] {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      stroke-width: 1.5;
      stroke: currentColor;
    }

    /* ========== BARRA DE NAVEGACIÓN SUPERIOR ========== */
    .main-nav-end {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* ========== BOTÓN DE TEMA OSCURO/CLARO ========== */
    .theme-switcher {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .theme-switcher .sun-icon,
    .theme-switcher .moon-icon {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      transition: opacity 0.3s ease;
    }

    .theme-switcher .moon-icon {
      opacity: 0;
    }

    .dark-mode .theme-switcher .sun-icon {
      opacity: 0;
    }

    .dark-mode .theme-switcher .moon-icon {
      opacity: 1;
    }

    /* ========== CONTENIDO PRINCIPAL ========== */
    #dashboardContent {
      padding: 10px 0;
    }

    .stat-cards-item {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <!-- ================================================
        ESTRUCTURA PRINCIPAL DEL LAYOUT
    ================================================== -->
  <div class="layer"></div>
  <a class="skip-link sr-only" href="#skip-target">Skip to content</a>

  <div class="page-flex">
    <!-- ========== BARRA LATERAL (SIDEBAR) ========== -->
    <aside class="sidebar">
      <div class="sidebar-start">
        <!-- Encabezado del sidebar -->
        <div class="sidebar-head">
          <a href="dashboard_admin.php" class="logo-wrapper" title="Home">
            <span class="sr-only">Home</span>
            <span class="icon logo" aria-hidden="true"></span>
            <div class="logo-text">
              <span class="logo-title">TRADING</span>
              <span class="logo-subtitle">Dashboard</span>
            </div>
          </a>
          <button class="sidebar-toggle transparent-btn" title="Menu" type="button">
            <span class="sr-only">Toggle menu</span>
            <span class="icon menu-toggle" aria-hidden="true"></span>
          </button>
        </div>

        <!-- Menú principal -->
        <div class="sidebar-body">
          <ul class="sidebar-body-menu">
            <li>
              <a class="active" href="dashboard_admin.php">
                <span class="icon home" aria-hidden="true"></span>
                Dashboard menu
              </a>
            </li>
            <li>
              <!-- Menú desplegable de Configuración -->
              <a class="show-cat-btn" href="##">
                <span class="icon document" aria-hidden="true"></span>
                Configuración
                <span class="category__btn transparent-btn" title="Open list">
                  <span class="sr-only">Open list</span>
                  <span class="icon arrow-down" aria-hidden="true"></span>
                </span>
              </a>
              <ul class="cat-sub-menu">
                <li>
                  <a href="#registroUsuarioModal" data-toggle="modal" data-target="#registroUsuarioModal">
                    <i data-feather="user-plus"></i>
                    Crear Usuario
                  </a>
                </li>
                <li>
                  <a href="#" id="listarUsuariosLink">
                    <i data-feather="list"></i>
                    Listar Usuarios
                  </a>
                </li>
                <li>
                  <a href="#" id="loginBtn">
                    <i data-feather="image"></i>
                    Editar logo del login
                  </a>
                </li>
                <li>
                  <a href="#" id="parametrizacionBtn">
                    <i data-feather="settings"></i>
                    Parametrización
                  </a>
                </li>
              </ul>
            </li>
            <li>
              <a href="#" id="cargaOperacionesBtn">
                <i data-feather="upload"></i>
                Cargar Operaciones
              </a>
            </li>
          </ul>

          <!-- Menú del sistema -->
          <span class="system-menu__title">system</span>
          <ul class="sidebar-body-menu">
            <li>
              <a href="logout.php">
                <i data-feather="log-out"></i>
                <span>Cerrar sesión</span>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Pie del sidebar con información del usuario -->
      <div class="sidebar-footer">
        <a href="##" class="sidebar-user">
          <span class="sidebar-user-img">
            <picture>
              <source srcset="<?php echo $imagenPerfil; ?>" type="image/webp">
              <img src="<?php echo $imagenPerfil; ?>" alt="User name">
            </picture>
          </span>
          <div class="sidebar-user-info">
            <span class="sidebar-user__title"><?php echo $nombreCompleto; ?></span>
            <span class="sidebar-user__subtitle">Administrador</span>
          </div>
        </a>
      </div>
    </aside>

    <!-- ========== CONTENIDO PRINCIPAL ========== -->
    <div class="main-wrapper">
      <!-- Barra de navegación superior -->
      <nav class="main-nav--bg">
        <div class="container main-nav">
          <div class="main-nav-start">
            <!-- Barra de búsqueda -->
            <div class="search-wrapper">
              <i data-feather="search" aria-hidden="true"></i>
              <input type="text" placeholder="Buscar..." required>
            </div>
          </div>

          <!-- Controles superiores derechos -->
          <div class="main-nav-end">
            <!-- Botón para colapsar/expandir sidebar -->
            <button class="sidebar-toggle transparent-btn" title="Menu" type="button">
              <span class="sr-only">Toggle menu</span>
              <span class="icon menu-toggle--gray" aria-hidden="true"></span>
            </button>

            <!-- Selector de tema oscuro/claro -->
            <button class="theme-switcher gray-circle-btn" type="button" title="Switch theme">
              <span class="sr-only">Switch theme</span>
              <i class="sun-icon" data-feather="sun" aria-hidden="true"></i>
              <i class="moon-icon" data-feather="moon" aria-hidden="true"></i>
            </button>

            <!-- Menú de usuario -->
            <div class="nav-user-wrapper">
              <button class="nav-user-btn dropdown-btn" title="Mi perfil" type="button">
                <span class="sr-only">Mi perfil</span>
                <span class="nav-user-img">
                  <picture>
                    <source srcset="<?php echo $imagenPerfil; ?>" type="image/webp">
                    <img src="<?php echo $imagenPerfil; ?>" alt="User name">
                  </picture>
                </span>
              </button>
              <ul class="users-item-dropdown nav-user-dropdown dropdown">
                <li>
                  <a href="#" data-toggle="modal" data-target="#editarFotoModal">
                    <i data-feather="user"></i>
                    <span>Editar foto</span>
                  </a>
                </li>
                <li>
                  <a class="danger" href="logout.php">
                    <i data-feather="log-out"></i>
                    <span>Cerrar sesión</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <!-- Contenido principal del dashboard -->
      <main class="main users chart-page" id="skip-target">
        <div class="container">
          <h2 class="main-title">Dashboard</h2>
          <div id="dashboardContent">
            <?php include "widgets.php"; ?>
          </div>
        </div>
      </main>

      <!-- Pie de página -->
      <footer class="footer">
        <div class="container footer--flex">
          <div class="footer-start">
            <p><?php echo date('Y'); ?> © criptoinversor Pro</p>
          </div>
          <ul class="footer-end">
            <li><a href="##">About</a></li>
            <li><a href="##">Support</a></li>
            <li><a href="##">Puchase</a></li>
          </ul>
        </div>
      </footer>
    </div>
  </div>

  <!-- ================================================
        MODALES DEL SISTEMA
    ================================================== -->

  <!-- Modal para editar foto de perfil -->
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
          <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
            <div class="form-group">
              <label for="nuevaFoto">Seleccionar nueva foto:</label>
              <input type="file" class="form-control-file" id="nuevaFoto" name="nuevaFoto" required>
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
        </div>
      </div>
    </div>
  </div>

<!-- Modal para carga de operaciones -->
<div class="modal fade" id="cargaOperacionesModal" tabindex="-1" role="dialog" aria-labelledby="cargaOperacionesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cargaOperacionesModalLabel">Cargar Reporte de Operaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCargaOperaciones" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="archivoOperaciones">Seleccione el archivo Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" class="form-control-file" id="archivoOperaciones" name="archivoOperaciones" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">El archivo debe contener las operaciones en el formato especificado</small>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <span id="submitText">Cargar</span>
                            <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
                <div id="resultadoCarga" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formCargaOperaciones').on('submit', function(e) {
        e.preventDefault();
        
        // Mostrar spinner y deshabilitar botón
        $('#loadingSpinner').removeClass('d-none');
        $('#submitText').text('Procesando...');
        $('#btnSubmit').prop('disabled', true);
        
        // Limpiar resultados anteriores
        $('#resultadoCarga').html('');
        
        // Crear FormData
        var formData = new FormData(this);
        
        // Enviar por AJAX
        $.ajax({
            url: 'procesar_operaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        $('#resultadoCarga').html(
                            '<div class="alert alert-success">' +
                            data.message + 
                            (data.errores && data.errores.length > 0 ? 
                                '<hr><strong>Errores:</strong><ul><li>' + 
                                data.errores.join('</li><li>') + 
                                '</li></ul>' : '') +
                            '</div>'
                        );
                        
                        // Recargar la tabla de operaciones si existe
                        if (typeof window.recargarOperaciones === 'function') {
                            window.recargarOperaciones();
                        }
                    } else {
                        $('#resultadoCarga').html(
                            '<div class="alert alert-danger">' + data.message + '</div>'
                        );
                    }
                } catch (e) {
                    $('#resultadoCarga').html(
                        '<div class="alert alert-danger">Error al procesar la respuesta del servidor</div>'
                    );
                    console.error('Error parsing response:', e);
                }
            },
            error: function(xhr, status, error) {
                $('#resultadoCarga').html(
                    '<div class="alert alert-danger">Error en la comunicación con el servidor: ' + 
                    error + '</div>'
                );
            },
            complete: function() {
                // Restaurar botón
                $('#loadingSpinner').addClass('d-none');
                $('#submitText').text('Cargar');
                $('#btnSubmit').prop('disabled', false);
            }
        });
    });
    
    // Limpiar el modal al cerrar
    $('#cargaOperacionesModal').on('hidden.bs.modal', function () {
        $('#formCargaOperaciones')[0].reset();
        $('#resultadoCarga').html('');
    });
});
</script>
  <!-- ================================================
        SCRIPTS DEL SISTEMA
    ================================================== -->
  <script src="./plugins/chart.min.js"></script>
  <script src="plugins/feather.min.js"></script>
  <script src="js/script.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

  <script>
    /**
     * INICIALIZACIÓN DEL SISTEMA
     * 
     * Configura los componentes interactivos del dashboard
     */
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Inicializar iconos Feather
      feather.replace();

      // 2. Configurar tema oscuro/claro
      if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
      }

      // 3. Manejador del botón de cambio de tema
      document.querySelector('.theme-switcher').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        feather.replace(); // Actualizar iconos después del cambio
      });

      // 4. Función para cargar contenido dinámico vía AJAX
      function loadContent(url) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('dashboardContent').innerHTML = xhr.responseText;
            feather.replace(); // Reemplazar iconos en el nuevo contenido
          }
        };
        xhr.open('GET', url, true);
        xhr.send();
      }

      // 5. Eventos para cargar contenido dinámico
      document.getElementById('listarUsuariosLink').addEventListener('click', function(e) {
        e.preventDefault();
        loadContent('listar_usuario.php');
      });

      document.getElementById('parametrizacionBtn').addEventListener('click', function(e) {
        e.preventDefault();
        loadContent('parametrizacion.php');
      });

      document.getElementById('loginBtn').addEventListener('click', function(e) {
        e.preventDefault();
        loadContent('editarlogin.php');
      });
    });

    // Evento para cargar operaciones
document.getElementById('cargaOperacionesBtn').addEventListener('click', function(e) {
    e.preventDefault();
    $('#cargaOperacionesModal').modal('show');
});

// Manejar envío del formulario de carga
document.getElementById('formCargaOperaciones').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_operaciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultadoDiv = document.getElementById('resultadoCarga');
        resultadoDiv.innerHTML = data.message;
        resultadoDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
        
        if (data.success) {
            // Recargar el contenido del dashboard después de 2 segundos
            setTimeout(() => {
                loadContent('widgets.php');
                $('#cargaOperacionesModal').modal('hide');
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('resultadoCarga').innerHTML = 'Error al procesar el archivo';
        document.getElementById('resultadoCarga').className = 'alert alert-danger';
    });
});
// Evento para cargar operaciones
document.getElementById('cargaOperacionesBtn').addEventListener('click', function(e) {
    e.preventDefault();
    $('#cargaOperacionesModal').modal('show');
});

// Manejar envío del formulario de carga
document.getElementById('formCargaOperaciones').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_operaciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultadoDiv = document.getElementById('resultadoCarga');
        resultadoDiv.innerHTML = data.message;
        resultadoDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
        
        if (data.success) {
            // Recargar el contenido del dashboard después de 2 segundos
            setTimeout(() => {
                loadContent('widgets.php');
                $('#cargaOperacionesModal').modal('hide');
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('resultadoCarga').innerHTML = 'Error al procesar el archivo';
        document.getElementById('resultadoCarga').className = 'alert alert-danger';
    });
});
  </script>
</body>

</html>