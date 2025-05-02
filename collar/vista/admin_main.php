<?php
// Iniciar sesión
session_start();

// Incluir archivos necesarios
require_once '../conexion/conexion.php';
require_once '../modelo/admin_model.php';
require_once '../control/admin_controller.php';


// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    // Si no es administrador, redirigir al login
    $_SESSION['error'] = 'Acceso denegado. Necesita credenciales de administrador.';
    header('Location: login-registro.php');
    exit;
}

// Inicializar controlador y obtener datos del dashboard
$adminController = new AdminController();
$dashboardData = $adminController->getDashboardData();

// Obtener información del usuario
$userEmail = $_SESSION['user_email'] ?? 'Administrador';
$userId = $_SESSION['user_id'] ?? 0;

// Formatear tiempo para mostrar en "hace X tiempo"
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $strTime = array("segundo", "minuto", "hora", "día", "mes", "año");
    $length = array("60", "60", "24", "30", "12", "10");

    $currentTime = time();
    if ($currentTime >= $timestamp) {
        $diff = $currentTime - $timestamp;
        
        for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
            $diff = $diff / $length[$i];
        }

        $diff = round($diff);
        return "Hace " . $diff . " " . $strTime[$i] . ($diff > 1 ? "s" : "");
    }
    
    return "Justo ahora";
}

