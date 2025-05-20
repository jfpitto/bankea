<?php
session_start();

include_once("../conexion.php");
// Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar el formulario de creación o edición de proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['direccion']) && isset($_POST['telefono']) && isset($_POST['email'])) {
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        if (isset($_POST['id'])) {
            // Se está realizando una edición de proveedor existente
            $idProveedor = $_POST['id'];

            // Actualizar el proveedor en la base de datos
            $query = "UPDATE proveedores SET nombre = '$nombre', direccion = '$direccion', telefono = '$telefono', email = '$email' WHERE id = '$idProveedor'";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: proveedor.php?success=El proveedor se ha editado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: proveedor.php?error=Hubo un error al editar el proveedor');
                exit();
            }
        } else {
            // Se está creando un nuevo proveedor
            // Insertar el proveedor en la base de datos
            $query = "INSERT INTO proveedores (nombre, direccion, telefono, email) VALUES ('$nombre', '$direccion', '$telefono', '$email')";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: proveedor.php?success=El proveedor se ha guardado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: proveedor.php?error=Hubo un error al guardar el proveedor');
                exit();
            }
        }
    } elseif (isset($_POST['eliminar']) && isset($_POST['id'])) {
        // Eliminar el proveedor seleccionado
        $idProveedor = $_POST['id'];

        // Eliminar el proveedor de la base de datos
        $query = "DELETE FROM proveedores WHERE id = '$idProveedor'";
        $resultado = $conexion->query($query);

        if ($resultado) {
            // Redirigir a la página actual con mensaje de éxito
            header('Location: proveedor.php?success=El proveedor se ha eliminado correctamente.');
            exit();
        } else {
            // Redirigir a la página actual con mensaje de error
            header('Location: proveedor.php?error=Hubo un error al eliminar el proveedor');
            exit();
        }
    }
}

// Obtener la lista de proveedores existentes
$query = "SELECT * FROM proveedores";
$resultado = $conexion->query($query);
$proveedores = $resultado->fetch_all(MYSQLI_ASSOC);

// Verificar si hay un mensaje de éxito o error para mostrar la alerta
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Proveedor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        .widget {
            text-align: center;
            margin-top: 50px;
        }

        .form-container {
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .form-container .form-group {
            margin-bottom: 10px;
        }

        .list-container {
            margin-top: 20px;
        }

        .list-container table {
            width: 100%;
        }

        .list-container th,
        .list-container td {
            padding: 8px;
            text-align: center;
        }

        @media (max-width: 767px) {
            /* Estilos para dispositivos móviles */
            .form-container,
            .list-container {
                margin-top: 10px;
                margin-bottom: 10px;
                overflow: auto;
            }

            .form-container .form-group,
            .list-container table {
                width: 100%;
            }

            .widget {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="widget">
        <h3>Proveedor</h3>
        <div class="form-container">
            <?php if (isset($_GET['id'])) : ?>
                <?php
                // Obtener el ID del proveedor a editar
                $idProveedorEditar = $_GET['id'];

                // Obtener la información del proveedor correspondiente al ID
                $query = "SELECT * FROM proveedores WHERE id = '$idProveedorEditar'";
                $resultado = $conexion->query($query);
                $proveedorEditar = $resultado->fetch_assoc();

                if (!$proveedorEditar) {
                    // Redirigir a la página de proveedores en caso de que no se encuentre el proveedor
                    header('Location: proveedor.php');
                    exit();
                }
                ?>
                <h4>Editar Proveedor</h4>
                <form method="POST" action="proveedor.php">
                    <div class="form-group">
                        <input type="hidden" name="id" value="<?php echo $proveedorEditar['id']; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del proveedor"
                               value="<?php echo $proveedorEditar['nombre']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="direccion" class="form-control"
                               placeholder="Dirección del proveedor"
                               value="<?php echo $proveedorEditar['direccion']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telefono" class="form-control"
                               placeholder="Teléfono del proveedor"
                               value="<?php echo $proveedorEditar['telefono']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email del proveedor"
                               value="<?php echo $proveedorEditar['email']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </form>
            <?php else : ?>
                <h4>Agregar Proveedor</h4>
                <form method="POST" action="proveedor.php">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del proveedor" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="direccion" class="form-control"
                               placeholder="Dirección del proveedor" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telefono" class="form-control"
                               placeholder="Teléfono del proveedor" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email del proveedor" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a class="btn btn-secondary" href="../dashboard_admin.php">Regresar</a>
                </form>
            <?php endif; ?>
        </div>
        <div class="list-container">
            <h4>Lista de Proveedores</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($proveedores as $proveedor) : ?>
                    <tr>
                        <td><?php echo $proveedor['nombre']; ?></td>
                        <td><?php echo $proveedor['direccion']; ?></td>
                        <td><?php echo $proveedor['telefono']; ?></td>
                        <td><?php echo $proveedor['email']; ?></td>
                        <td>
                            <a href="proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="proveedor.php" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?php echo $proveedor['id']; ?>">
                                <button type="submit" name="eliminar" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

</body>
</html>
