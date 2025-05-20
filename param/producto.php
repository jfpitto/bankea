<?php
session_start();

include_once("../conexion.php");
// Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar el formulario de creación o edición de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['descripcion'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];

        if (isset($_POST['id'])) {
            // Se está realizando una edición de producto existente
            $idProducto = $_POST['id'];

            // Actualizar el producto en la base de datos
            $query = "UPDATE productos SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = '$idProducto'";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: producto.php?success=El producto se ha editado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: producto.php?error=Hubo un error al editar el producto');
                exit();
            }
        } else {
            // Se está creando un nuevo producto
            // Insertar el producto en la base de datos
            $query = "INSERT INTO productos (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: producto.php?success=El producto se ha guardado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: producto.php?error=Hubo un error al guardar el producto');
                exit();
            }
        }
    } elseif (isset($_POST['eliminar']) && isset($_POST['id'])) {
        // Eliminar el producto seleccionado
        $idProducto = $_POST['id'];

        // Eliminar el producto de la base de datos
        $query = "DELETE FROM productos WHERE id = '$idProducto'";
        $resultado = $conexion->query($query);

        if ($resultado) {
            // Redirigir a la página actual con mensaje de éxito
            header('Location: producto.php?success=El producto se ha eliminado correctamente.');
            exit();
        } else {
            // Redirigir a la página actual con mensaje de error
            header('Location: producto.php?error=Hubo un error al eliminar el producto');
            exit();
        }
    }
}

// Obtener la lista de productos existentes
$query = "SELECT * FROM productos";
$resultado = $conexion->query($query);
$productos = $resultado->fetch_all(MYSQLI_ASSOC);

// Verificar si hay un mensaje de éxito o error para mostrar la alerta
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Producto</title>
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
        <h3>Producto</h3>
        <div class="form-container">
            <?php if (isset($_GET['id'])) : ?>
                <?php
                // Obtener el ID del producto a editar
                $idProductoEditar = $_GET['id'];

                // Obtener la información del producto correspondiente al ID
                $query = "SELECT * FROM productos WHERE id = '$idProductoEditar'";
                $resultado = $conexion->query($query);
                $productoEditar = $resultado->fetch_assoc();

                if (!$productoEditar) {
                    // Redirigir a la página de productos en caso de que no se encuentre el producto
                    header('Location: producto.php');
                    exit();
                }
                ?>
                <h4>Editar Producto</h4>
                <form method="POST" action="producto.php">
                    <div class="form-group">
                        <input type="hidden" name="id" value="<?php echo $productoEditar['id']; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto"
                               value="<?php echo $productoEditar['nombre']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="descripcion" class="form-control"
                               placeholder="Descripción del producto"
                               value="<?php echo $productoEditar['descripcion']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </form>
            <?php else : ?>
                <h4>Agregar Producto</h4>
                <form method="POST" action="producto.php">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="descripcion" class="form-control"
                               placeholder="Descripción del producto" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                        <a class="btn btn-secondary" href="../dashboard_admin.php">Regresar</a>
                </form>
            <?php endif; ?>
        </div>
        <div class="list-container">
            <h4>Lista de Productos</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $producto) : ?>
                    <tr>
                        <td><?php echo $producto['nombre']; ?></td>
                        <td><?php echo $producto['descripcion']; ?></td>
                        <td>
                            <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> 
                            </a>
                            <form method="POST" action="producto.php" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" name="eliminar" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar este producto?')">
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