// Determinar la sección a mostrar
$section = $_GET['section'] ?? 'dashboard';
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
                    <input type="text" placeholder="Buscar usuarios..." class="search-input">
                </div>
                <div id="search-results" class="search-results"></div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Gestión de Usuarios <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="users">Listar Usuarios</a>
                        <a href="#" class="dropdown-item" data-section="add_user">Añadir Usuario</a>
                        <a href="#" class="dropdown-item" data-section="user_stats">Estadísticas</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Seguridad <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="login_attempts">Registros de Intentos</a>
                        <a href="#" class="dropdown-item" data-section="blocks">Bloqueos</a>
                        <a href="#" class="dropdown-item" data-section="security_settings">Configuración</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Sistema <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="system_settings">Configuración</a>
                        <a href="#" class="dropdown-item" data-section="backups">Backups</a>
                        <a href="#" class="dropdown-item" data-section="logs">Logs</a>
                    </div>
                </li>
            </ul>
            
            <div class="user-menu">
                <div class="user-profile dropdown">
                    <img src="../icons/admin-user.avif" alt="Administrador" class="user-avatar" width="30" height="30">
                    <span class="user-name"><?php echo htmlspecialchars($userEmail); ?> <span class="admin-badge">Admin</span></span>
                    <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12">
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="profile">Mi Perfil</a>
                        <a href="#" class="dropdown-item" data-section="settings">Configuración</a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" 
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
        <?php if ($section === 'dashboard' || $section === ''): ?>
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
                    <p class="stat-value"><?php echo $dashboardData['total_users']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/login.avif" alt="Inicios de sesión">
                </div>
                <div class="stat-details">
                    <h3>Inicios de Sesión Hoy</h3>
                    <p class="stat-value"><?php echo $dashboardData['today_logins']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/alert.avif" alt="Alertas">
                </div>
                <div class="stat-details">
                    <h3>Intentos Fallidos</h3>
                    <p class="stat-value"><?php echo $dashboardData['failed_attempts']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../icons/lock.avif" alt="Bloqueos">
                </div>
                <div class="stat-details">
                    <h3>Cuentas Bloqueadas</h3>
                    <p class="stat-value"><?php echo $dashboardData['blocked_accounts']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Tabla de usuarios registrados -->
        <div class="admin-section">
            <div class="section-header">
                <h2>Usuarios Registrados</h2>
                <button class="btn btn-primary" onclick="window.location.href='admin_main.php?section=add_user'">Añadir Usuario</button>
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
                        <?php foreach ($dashboardData['users'] as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <select class="role-select" data-user-id="<?php echo $user['id']; ?>" data-original-role="<?php echo htmlspecialchars($user['role'] ?? 'usuario'); ?>">
                                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>admin</option>
                                    <option value="usuario" <?php echo ($user['role'] === null || $user['role'] === 'usuario') ? 'selected' : ''; ?>>usuario</option>
                                </select>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo ($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit" data-user-id="<?php echo $user['id']; ?>">Editar</button>
                                    <button class="btn btn-sm btn-delete" 
                                            data-user-id="<?php echo $user['id']; ?>" 
                                            data-user-email="<?php echo htmlspecialchars($user['email']); ?>">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Actividad Reciente -->
        <div class="admin-section">
            <h2>Actividad Reciente</h2>
            <div class="activity-log">
                <?php foreach ($dashboardData['recent_activity'] as $activity): ?>
                <div class="activity-item">
                    <?php if ($activity['type'] === 'login'): ?>
                    <div class="activity-icon success">
                        <img src="../icons/check.avif" alt="Éxito">
                    </div>
                    <div class="activity-details">
                        <p><strong>Inicio de sesión exitoso</strong> - <?php echo htmlspecialchars($activity['email']); ?></p>
                        <span class="activity-time"><?php echo timeAgo($activity['time']); ?></span>
                    </div>
                    <?php elseif ($activity['type'] === 'failed'): ?>
                    <div class="activity-icon warning">
                        <img src="../icons/warning.avif" alt="Advertencia">
                    </div>
                    <div class="activity-details">
                        <p><strong>Intento de inicio de sesión fallido</strong> - <?php echo htmlspecialchars($activity['email']); ?> - IP: <?php echo htmlspecialchars($activity['ip_address']); ?></p>
                        <span class="activity-time"><?php echo timeAgo($activity['time']); ?></span>
                    </div>
                    <?php elseif ($activity['type'] === 'register'): ?>
                    <div class="activity-icon info">
                        <img src="../icons/information.avif" alt="Información">
                    </div>
                    <div class="activity-details">
                        <p><strong>Usuario registrado</strong> - <?php echo htmlspecialchars($activity['email']); ?></p>
                        <span class="activity-time"><?php echo timeAgo($activity['time']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($dashboardData['recent_activity'])): ?>
                <div class="no-activity">
                    <p>No hay actividad reciente para mostrar.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($section === 'users'): ?>
        <!-- Sección de Listado de Usuarios -->
        <div class="admin-header">
            <h1>Listado de Usuarios</h1>
            <p>Gestión completa de usuarios del sistema</p>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>Usuarios Registrados</h2>
                <button class="btn btn-primary" onclick="window.location.href='admin_main.php?section=add_user'">Añadir Usuario</button>
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
                        <?php foreach ($dashboardData['users'] as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <select class="role-select" data-user-id="<?php echo $user['id']; ?>" data-original-role="<?php echo htmlspecialchars($user['role'] ?? 'usuario'); ?>">
                                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>admin</option>
                                    <option value="usuario" <?php echo ($user['role'] === null || $user['role'] === 'usuario') ? 'selected' : ''; ?>>usuario</option>
                                </select>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo ($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit" data-user-id="<?php echo $user['id']; ?>">Editar</button>
                                    <button class="btn btn-sm btn-delete" 
                                            data-user-id="<?php echo $user['id']; ?>" 
                                            data-user-email="<?php echo htmlspecialchars($user['email']); ?>">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif ($section === 'add_user'): ?>
        <!-- Sección para Añadir Usuario -->
        <div class="admin-header">
            <h1>Añadir Nuevo Usuario</h1>
            <p>Crear una nueva cuenta de usuario en el sistema</p>
        </div>
        
        <div class="admin-section">
            <div class="form-container">
                <form action="../control/auth_controller.php" method="POST" class="admin-form">
                    <input type="hidden" name="action" value="register_user">
                    <input type="hidden" name="from_admin" value="1">
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="password-strength-meter">
                            <div class="strength-bar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rol:</label>
                        <select id="role" name="role" class="form-control">
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='admin_main.php'">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif ($section === 'login_attempts'): ?>
        <!-- Sección de Registros de Intentos de Inicio de Sesión -->
        <div class="admin-header">
            <h1>Registros de Intentos de Inicio de Sesión</h1>
            <p>Monitoreo de la seguridad del sistema</p>
        </div>
        
        <div class="admin-section">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Dirección IP</th>
                            <th>Fecha y Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí se listarían los intentos de inicio de sesión -->
                        <tr>
                            <td colspan="5" class="text-center">No hay registros de intentos de inicio de sesión fallidos.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif ($section === 'profile'): ?>
        <!-- Sección del Perfil del Administrador -->
        <div class="admin-header">
            <h1>Mi Perfil</h1>
            <p>Gestión de su cuenta de administrador</p>
        </div>
        
        <div class="admin-section">
            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="../icons/admin-user.avif" alt="Perfil" class="profile-avatar">
                        <h2><?php echo htmlspecialchars($userEmail); ?></h2>
                        <span class="admin-role-badge">Administrador</span>
                    </div>
                    
                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-label">ID de Usuario:</span>
                            <span class="info-value"><?php echo $userId; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Último Acceso:</span>
                            <span class="info-value">
                                <?php 
                                foreach ($dashboardData['users'] as $user) {
                                    if ($user['id'] == $userId && $user['last_login']) {
                                        echo date('d/m/Y H:i', strtotime($user['last_login']));
                                        break;
                                    }
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <button class="btn btn-primary" onclick="window.location.href='admin_main.php?section=change_password'">Cambiar Contraseña</button>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Sección de Desarrollo (para secciones aún no implementadas) -->
        <div class="admin-header">
            <h1>Sección en Desarrollo</h1>
            <p>Esta sección está actualmente en desarrollo. Disculpe las molestias.</p>
        </div>
        
        <div class="admin-section">
            <div class="development-message">
                <img src="../icons/construction.avif" alt="En construcción" class="construction-icon">
                <p>La sección <strong><?php echo htmlspecialchars($section); ?></strong> estará disponible próximamente.</p>
                <button class="btn btn-primary" onclick="window.location.href='admin_main.php'">Volver al Dashboard</button>
            </div>
        </div>
        <?php endif; ?>
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
<!-- Modal para editar usuario - Añadir antes del cierre del body -->
<div id="edit-user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Usuario</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div id="edit-confirmation" class="confirmation-message">
            Usuario actualizado correctamente
        </div>
        <form id="edit-user-form" action="../control/admin_controller.php" method="POST" class="admin-form">
            <input type="hidden" name="action" value="update_user">
            <input type="hidden" id="edit-user-id" name="user_id" value="">

            <div class="form-group">
                <label for="edit-email">Correo Electrónico:</label>
                <input type="email" id="edit-email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-password">Nueva Contraseña (dejar en blanco para mantener la actual):</label>
                <input type="password" id="edit-password" name="password" class="form-control">
                <div class="password-strength-meter">
                    <div class="strength-bar"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="edit-confirm-password">Confirmar Nueva Contraseña:</label>
                <input type="password" id="edit-confirm-password" name="confirm_password" class="form-control">
            </div>

            <div class="form-group">
                <label for="edit-role">Rol:</label>
                <select id="edit-role" name="role" class="form-control">
                    <option value="usuario">Usuario</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>