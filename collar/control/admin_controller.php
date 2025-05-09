<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
require_once '../modelo/admin_model.php';
require_once '../conexion/conexion.php';
require_once 'BaseController.php';

class AdminController extends BaseController {
    private $adminModel;
    
    public function __construct() {
        parent::__construct();
        global $conexion;
        $this->adminModel = new AdminModel($conexion);
    }
    
    /**
     * Procesa la acción solicitada para el panel de administración
     */
    public function processRequest() {
        if (!$this->checkAdminAccess()) {
            $this->redirect('../login-registro.php', 'Acceso denegado. Necesita credenciales de administrador.', 'error');
        }
        
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
                $this->redirect('../vista/admin_main.php');
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
            $this->redirect('../vista/admin_main.php', 'ID de usuario inválido.', 'error');
        }
        
        $userId = (int) $_POST['user_id'];
        $email = $this->sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'usuario';
        
        if (!$this->validateEmail($email)) {
            $this->redirect('../vista/admin_main.php', 'Formato de email inválido.', 'error');
        }
        
        if (!in_array($role, ['admin', 'usuario'])) {
            $this->redirect('../vista/admin_main.php', 'Rol inválido.', 'error');
        }
        
        if ($this->adminModel->updateUser($userId, $email, $password, $role)) {
            $this->redirect('../vista/admin_main.php', 'Usuario actualizado correctamente.');
        } else {
            $this->redirect('../vista/admin_main.php', 'Error al actualizar el usuario.', 'error');
        }
    }
    
    /**
     * Elimina un usuario por su ID
     */
    private function deleteUser() {
        if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
            $this->redirect('../vista/admin_main.php', 'ID de usuario inválido.', 'error');
        }
        
        $userId = (int) $_POST['user_id'];
        
        if ($userId == $_SESSION['user_id']) {
            $this->redirect('../vista/admin_main.php', 'No puede eliminar su propia cuenta.', 'error');
        }
        
        if ($this->adminModel->deleteUser($userId)) {
            $this->redirect('../vista/admin_main.php', 'Usuario eliminado correctamente.');
        } else {
            $this->redirect('../vista/admin_main.php', 'Error al eliminar el usuario.', 'error');
        }
    }
    
    /**
     * Actualiza el rol de un usuario
     */
    private function updateUserRole() {
        if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
            $this->redirect('../vista/admin_main.php', 'ID de usuario inválido.', 'error');
        }
        
        $userId = (int) $_POST['user_id'];
        $role = $_POST['role'] ?? 'usuario';
        
        if (!in_array($role, ['admin', 'usuario'])) {
            $this->redirect('../vista/admin_main.php', 'Rol inválido.', 'error');
        }
        
        if ($this->adminModel->updateUserRole($userId, $role)) {
            $this->redirect('../vista/admin_main.php', 'Rol de usuario actualizado correctamente.');
        } else {
            $this->redirect('../vista/admin_main.php', 'Error al actualizar el rol del usuario.', 'error');
        }
    }
    
    /**
     * Devuelve estadísticas del dashboard en formato JSON para AJAX
     */
    private function fetchStats() {
        header('Content-Type: application/json');
        echo json_encode([
            'total_users' => $this->adminModel->getTotalUsers(),
            'today_logins' => $this->adminModel->getTodayLogins()
        ]);
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

// Crear instancia y procesar la solicitud
$controller = new AdminController();
$controller->processRequest();

    
?>