<?php
require_once 'conexion.php';

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$cedula = $_POST['cedula'];
$telefono = $_POST['telefono'];
$perfil = $_POST['perfil'];
$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

// Verificar si ya existe un usuario con el mismo nombre de usuario o correo
$query = "SELECT * FROM usuarios WHERE usuario = '$usuario' OR correo = '$correo'";
$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) > 0) {
    // Ya existe un usuario con el mismo nombre de usuario o correo
    echo "Ya existe un usuario con el mismo nombre de usuario o correo.";
} else {
    // Encriptar la contraseña
    $contrasenaEncriptada = password_hash($contrasena, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO usuarios (nombre_completo, apellido_completo, correo, cedula, telefono, perfil_id, usuario, contrasena) 
                    VALUES ('$nombre', '$apellido', '$correo', '$cedula', '$telefono', $perfil, '$usuario', '$contrasenaEncriptada')";

    if (mysqli_query($conexion, $insertQuery)) {
        echo "Usuario registrado exitosamente.";
    } else {
        echo "Error al registrar el usuario: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>
