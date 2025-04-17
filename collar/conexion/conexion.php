<?php 
$conexion = new mysqli("localhost", "root", "54747454", "collar"); 
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>