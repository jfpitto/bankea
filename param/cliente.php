<?php
require_once('conexion.php');

$clientes = array();

// Obtener la lista de clientes
$query = "SELECT * FROM clientes";
$resultado = $conexion->query($query);

while ($cliente = $resultado->fetch_assoc()) {
    $clientes[] = $cliente;
}

// Guardar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre']) && isset($_POST['direccion']) && isset($_POST['telefono'])) {
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];

        // Insertar el cliente en la base de datos
        $query = "INSERT INTO clientes (nombre, direccion, telefono) VALUES ('$nombre', '$direccion', '$telefono')";
        if ($conexion->query($query)) {
            $successMessage = "Cliente agregado exitosamente.";
        } else {
            $errorMessage = "Error al agregar el cliente: " . $conexion->error;
        }
    } else {
        $errorMessage = "Por favor, completa todos los campos requeridos.";
    }
}

// Eliminar cliente
if (isset($_POST['eliminar'])) {
    $idClienteEliminar = $_POST['id'];

    // Eliminar el cliente de la base de datos
    $query = "DELETE FROM clientes WHERE id = '$idClienteEliminar'";
    if ($conexion->query($query)) {
        $successMessage = "Cliente eliminado exitosamente.";
    } else {
        $errorMessage = "Error al eliminar el cliente: " . $conexion->error;
    }
}

$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cliente</title>
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
        <h3>Cliente</h3>
        <div class="form-container">
            <?php if (isset($_GET['id'])) : ?>
                <?php
                // Obtener datos del cliente a editar
                $idClienteEditar = $_GET['id'];
                $query = "SELECT * FROM clientes WHERE id = '$idClienteEditar'";
                $resultado = $conexion->query($query);
                $clienteEditar = $resultado->fetch_assoc();
                ?>
                <h4>Editar Cliente</h4>
                <form method="POST" action="cliente.php">
                    <input type="hidden" name="id" value="<?php echo $clienteEditar['id']; ?>">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del cliente"
                               value="<?php echo $clienteEditar['nombre']; ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="direccion" class="form-control"
                               placeholder="Dirección del cliente" value="<?php echo $clienteEditar['direccion']; ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telefono" class="form-control"
                               placeholder="Teléfono del cliente" value="<?php echo $clienteEditar['telefono']; ?>"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </form>
            <?php else : ?>
                <h4>Agregar Cliente</h4>
                <form method="POST" action="cliente.php">
                    <div class="form-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del cliente" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="direccion" class="form-control"
                               placeholder="Dirección del cliente" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telefono" class="form-control"
                               placeholder="Teléfono del cliente" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a class="btn btn-secondary" href="../dashboard_admin.php">Regresar</a>
                </form>
            <?php endif; ?>
        </div>
        <div class="list-container">
            <h4>Lista de Clientes</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $cliente) : ?>
                    <tr>
                        <td><?php echo $cliente['nombre']; ?></td>
                        <td><?php echo $cliente['direccion']; ?></td>
                        <td><?php echo $cliente['telefono']; ?></td>
                        <td>
                            <a href="cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="cliente.php" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                                <button type="submit" name="eliminar" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.15.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
