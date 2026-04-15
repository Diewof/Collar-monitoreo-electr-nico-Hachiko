<?php
/**
 * Controlador para cerrar sesión
 * Maneja la lógica de negocio relacionada con el cierre de sesión
 */

// Iniciar sesión
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redireccionar al usuario a la página de login
$_SESSION['success'] = 'Has cerrado sesión correctamente';
header('Location: ../vista/login-registro.php');
exit;