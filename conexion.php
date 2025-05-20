<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bankeane';

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die('Error al conectar a la base de datos: ' . mysqli_connect_error());
}
?>
