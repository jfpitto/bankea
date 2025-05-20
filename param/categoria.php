<?php
session_start();

include_once("../conexion.php");
// Asegúrate de incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión y tiene un perfil válido
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['nombreCompleto']) || $_SESSION['perfilNombre'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar el formulario de creación o edición de categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['descripcion'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];

        if (isset($_POST['id'])) {
            // Se está realizando una edición de categoría existente
            $idCategoria = $_POST['id'];

            // Actualizar la categoría en la base de datos
            $query = "UPDATE categorias SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = '$idCategoria'";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: categoria.php?success=La categoría se ha editado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: categoria.php?error=Hubo un error al editar la categoría');
                exit();
            }
        } else {
            // Se está creando una nueva categoría
            // Insertar la categoría en la base de datos
            $query = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
            $resultado = $conexion->query($query);

            if ($resultado) {
                // Redirigir a la página actual con mensaje de éxito
                header('Location: categoria.php?success=La categoría se ha guardado correctamente.');
                exit();
            } else {
                // Redirigir a la página actual con mensaje de error
                header('Location: categoria.php?error=Hubo un error al guardar la categoría');
                exit();
            }
        }
    } elseif (isset($_POST['eliminar']) && isset($_POST['id'])) {
        // Eliminar la categoría seleccionada
        $idCategoria = $_POST['id'];

        // Eliminar la categoría de la base de datos
        $query = "DELETE FROM categorias WHERE id = '$idCategoria'";
        $resultado = $conexion->query($query);

        if ($resultado) {
            // Redirigir a la página actual con mensaje de éxito
            header('Location: categoria.php?success=La categoría se ha eliminado correctamente.');
            exit();
        } else {
            // Redirigir a la página actual con mensaje de error
            header('Location: categoria.php?error=Hubo un error al eliminar la categoría');
            exit();
        }
    }
}

// Obtener la lista de categorías existentes
$query = "SELECT * FROM categorias";
$resultado = $conexion->query($query);
$categorias = $resultado->fetch_all(MYSQLI_ASSOC);

// Verificar si hay un mensaje de éxito o error para mostrar la alerta
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Categoría</title>
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
        <h3>Categoría</h3>
        <div class="form-container">
            <?php if (isset($_GET['id'])) : ?>
                <?php
                // Obtener el ID de la categoría a editar
                $idCategoriaEditar = $_GET['id'];

                // Obtener la información de la categoría correspondiente al ID
                $query = "SELECT * FROM categorias WHERE id = '$idCategoriaEditar'";
                $resultado = $conexion->query($query);
                $categoriaEditar = $resultado->fetch_assoc();

                if (!$categoriaEditar) {
                    // Redirigir a la página de categorías en caso de que no se encuentre la categoría
                    header('Location: categoria.php');
                    exit();
                }
                ?>
                <h4>Editar Categoría</h4>
                <form method="POST" action="categoria.php">
                    <div class="form-group">
                        <input type="hidden" name="id" value="<?php echo $categoriaEditar['id']; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de categoría"
                               value="<?php echo $categoriaEditar['nombre']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="descripcion" class="form-control"
                               placeholder="Descripción de categoría"
                               value="<?php echo $categoriaEditar['descripcion']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </form>
            <?php else : ?>
                <h4>Agregar Categoría</h4>
                <form method="POST" action="categoria.php">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de categoría" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="descripcion" class="form-control"
                               placeholder="Descripción de categoría" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                        <a class="btn btn-secondary" href="../dashboard_admin.php">Regresar</a>
                </form>
            <?php endif; ?>
        </div>
        <div class="list-container">
            <h4>Lista de Categorías</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categorias as $categoria) : ?>
                    <tr>
                        <td><?php echo $categoria['nombre']; ?></td>
                        <td><?php echo $categoria['descripcion']; ?></td>
                        <td>
                            <a href="categoria.php?id=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> 
                            </a>
                            <form method="POST" action="categoria.php" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
                                <button type="submit" name="eliminar" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
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

