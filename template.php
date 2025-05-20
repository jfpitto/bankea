<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      margin: 0;
      padding-top: 0;
      background-color: #f8f9fa;
    }

    .navbar {
      position: relative;
      background-color: transparent;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 0;
    }

    .navbar-container {
      background-image: url('discos/menu.png'); /* Utiliza la ruta correcta de tu imagen */
      background-size: cover;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    .navbar-brand, .navbar-toggler-icon {
      color: #ffffff;
      font-weight: bold;
      font-size: 24px;
    }

    .navbar-toggler {
      border: none;
      background-color: #8e44ad;
      outline: none;
    }

    .navbar-toggler:hover, .navbar-toggler:focus {
      color: #ecf0f1;
    }

    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      z-index: 1;
      top: 0;
      left: 0;
      background-color: #333;
      overflow-x: hidden;
      transition: 0.5s;
      padding-top: 60px;
    }

    .sidebar a {
      padding: 8px 8px 8px 32px;
      text-decoration: none;
      font-size: 18px;
      color: #818181;
      display: block;
      transition: 0.3s;
    }

    .sidebar a:hover {
      color: #f1f1f1;
    }

    .sidebar .close-btn {
      position: absolute;
      top: 0;
      right: 25px;
      font-size: 36px;
      margin-left: 50px;
    }

    @media screen and (max-height: 450px) {
      .sidebar {padding-top: 15px;}
      .sidebar a {font-size: 14px;}
    }
  </style>
  <title>Menú Desplegable Elegante</title>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="navbar-container"></div>
  <div class="container">
    <button class="navbar-toggler" type="button" id="sidebarToggle">
      &#9776;
    </button>
  </div>
</nav>

<div class="sidebar" id="mySidebar">
  <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">×</a>
  <a href="#">Inicio</a>
  <a href="#">Acerca de</a>
  <a href="#">Servicios</a>
  <a href="#">Contacto</a>
</div>

<!-- Contenido de la página -->
<div class="container mt-4">
  
</div>

<!-- Scripts de Bootstrap y jQuery (asegúrate de incluir jQuery antes de Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  function openNav() {
    document.getElementById("mySidebar").style.width = "250px";
  }

  function closeNav() {
    document.getElementById("mySidebar").style.width = "0";
  }

  document.getElementById("sidebarToggle").addEventListener("click", function() {
    if (document.getElementById("mySidebar").style.width === "250px") {
      closeNav();
    } else {
      openNav();
    }
  });
</script>

</body>
</html>
