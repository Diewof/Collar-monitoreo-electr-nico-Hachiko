<?php
require_once '../control/BaseController.php';
require_once 'components/Notification.php';

// Iniciar sesión
session_start();

// Mostrar notificaciones
BaseController::showNotification();

// Verificar si hay algún mensaje de error, éxito o información
$errorMsg = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';
$infoMsg = $_GET['info'] ?? ($_SESSION['info'] ?? '');

// Limpiar mensajes de sesión después de mostrarlos
if(isset($_SESSION['error'])) unset($_SESSION['error']);
if(isset($_SESSION['success'])) unset($_SESSION['success']);
if(isset($_SESSION['info'])) unset($_SESSION['info']);

// Recuperar datos del formulario previo en caso de error
$formData = $_SESSION['form_data'] ?? [];
if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

// Determinar qué formulario mostrar
$activeForm = isset($_GET['form']) && $_GET['form'] === 'register' ? 'register' : 'login';

// Incluir el modal de propietario si es el primer inicio de sesión
if (isset($_SESSION['is_first_login'])) {
    require_once 'components/propietario_modal.php';
    // No redirigir si es el primer inicio de sesión
    if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
        // Mantener al usuario en la página de login para completar el formulario
        $activeForm = 'login';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Registro</title>
    <!-- Importación de fuente Poppins de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <!-- Botón para cambiar tema -->
    <button class="theme-toggle" id="theme-toggle"><img src="../icons/sun.avif" alt="Icono de tema" width="38" height="38"></button>
    
    <div class="container">
        <!-- Mostrar mensajes de error o éxito si existen -->
        <?php if(!empty($errorMsg)): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($successMsg)): ?>
            <div class="message success"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['info'])): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($_GET['info']); ?></div>
        <?php endif; ?>
    
        <div class="tabs">
            <a href="?form=login" class="tab <?php echo $activeForm === 'login' ? 'active' : ''; ?>">Iniciar Sesión</a>
            <a href="?form=register" class="tab <?php echo $activeForm === 'register' ? 'active' : ''; ?>">Registrarse</a>
        </div>
        
        <div class="forms">
            <!-- Formulario de Login -->
            <form id="login" action="../control/auth_controller.php" method="POST" class="form <?php echo $activeForm === 'login' ? 'active' : ''; ?>">
                <input type="hidden" name="action" value="login">
                
                <div class="form-header">
                    <span class="icon"><img src="../icons/dogmain.avif" alt="Icono de perro" width="38" height="38"></span>
                    <h2>Iniciar Sesión</h2>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/email.avif" alt="Icono de email" width="22" height="22"></span>
                    <input type="email" name="email" class="input-field" placeholder="Correo Electrónico" 
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                           minlength="10" maxlength="45" required
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                           title="El correo debe tener entre 10 y 45 caracteres y un formato válido">
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.avif" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="password" class="input-field" placeholder="Contraseña" 
                           minlength="8" maxlength="25" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                           title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número">
                    <span class="password-toggle" data-state="closed">
                        <img src="../icons/close-eye.avif" alt="Mostrar contraseña" width="20" height="20">
                    </span>
                </div>
                
                <button type="submit" class="submit-btn">
                    <span class="btn-icon"><img src="../icons/login.avif" alt="Icono de inicio de sesión" width="24" height="24"></span>
                    Iniciar Sesión
                </button>
                
                <div class="forgot-password">
                    <a href="recuperar-password.php">¿Olvidaste tu contraseña?</a>
                </div>
                
                <div class="divider"></div>
                
                <div class="switch-form">
                    <a href="?form=register">Crear cuenta</a>
                </div>
            </form>
            
            <!-- Formulario de Registro -->
            <form id="register" action="../control/auth_controller.php" method="POST" class="form <?php echo $activeForm === 'register' ? 'active' : ''; ?>">
                <input type="hidden" name="action" value="register">
                
                <div class="form-header">
                    <span class="icon"><img src="../icons/dogmain.avif" alt="Icono de perro" width="38" height="38"></span>
                    <h2>Registrarse</h2>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/email.avif" alt="Icono de email" width="22" height="22"></span>
                    <input type="email" name="email" class="input-field" placeholder="Correo Electrónico" 
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                           minlength="10" maxlength="45" required
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                           title="El correo debe tener entre 10 y 45 caracteres y un formato válido">
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.avif" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="password" class="input-field" placeholder="Contraseña" 
                           minlength="8" maxlength="25" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                           title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número">
                    <span class="password-toggle" data-state="closed">
                        <img src="../icons/close-eye.avif" alt="Mostrar contraseña" width="20" height="20">
                    </span>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.avif" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="confirm_password" class="input-field" placeholder="Confirmar Contraseña" 
                           minlength="8" maxlength="25" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                           title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número">
                    <span class="password-toggle" data-state="closed">
                        <img src="../icons/close-eye.avif" alt="Mostrar contraseña" width="20" height="20">
                    </span>
                </div>
                
                <button type="submit" class="submit-btn">
                    <span class="btn-icon"><img src="../icons/register.avif" alt="Icono de registro" width="24" height="24"></span>
                    Registrarse
                </button>
                
                <div class="divider"></div>
                
                <div class="switch-form">
                    <a href="?form=login">Ya tengo cuenta</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Script para funcionalidades del cliente -->
    <script src="../js/ui.js"></script>
</body>
</html>
