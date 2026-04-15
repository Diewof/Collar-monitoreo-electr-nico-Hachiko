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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
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
    
        <div class="form active">
            <div class="form-header">
                <!-- ICONO: Puedes cambiar este icono 🔑 por cualquier otro -->
                <span class="icon">🔑</span>
                <h2>Recuperar Contraseña</h2>
            </div>
            
            <p class="form-info">Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.</p>
            
            <form action="../control/auth_controller.php" method="POST">
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="input-group">
                    <!-- ICONO: Puedes cambiar este icono ✉️ por cualquier otro -->
                    <span class="input-icon">✉️</span>
                    <input type="email" name="email" class="input-field" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <!-- ICONO: Puedes cambiar este icono 📨 por cualquier otro -->
                    <span class="btn-icon">📨</span>
                    Enviar Instrucciones
                </button>
                
                <div class="divider"></div>
                
                <div class="switch-form">
                    <a href="login-registro.php">Volver al inicio de sesión</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Script para funcionalidades del cliente -->
    <script src="../js/ui.js"></script>
</body>
</html>