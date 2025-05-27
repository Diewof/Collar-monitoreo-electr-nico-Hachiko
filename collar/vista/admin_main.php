<?php
// Iniciar sesión
session_start();

// Incluir archivos necesarios
require_once '../conexion/conexion.php';
require_once '../modelo/admin_model.php';
require_once '../control/admin_controller.php';
require_once '../control/BaseController.php';
require_once 'components/Notification.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    // Si no es administrador, redirigir al login
    $_SESSION['notification'] = [
        'message' => 'Acceso denegado. Necesita credenciales de administrador.',
        'type' => 'error'
    ];
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

// Mostrar notificaciones
BaseController::showNotification();
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
            
            <!-- TODO: Aquí se añadirán nuevas secciones del navbar cuando se necesiten -->
            
            <ul class="nav-menu">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Dashboard <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="dashboard">Vista General</a>
                        <a href="#" class="dropdown-item" data-section="analytics">Analíticas</a>
                        <a href="#" class="dropdown-item" data-section="reports">Reportes</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Usuarios <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="users">Lista de Usuarios</a>
                        <a href="#" class="dropdown-item" data-section="add_user">Añadir Usuario</a>
                        <a href="#" class="dropdown-item" data-section="user_roles">Roles y Permisos</a>
                        <a href="#" class="dropdown-item" data-section="login_attempts">Intentos de Acceso</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Mascota <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="mascotas">Mascotas</a>
                        <a href="#" class="dropdown-item" data-section="historial_emociones">Historial de emociones</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Collar <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="collares">Collares</a>
                        <a href="#" class="dropdown-item" data-section="registro_sensores">Registro de sensores</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Configuración <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="system_settings">Configuración General</a>
                        <a href="#" class="dropdown-item" data-section="email_settings">Configuración de Email</a>
                        <a href="#" class="dropdown-item" data-section="notification_settings">Notificaciones</a>
                        <a href="#" class="dropdown-item" data-section="backup_settings">Backups</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Seguridad <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="security_logs">Logs de Seguridad</a>
                        <a href="#" class="dropdown-item" data-section="ip_whitelist">Lista Blanca IP</a>
                        <a href="#" class="dropdown-item" data-section="two_factor">Autenticación 2FA</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Mantenimiento <img src="../icons/arrow-down.avif" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" data-section="system_logs">Logs del Sistema</a>
                        <a href="#" class="dropdown-item" data-section="cache">Gestión de Caché</a>
                        <a href="#" class="dropdown-item" data-section="optimization">Optimización</a>
                        <a href="#" class="dropdown-item" data-section="updates">Actualizaciones</a>
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
            <div class="header-content">
                <h1>Panel de Administración</h1>
                <div class="header-actions">
                    <!-- Eliminando el checkbox redundante -->
                </div>
            </div>
            <p>Gestiona usuarios, planes y configuración del sistema</p>
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
                            <th>Nombre Completo</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Plan</th>
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
                            <td>
                                <?php 
                                if (isset($user['propietario'])) {
                                    echo htmlspecialchars(
                                        trim(
                                            ($user['propietario']['primer_nombre'] ?? '') . ' ' .
                                            ($user['propietario']['segundo_nombre'] ?? '') . ' ' .
                                            ($user['propietario']['apellido'] ?? '') . ' ' .
                                            ($user['propietario']['segundo_apellido'] ?? '')
                                        )
                                    );
                                } else {
                                    echo 'No registrado';
                                }
                                ?>
                            </td>
                            <td><?php echo isset($user['propietario']) ? htmlspecialchars($user['propietario']['telefono'] ?? 'No registrado') : 'No registrado'; ?></td>
                            <td>
                                <?php 
                                if (isset($user['propietario']) && isset($user['propietario']['direccion'])) {
                                    echo htmlspecialchars(
                                        ($user['propietario']['direccion']['direccion'] ?? '') . ', ' .
                                        ($user['propietario']['direccion']['ciudad'] ?? '')
                                    );
                                } else {
                                    echo 'No registrada';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (isset($user['propietario']) && isset($user['propietario']['plan'])) {
                                    echo htmlspecialchars($user['propietario']['plan']['nombre_plan'] ?? 'No asignado');
                                } else {
                                    echo 'No asignado';
                                }
                                ?>
                            </td>
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
                            <th>Nombre Completo</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Plan</th>
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
                            <td>
                                <?php 
                                if (isset($user['propietario'])) {
                                    echo htmlspecialchars(
                                        trim(
                                            ($user['propietario']['primer_nombre'] ?? '') . ' ' .
                                            ($user['propietario']['segundo_nombre'] ?? '') . ' ' .
                                            ($user['propietario']['apellido'] ?? '') . ' ' .
                                            ($user['propietario']['segundo_apellido'] ?? '')
                                        )
                                    );
                                } else {
                                    echo 'No registrado';
                                }
                                ?>
                            </td>
                            <td><?php echo isset($user['propietario']) ? htmlspecialchars($user['propietario']['telefono'] ?? 'No registrado') : 'No registrado'; ?></td>
                            <td>
                                <?php 
                                if (isset($user['propietario']) && isset($user['propietario']['direccion'])) {
                                    echo htmlspecialchars(
                                        ($user['propietario']['direccion']['direccion'] ?? '') . ', ' .
                                        ($user['propietario']['direccion']['ciudad'] ?? '')
                                    );
                                } else {
                                    echo 'No registrada';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if (isset($user['propietario']) && isset($user['propietario']['plan'])) {
                                    echo htmlspecialchars($user['propietario']['plan']['nombre_plan'] ?? 'No asignado');
                                } else {
                                    echo 'No asignado';
                                }
                                ?>
                            </td>
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
                <form action="../control/admin_controller.php" method="POST" class="admin-form" id="add-user-form">
                    <input type="hidden" name="action" value="register_user">
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               minlength="10" maxlength="45" required
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                               title="El correo debe tener entre 10 y 45 caracteres y un formato válido"
                               placeholder="ejemplo@correo.com">
                        <div class="error-message" id="email_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El correo debe tener entre 10 y 45 caracteres y un formato válido</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               minlength="8" maxlength="25" required
                               pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                               title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número"
                               placeholder="Mínimo 8 caracteres">
                        <div class="error-message" id="password_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número</span>
                        </div>
                        <div class="password-strength-meter">
                            <div class="strength-bar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               minlength="8" maxlength="25" required
                               pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                               title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número"
                               placeholder="Repita la contraseña">
                        <div class="error-message" id="confirm_password_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">Las contraseñas deben coincidir</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rol *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">Seleccione un rol</option>
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <div class="error-message" id="role_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">Debe seleccionar un rol</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="create-user-btn" disabled>Crear Usuario</button>
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
<?php include 'components/edit_user_modal.php'; ?>
<style>
    /* --- Tema claro/oscuro para el formulario y campos --- */
    body.dark-theme .admin-form {
        background-color: #18191a;
        color: #f1f1f1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.7);
    }
    body.dark-theme .form-control {
        background-color: #23272f;
        color: #f1f1f1;
        border-color: #333;
    }
    body.dark-theme .form-control:focus {
        border-color: #4f8cff;
        box-shadow: 0 0 5px rgba(79, 140, 255, 0.3);
    }
    body.dark-theme .form-control.error {
        background-color: rgba(231, 76, 60, 0.08);
    }
    body.dark-theme .form-control.valid {
        background-color: rgba(46, 204, 113, 0.08);
    }
    body.dark-theme .error-message {
        background-color: rgba(231, 76, 60, 0.13);
        color: #ffb3b3;
    }
    body.dark-theme .btn-primary {
        background-color: #4f8cff;
        color: #fff;
    }
    body.dark-theme .btn-primary:hover:not(:disabled) {
        background-color: #2563eb;
    }
    body.dark-theme .btn-secondary {
        background-color: #333;
        color: #fff;
    }
    body.dark-theme .btn-secondary:hover {
        background-color: #555;
    }

    body.light-theme .admin-form {
        background-color: #fff;
        color: #222;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    body.light-theme .form-control {
        background-color: #fff;
        color: #222;
        border-color: #ddd;
    }
    body.light-theme .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }
    body.light-theme .form-control.error {
        background-color: rgba(231, 76, 60, 0.05);
    }
    body.light-theme .form-control.valid {
        background-color: rgba(46, 204, 113, 0.05);
    }
    body.light-theme .error-message {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    body.light-theme .btn-primary {
        background-color: #3498db;
        color: #fff;
    }
    body.light-theme .btn-primary:hover:not(:disabled) {
        background-color: #2980b9;
    }
    body.light-theme .btn-secondary {
        background-color: #95a5a6;
        color: #fff;
    }
    body.light-theme .btn-secondary:hover {
        background-color: #7f8c8d;
    }

    /* --- Resto de estilos del formulario (ya existentes) --- */
    .admin-form {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 8px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        outline: none;
    }
    .form-control.error {
        border-color: #e74c3c;
    }
    .form-control.valid {
        border-color: #2ecc71;
    }
    .error-message {
        display: none;
        font-size: 12px;
        margin-top: 5px;
        padding: 8px;
        border-radius: 4px;
        align-items: center;
    }
    .error-message.show {
        display: flex;
    }
    .error-icon {
        margin-right: 8px;
        font-weight: bold;
    }
    .btn-primary {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-primary:disabled {
        background-color: #95a5a6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .btn-secondary {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 10px;
    }
</style>
<script>
// --- JS para alternar tema claro/oscuro ---
function setTheme(theme) {
    document.body.classList.remove('dark-theme', 'light-theme');
    document.body.classList.add(theme + '-theme');
    localStorage.setItem('theme', theme);
}

function initTheme() {
    let theme = localStorage.getItem('theme');
    if (!theme) {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    setTheme(theme);
}

document.getElementById('theme-toggle').addEventListener('click', function() {
    const current = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
    setTheme(current === 'dark' ? 'light' : 'dark');
});

initTheme();
</script>
</body>
</html>