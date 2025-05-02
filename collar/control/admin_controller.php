<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
require_once '../modelo/admin_model.php';
require_once '../conexion/conexion.php';

class AdminController {
    private $adminModel;
    
    public function __construct() {
        global $conexion;
        $this->adminModel = new AdminModel($conexion);
    }
    
    /**
     * Procesa la acción solicitada para el panel de administración
     */
    public function processRequest() {
        // Verificar si el usuario tiene permisos de administrador
        if (!$this->checkAdminAccess()) {
            $this->redirectToLogin();
            return;
        }
        
        // Obtener la acción solicitada
        $action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'dashboard':
                // No hace nada, simplemente mostrará el dashboard
                break;
                
            case 'delete_user':
                $this->deleteUser();
                break;
                
            case 'update_role':
                $this->updateUserRole();
                break;
                
            case 'update_user':
                $this->updateUser();
                break;
                
            case 'fetch_stats':
                $this->fetchStats();
                break;
                
            default:
                // Acción desconocida, redirigir al dashboard
                header('Location: ../vista/admin_main.php');
                exit;
        }
    }
    
    /**
     * Verifica si el usuario actual tiene permisos de administrador
     */
    private function checkAdminAccess() {
        return (
            isset($_SESSION['is_logged_in']) && 
            $_SESSION['is_logged_in'] === true && 
            isset($_SESSION['user_role']) && 
            $_SESSION['user_role'] === 'admin'
        );
    }

    /**
 * Actualiza los datos de un usuario
 */
private function updateUser() {
    if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
        $_SESSION['error'] = 'ID de usuario inválido.';
        header('Location: ../vista/admin_main.php');
        exit;
    }
    
    $userId = (int) $_POST['user_id'];
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'usuario';
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Formato de email inválido.';
        header('Location: ../vista/admin_main.php');
        exit;
    }
    
    // Validar rol
    if (!in_array($role, ['admin', 'usuario'])) {
        $_SESSION['error'] = 'Rol inválido.';
        header('Location: ../vista/admin_main.php');
        exit;
    }
    
    // Actualizar usuario en el modelo
    if ($this->adminModel->updateUser($userId, $email, $password, $role)) {
        $_SESSION['success'] = 'Usuario actualizado correctamente.';
    } else {
        $_SESSION['error'] = 'Error al actualizar el usuario.';
    }
    
    header('Location: ../vista/admin_main.php');
    exit;
}
    
    /**
     * Redirige al usuario a la página de login con un mensaje de error
     */
    private function redirectToLogin() {
        $_SESSION['error'] = 'Acceso denegado. Necesita credenciales de administrador.';
        header('Location: ../login-registro.php');
        exit;
    }
    
    /**
     * Elimina un usuario por su ID
     */
    private function deleteUser() {
        if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
            $_SESSION['error'] = 'ID de usuario inválido.';
            header('Location: ../vista/admin_main.php');
            exit;
        }
        
        $userId = (int) $_POST['user_id'];
        
        // No permitir eliminar al usuario actual
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['error'] = 'No puede eliminar su propia cuenta.';
            header('Location: ../vista/admin_main.php');
            exit;
        }
        
        if ($this->adminModel->deleteUser($userId)) {
            $_SESSION['success'] = 'Usuario eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar el usuario.';
        }
        
        header('Location: ../vista/admin_main.php');
        exit;
    }
    
    /**
     * Actualiza el rol de un usuario
     */
    private function updateUserRole() {
        if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
            $_SESSION['error'] = 'ID de usuario inválido.';
            header('Location: ../vista/admin_main.php');
            exit;
        }
        
        $userId = (int) $_POST['user_id'];
        $role = $_POST['role'] ?? null;
        
        if (!in_array($role, ['admin', 'user', null])) {
            $_SESSION['error'] = 'Rol inválido.';
            header('Location: ../vista/admin_main.php');
            exit;
        }
        
        if ($this->adminModel->updateUserRole($userId, $role)) {
            $_SESSION['success'] = 'Rol de usuario actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el rol del usuario.';
        }
        
        header('Location: ../vista/admin_main.php');
        exit;
    }
    
    /**
     * Devuelve estadísticas del dashboard en formato JSON para AJAX
     */
    private function fetchStats() {
        header('Content-Type: application/json');
        
        $stats = [
            'total_users' => $this->adminModel->getTotalUsers(),
            'today_logins' => $this->adminModel->getTodayLogins(),
            'failed_attempts' => $this->adminModel->getFailedLoginAttempts(),
            'blocked_accounts' => $this->adminModel->getBlockedAccounts()
        ];
        
        echo json_encode($stats);
        exit;
    }
    
    /**
     * Obtiene los datos necesarios para el dashboard
     */
    public function getDashboardData() {
        return [
            'total_users' => $this->adminModel->getTotalUsers(),
            'today_logins' => $this->adminModel->getTodayLogins(),
            'failed_attempts' => $this->adminModel->getFailedLoginAttempts(),
            'blocked_accounts' => $this->adminModel->getBlockedAccounts(),
            'users' => $this->adminModel->getUsers(),
            'recent_activity' => $this->adminModel->getRecentActivity()
        ];
    }
}

// Si este archivo se invoca directamente (para acciones AJAX o formularios)
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new AdminController();
    $controller->processRequest();
}

    
?>