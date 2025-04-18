<?php
/**
 * Controlador de autenticación
 * Maneja la lógica de negocio relacionada con el inicio de sesión y registro
 */

// Incluir el modelo
require_once '../modelo/authmodel.php';

// Iniciar sesión
session_start();

// Crear instancia del modelo
$authModel = new AuthModel();

// Verificar qué acción se solicitó
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin($authModel);
            break;
        case 'register':
            handleRegister($authModel);
            break;
        case 'forgot_password':
            handleForgotPassword($authModel);
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            // Acción no reconocida
            $_SESSION['error'] = 'Acción no válida';
            header('Location: ../vista/login-registro.php');
            exit;
    }
} else {
    // No se permitió el acceso directo al controlador
    header('Location: ../vista/login-registro.php');
    exit;
}

/**
 * Maneja el proceso de inicio de sesión
 * @param AuthModel $authModel - Instancia del modelo de autenticación
 */
function handleLogin($authModel) {
    // Obtener y sanitizar datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Obtener dirección IP del cliente
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Validación básica
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Por favor, complete todos los campos';
        $_SESSION['form_data'] = $_POST;
        header('Location: ../vista/login-registro.php?form=login');
        exit;
    }
    
    // Verificar si la cuenta está bloqueada antes de intentar iniciar sesión
    $lockStatus = $authModel->isUserLocked($email, $ip_address);
    if ($lockStatus['locked']) {
        $_SESSION['error'] = "Demasiados intentos fallidos. Cuenta bloqueada por " . $lockStatus['minutes_left'] . " minutos.";
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: ../vista/login-registro.php?form=login');
        exit;
    }
    
    // Intentar iniciar sesión
    $result = $authModel->login($email, $password, $ip_address);
    
    if ($result['success']) {
        // Iniciar sesión
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['user_email'] = $email;
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_name'] = $result['user_name'] ?? ''; // Agregar nombre de usuario si está disponible
        
        // Redirigir a página principal
        $_SESSION['success'] = '¡Bienvenido de nuevo!';
        header('Location: ../vista/main.php');
        exit;
    } else {
        // Error de inicio de sesión
        $_SESSION['error'] = $result['error'] ?? 'Credenciales incorrectas';
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: ../vista/login-registro.php?form=login');
        exit;
    }
}

/**
 * Maneja el proceso de registro de usuario
 * @param AuthModel $authModel - Instancia del modelo de autenticación
 */
function handleRegister($authModel) {
    // Obtener y sanitizar datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validación básica
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = 'Por favor, complete todos los campos';
        $_SESSION['form_data'] = $_POST;
        header('Location: ../vista/login-registro.php?form=register');
        exit;
    }
    
    // Verificar que las contraseñas coincidan
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: ../vista/login-registro.php?form=register');
        exit;
    }
    
    // Validar fortaleza de la contraseña
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: ../vista/login-registro.php?form=register');
        exit;
    }
    
    // Intentar registrar al usuario
    $result = $authModel->register($email, $password);
    
    if ($result['success']) {
        // Registro exitoso
        $_SESSION['success'] = '¡Registro exitoso! Ahora puedes iniciar sesión';
        header('Location: ../vista/login-registro.php?form=login');
        exit;
    } else {
        // Error en el registro
        $_SESSION['error'] = $result['error'] ?? 'Error al registrar el usuario';
        $_SESSION['form_data'] = ['email' => $email];
        header('Location: ../vista/login-registro.php?form=register');
        exit;
    }
}

/**
 * Maneja el proceso de recuperación de contraseña
 * @param AuthModel $authModel - Instancia del modelo de autenticación
 */
function handleForgotPassword($authModel) {
    // Obtener y sanitizar el correo electrónico
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validación básica
    if (empty($email)) {
        $_SESSION['error'] = 'Por favor, ingrese su correo electrónico';
        header('Location: ../vista/recuperar-password.php');
        exit;
    }
    
    // Intentar enviar correo de recuperación
    $result = $authModel->requestPasswordReset($email);
    
    // Siempre mostrar un mensaje de éxito por seguridad, incluso si el correo no existe
    $_SESSION['success'] = 'Si el correo existe en nuestra base de datos, recibirá instrucciones para restablecer su contraseña';
    header('Location: ../vista/login-registro.php?form=login');
    exit;
}

/**
 * Maneja el proceso de cierre de sesión
 */
function handleLogout() {
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
    session_start(); // Reiniciar sesión para el mensaje
    $_SESSION['success'] = 'Has cerrado sesión correctamente';
    header('Location: ../vista/login-registro.php');
    exit;
}