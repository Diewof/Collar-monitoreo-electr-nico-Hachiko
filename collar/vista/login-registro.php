<?php
// Iniciar sesión
session_start();

// Verificar si hay algún mensaje de error o éxito
$errorMsg = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';

// Limpiar mensajes de sesión después de mostrarlos
if(isset($_SESSION['error'])) unset($_SESSION['error']);
if(isset($_SESSION['success'])) unset($_SESSION['success']);

// Recuperar datos del formulario previo en caso de error
$formData = $_SESSION['form_data'] ?? [];
if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

// Determinar qué formulario mostrar
$activeForm = isset($_GET['form']) && $_GET['form'] === 'register' ? 'register' : 'login';
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
    <button class="theme-toggle" id="theme-toggle">☀️</button>
    
    <div class="container">
        <!-- Mostrar mensajes de error o éxito si existen -->
        <?php if(!empty($errorMsg)): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($successMsg)): ?>
            <div class="message success"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
    
        <div class="tabs">
            <a href="?form=login" class="tab <?php echo $activeForm === 'login' ? 'active' : ''; ?>">Iniciar Sesión</a>
            <a href="?form=register" class="tab <?php echo $activeForm === 'register' ? 'active' : ''; ?>">Registrarse</a>
        </div>
        
        <div class="forms">
            <!-- Formulario de Login -->
            <form id="login" action="../controlador/auth.controller.php" method="POST" class="form <?php echo $activeForm === 'login' ? 'active' : ''; ?>">
                <input type="hidden" name="action", value="login">
                
                <div class="form-header">
                    <span class="icon"><img src="../icons/dogmain.png" alt="Icono de perro" width="38" height="38"></span>
                    <h2>Iniciar Sesión</h2>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/email.png" alt="Icono de email" width="22" height="22"></span>
                    <input type="email" name="email" class="input-field" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.png" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="password" class="input-field" placeholder="Contraseña" required>
                    <span class="password-toggle"><img src="../icons/eye.png" alt="Mostrar contraseña" width="20" height="20"></span>
                </div>
                
                <button type="submit" class="submit-btn">
                    <span class="btn-icon"><img src="../icons/login.png" alt="Icono de inicio de sesión" width="24" height="24"></span>
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
            <form id="register" action="../controlador/auth.controller.php" method="POST" class="form <?php echo $activeForm === 'register' ? 'active' : ''; ?>">
                <input type="hidden" name="action", value="register">
                
                <div class="form-header">
                    <span class="icon"><img src="../icons/dogmain.png" alt="Icono de perro" width="38" height="38"></span>
                    <h2>Registrarse</h2>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/email.png" alt="Icono de email" width="22" height="22"></span>
                    <input type="email" name="email" class="input-field" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.png" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="password" class="input-field" placeholder="Contraseña" required>
                    <span class="password-toggle"><img src="../icons/close-eye.png" alt="Mostrar contraseña" width="20" height="20"></span>
                </div>
                
                <div class="input-group">
                    <span class="input-icon"><img src="../icons/password.png" alt="Icono de contraseña" width="22" height="22"></span>
                    <input type="password" name="confirm_password" class="input-field" placeholder="Confirmar Contraseña" required>
                    <span class="password-toggle"><img src="../icons/close-eye.png" alt="Mostrar contraseña" width="20" height="20"></span>
                </div>
                
                <button type="submit" class="submit-btn">
                    <span class="btn-icon"><img src="../icons/register.png" alt="Icono de registro" width="24" height="24"></span>
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