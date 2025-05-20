<!DOCTYPE html>
<html>
<head>
  <title>Parametrización</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .widget {
      text-align: center;
      margin-top: 50px;
    }
    
    .icon-menu {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 20px;
    }
    
    .icon-link {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 150px;
      height: 150px;
      margin: 10px;
      text-decoration: none;
      color: #fff;
      text-align: center;
    }
    
    .icon-link i {
      font-size: 48px;
      margin-bottom: 10px;
    }
    
    /* Estilo para dispositivos móviles */
    @media (max-width: 767px) {
      .icon-menu {
        flex-direction: column;
      }
      
      .icon-link {
        width: 100%;
        height: auto;
        margin: 10px 0;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="widget">
      <h3>Parametrización</h3>
      <div class="icon-menu">
        <a href="param/categoria.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-cogs"></i>
          <span>Categoría</span>
        </a>
        <a href="param/producto.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-shopping-cart"></i>
          <span>Producto</span>
        </a>
        <a href="param/clientes.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-users"></i>
          <span>Cliente</span>
        </a>
        <a href="param/proveedor.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-truck"></i>
          <span>Proveedor</span>
        </a>
        <a href="param/portafolio.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-briefcase"></i>
          <span>Portafolio</span>
        </a>
        <a href="param/configuracion_sistema.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-cog"></i>
          <span>Configuración del sistema</span>
        </a>
        <a href="param/inventario.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-clipboard-list"></i>
          <span>Inventario</span>
        </a>
        <a href="param/grupo_trabajo.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-users"></i>
          <span>Grupo de trabajo</span>
        </a>
        <a href="param/sedes.php" class="btn btn-primary btn-lg icon-link">
          <i class="fas fa-map-marker-alt"></i>
          <span>Sedes</span>
        </a>
        <!-- Agrega más botones y enlaces según sea necesario -->
      </div>
    </div>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
