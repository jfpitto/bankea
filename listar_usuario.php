<!DOCTYPE html>
<html>
<head>
  <title>Lista de Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .table {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mt-3">Lista de Usuarios</h1>

    <div class="form-group mt-3">
      <label for="search">Buscar Usuarios:</label>
      <input type="text" class="form-control" id="search" placeholder="Ingrese el nombre, apellido o usuario del usuario">
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Correo</th>
          <th>Cédula</th>
          <th>Teléfono</th>
          <th>Perfil</th>
          <th>Usuario</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php
      require_once 'conexion.php';

      // Consultar la tabla de usuarios
      $query = "SELECT usuarios.id, usuarios.nombre_completo, usuarios.apellido_completo, usuarios.correo, usuarios.cedula, usuarios.telefono, perfiles.nombre AS perfil, usuarios.usuario FROM usuarios INNER JOIN perfiles ON usuarios.perfil_id = perfiles.id";
      $result = mysqli_query($conexion, $query);

      // Verificar si se encontraron registros
      if (mysqli_num_rows($result) > 0) {
          // Iterar sobre los resultados y mostrar cada usuario en una fila de la tabla
          while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>";
              echo "<td>{$row['id']}</td>";
              echo "<td>{$row['nombre_completo']}</td>";
              echo "<td>{$row['apellido_completo']}</td>";
              echo "<td>{$row['correo']}</td>";
              echo "<td>{$row['cedula']}</td>";
              echo "<td>{$row['telefono']}</td>";
              echo "<td>{$row['perfil']}</td>";
              echo "<td>{$row['usuario']}</td>";
              echo "<td><a href='editar_usuario.php?id={$row['id']}' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i></a></td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='9'>No se encontraron usuarios</td></tr>";
      }

      mysqli_close($conexion);
      ?>
      </tbody>
    </table>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
      });
   
    });
  </script>
</body>
</html>
