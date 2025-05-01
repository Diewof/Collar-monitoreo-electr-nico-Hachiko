<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    // Si no es administrador, redirigir al login
    $_SESSION['error'] = 'Acceso denegado. Necesita credenciales de administrador.';
    header('Location: login-registro.php');
    exit;
}

// Obtener información del usuario
$userEmail = $_SESSION['user_email'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hachiko - Panel de Administración</title>
    <!-- Importación de fuente Poppins de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <!-- Botón para cambiar tema -->
    <button class="theme-toggle" id="theme-toggle">
        <img src="../icons/moon.avif" alt="Cambiar tema" class="theme-icon" width="30" height="30">
    </button>
    
    <!-- Temporizador de inactividad -->
    <div class="inactivity-timer" id="inactivity-timer">⏰
        <span id="timer-countdown">15:00</span> de inactividad antes de cerrar sesión
    </div>
    
    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../icons/dogmain.avif" alt="Logo" width="38" height="38">
                <span>¡Hachiko Admin!</span>
            </div>
            
            <div class="search-container">
                <div class="search-box">
                    <img src="../icons/search.avif" alt="Buscar" class="search-icon" width="18" height="18">
                    <input type="text" placeholder="Buscar..." class="search-input">
                </div>
                <div id="search-results" class="search-results"></div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Gestión de Usuarios <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Listar Usuarios</a>
                        <a href="#" class="dropdown-item">Añadir Usuario</a>
                        <a href="#" class="dropdown-item">Estadísticas</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Seguridad <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Registros de Intentos</a>
                        <a href="#" class="dropdown-item">Bloqueos</a>
                        <a href="#" class="dropdown-item">Configuración</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Sistema <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Configuración</a>
                        <a href="#" class="dropdown-item">Backups</a>
                        <a href="#" class="dropdown-item">Logs</a>
                    </div>
                </li>
            </ul>
            
            <div class="user-menu">
                <div class="user-profile dropdown">
                    <img src="../icons/admin-user.avif" alt="Administrador" class="user-avatar" width="30" height="30">
                    <span class="user-name"><?php echo htmlspecialchars($userEmail); ?> <span class="admin-badge">Admin</span></span>
                    <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12">
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Mi Perfil</a>
                        <a href="#" class="dropdown-item">Configuración</a>
                        <div class="dropdown-divider"></div>
                        <a href="../control/auth_controller.php" class="dropdown-item" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                           Cerrar Sesión
                        </a>
                        <form id="logout-form" action="../control/auth_controller.php" method="POST" style="display: none;">
                            <input type="hidden" name="action" value="logout">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mensajes de sistema -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
        ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
        ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
    <div class="alert alert-info">
        <?php 
            echo htmlspecialchars($_SESSION['info']); 
            unset($_SESSION['info']);
        ?>
    </div>
    <?php endif; ?>
    
    <!-- Contenido principal -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido al panel de control administrativo de Hachiko</p>
        </div>
        
        <!-- Resumen de estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/user.avif" alt="Usuarios">
                </div>
                <div class="stat-details">
                    <h3>Usuarios Totales</h3>
                    <p class="stat-value">2</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/login.avif" alt="Inicios de sesión">
                </div>
                <div class="stat-details">
                    <h3>Inicios de Sesión Hoy</h3>
                    <p class="stat-value">5</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/alert.avif" alt="Alertas">
                </div>
                <div class="stat-details">
                    <h3>Intentos Fallidos</h3>
                    <p class="stat-value">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/lock.avif" alt="Bloqueos">
                </div>
                <div class="stat-details">
                    <h3>Cuentas Bloqueadas</h3>
                    <p class="stat-value">0</p>
                </div>
            </div>
        </div>
        
        <!-- Tabla de usuarios registrados -->
        <div class="admin-section">
            <div class="section-header">
                <h2>Usuarios Registrados</h2>
                <button class="btn btn-primary">Añadir Usuario</button>
            </div>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha de Registro</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Aquí iría el código para recuperar y mostrar usuarios
                        // Este es un ejemplo estático
                        ?>
                        <tr>
                            <td>3</td>
                            <td>dsd@gmail.com</td>
                            <td>usuario</td>
                            <td>16/04/2025</td>
                            <td>20/04/2025</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit">Editar</button>
                                    <button class="btn btn-sm btn-delete">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>jdgutierrezotalvaro04@gmail.com</td>
                            <td>usuario</td>
                            <td>17/04/2025</td>
                            <td>20/04/2025</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit">Editar</button>
                                    <button class="btn btn-sm btn-delete">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Actividad Reciente -->
        <div class="admin-section">
            <h2>Actividad Reciente</h2>
            <div class="activity-log">
                <div class="activity-item">
                    <div class="activity-icon success">
                        <img src="../icons/check.avif" alt="Éxito">
                    </div>
                    <div class="activity-details">
                        <p><strong>Inicio de sesión exitoso</strong> - jdgutierrezotalvaro04@gmail.com</p>
                        <span class="activity-time">Hace 3 horas</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon warning">
                        <img src="../icons/warning.avif" alt="Advertencia">
                    </div>
                    <div class="activity-details">
                        <p><strong>Intento de inicio de sesión fallido</strong> - IP: 192.168.1.1</p>
                        <span class="activity-time">Ayer, 15:32</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon info">
                        <img src="../icons/information.avif" alt="Información">
                    </div>
                    <div class="activity-details">
                        <p><strong>Usuario registrado</strong> - nuevo@usuario.com</p>
                        <span class="activity-time">Ayer, 10:15</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Pie de página -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../icons/dogmain.avif" alt="Logo" width="30" height="30">
                <span>Hachiko - Panel de Administración</span>
            </div>
            <div class="footer-copyright">
                &copy; 2025 Hachiko. Todos los derechos reservados.
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="../js/main.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>