<!DOCTYPE html>
<html>
<head>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
           
            <form action="guardar_usuario.php" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre completo:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido completo:</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                </div>
                <div class="form-group">
                    <label for="perfil">Perfil:</label>
                    <select class="form-control" id="perfil" name="perfil" required>
                        <?php
                        require_once 'conexion.php';

                        // Consulta para obtener los perfiles
                        $query = "SELECT id, nombre FROM perfiles";
                        $result = mysqli_query($conexion, $query);

                        // Generar las opciones del select
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Registrar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
