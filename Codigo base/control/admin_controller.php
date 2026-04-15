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
                
            case 'get_user_data':
                $this->getUserData();
                break;
                
            case 'register_user':
                $this->registerUser();
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
        
        // Datos del propietario
        $propietarioId = $_POST['propietario_id'] ?? null;
        $primerNombre = $_POST['primer_nombre'] ?? '';
        $segundoNombre = $_POST['segundo_nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $segundoApellido = $_POST['segundo_apellido'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $paisId = $_POST['pais'] ?? null;
        $departamentoId = $_POST['departamento'] ?? null;
        $ciudadId = $_POST['ciudad'] ?? null;
        $planId = $_POST['plan_id'] ?? null;
        
        if (!$this->validateEmail($email)) {
            $this->redirect('../vista/admin_main.php', 'Formato de email inválido.', 'error');
        }
        
        if (!in_array($role, ['admin', 'usuario'])) {
            $this->redirect('../vista/admin_main.php', 'Rol inválido.', 'error');
        }
        
        try {
            // Iniciar transacción
            $this->adminModel->beginTransaction();
            
            // Actualizar usuario
            if (!$this->adminModel->updateUser($userId, $email, $password, $role)) {
                throw new Exception("Error al actualizar el usuario");
            }
            
            // Actualizar o insertar propietario
            if ($propietarioId) {
                // Actualizar propietario existente
                if (!$this->adminModel->updatePropietario(
                    $propietarioId,
                    $primerNombre,
                    $segundoNombre,
                    $apellido,
                    $segundoApellido,
                    $telefono,
                    $direccion,
                    $paisId,
                    $departamentoId,
                    $ciudadId,
                    $planId
                )) {
                    throw new Exception("Error al actualizar los datos del propietario");
                }
            } else if (!empty($primerNombre) || !empty($apellido)) {
                // Insertar nuevo propietario
                if (!$this->adminModel->insertPropietario(
                    $userId,
                    $primerNombre,
                    $segundoNombre,
                    $apellido,
                    $segundoApellido,
                    $telefono,
                    $direccion,
                    $paisId,
                    $departamentoId,
                    $ciudadId,
                    $planId
                )) {
                    throw new Exception("Error al insertar los datos del propietario");
                }
            }
            
            // Confirmar transacción
            $this->adminModel->commit();
            $this->redirect('../vista/admin_main.php', 'Usuario actualizado correctamente.');
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->adminModel->rollback();
            $this->redirect('../vista/admin_main.php', 'Error al actualizar: ' . $e->getMessage(), 'error');
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
     * Obtiene los datos de un usuario para edición
     */
    private function getUserData() {
        if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID de usuario inválido']);
            return;
        }
        
        $userId = (int)$_GET['user_id'];
        
        try {
            $sql = "SELECT u.id, u.email, u.role,
                           p.propietario_id, p.primer_nombre, p.segundo_nombre, p.apellido, p.segundo_apellido,
                           p.telefono, p.email as propietario_email, p.plan_id, p.residencia_id,
                           pl.nombre_plan, pl.descripcion as plan_descripcion, pl.costo as plan_costo,
                           r.direccion, c.ciudad_id, c.nombre as nombre_ciudad,
                           d.departamento_id, d.nombre as nombre_departamento,
                           pa.pais_id, pa.nombre as nombre_pais
                    FROM users u 
                    LEFT JOIN propietario p ON u.id = p.user_id
                    LEFT JOIN plan pl ON p.plan_id = pl.plan_id
                    LEFT JOIN residencia r ON p.residencia_id = r.residencia_id
                    LEFT JOIN ciudad c ON r.ciudad_id = c.ciudad_id
                    LEFT JOIN departamento d ON c.departamento_id = d.departamento_id
                    LEFT JOIN pais pa ON d.pais_id = pa.pais_id
                    WHERE u.id = ?";
            
            $stmt = $this->adminModel->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Organizar los datos del propietario
                if ($row['propietario_id']) {
                    $row['propietario'] = [
                        'propietario_id' => $row['propietario_id'],
                        'primer_nombre' => $row['primer_nombre'],
                        'segundo_nombre' => $row['segundo_nombre'],
                        'apellido' => $row['apellido'],
                        'segundo_apellido' => $row['segundo_apellido'],
                        'telefono' => $row['telefono'],
                        'email' => $row['propietario_email'],
                        'direccion' => [
                            'direccion' => $row['direccion'],
                            'pais_id' => $row['pais_id'],
                            'pais' => $row['nombre_pais'],
                            'departamento_id' => $row['departamento_id'],
                            'departamento' => $row['nombre_departamento'],
                            'ciudad_id' => $row['ciudad_id'],
                            'ciudad' => $row['nombre_ciudad']
                        ],
                        'plan_id' => $row['plan_id']
                    ];
                }
                
                // Eliminar las columnas duplicadas
                unset($row['propietario_id'], $row['primer_nombre'], $row['segundo_nombre'], 
                      $row['apellido'], $row['segundo_apellido'], $row['telefono'], 
                      $row['propietario_email'], $row['plan_id'], $row['nombre_plan'], 
                      $row['plan_descripcion'], $row['plan_costo'], $row['direccion'],
                      $row['nombre_ciudad'], $row['residencia_id'], $row['ciudad_id'],
                      $row['departamento_id'], $row['nombre_departamento'],
                      $row['pais_id'], $row['nombre_pais']);
                
                header('Content-Type: application/json');
                echo json_encode($row);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
            
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Error al obtener los datos del usuario']);
        }
    }
    
    /**
     * Obtiene los datos necesarios para el dashboard
     */
    public function getDashboardData() {
        try {
            // Obtener usuarios con sus datos de propietario
            $sql = "SELECT u.id, u.email, u.role, 
                           p.propietario_id, p.primer_nombre, p.segundo_nombre, p.apellido, p.segundo_apellido, 
                           p.telefono, p.email as propietario_email, p.plan_id, p.residencia_id,
                           pl.nombre_plan, pl.descripcion as plan_descripcion, pl.costo as plan_costo,
                           r.direccion, c.nombre as nombre_ciudad
                    FROM users u 
                    LEFT JOIN propietario p ON u.id = p.user_id
                    LEFT JOIN plan pl ON p.plan_id = pl.plan_id
                    LEFT JOIN residencia r ON p.residencia_id = r.residencia_id
                    LEFT JOIN ciudad c ON r.ciudad_id = c.ciudad_id
                    ORDER BY u.id DESC";
            
            error_log("SQL Query: " . $sql); // Log para depuración
            
            $result = $this->adminModel->query($sql);
            if (!$result) {
                error_log("Error en la consulta: " . $this->adminModel->getLastError());
                throw new Exception("Error al obtener los datos de usuarios");
            }
            
            $users = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Organizar los datos del propietario
                    if ($row['propietario_id']) {
                        $row['propietario'] = [
                            'propietario_id' => $row['propietario_id'],
                            'primer_nombre' => $row['primer_nombre'],
                            'segundo_nombre' => $row['segundo_nombre'],
                            'apellido' => $row['apellido'],
                            'segundo_apellido' => $row['segundo_apellido'],
                            'telefono' => $row['telefono'],
                            'email' => $row['propietario_email'],
                            'direccion' => [
                                'direccion' => $row['direccion'],
                                'ciudad' => $row['nombre_ciudad']
                            ],
                            'plan' => [
                                'plan_id' => $row['plan_id'],
                                'nombre_plan' => $row['nombre_plan'],
                                'descripcion' => $row['plan_descripcion'],
                                'costo' => $row['plan_costo']
                            ]
                        ];
                    }
                    
                    // Eliminar las columnas duplicadas
                    unset($row['propietario_id'], $row['primer_nombre'], $row['segundo_nombre'], 
                          $row['apellido'], $row['segundo_apellido'], $row['telefono'], 
                          $row['propietario_email'], $row['plan_id'], $row['nombre_plan'], 
                          $row['plan_descripcion'], $row['plan_costo'], $row['direccion'],
                          $row['nombre_ciudad'], $row['residencia_id']);
                    
                    $users[] = $row;
                }
            }
            
            error_log("Número de usuarios encontrados: " . count($users)); // Log para depuración
            
            // Obtener estadísticas
            $totalUsers = count($users);
            $todayLogins = $this->adminModel->getTodayLogins();
            $failedAttempts = $this->adminModel->getFailedLoginAttempts();
            $blockedAccounts = $this->adminModel->getBlockedAccounts();
            $recentActivity = $this->adminModel->getRecentActivity();
            
            return [
                'users' => $users,
                'total_users' => $totalUsers,
                'today_logins' => $todayLogins,
                'failed_attempts' => $failedAttempts,
                'blocked_accounts' => $blockedAccounts,
                'recent_activity' => $recentActivity
            ];
            
        } catch (Exception $e) {
            error_log("Error en getDashboardData: " . $e->getMessage());
            return [
                'users' => [],
                'total_users' => 0,
                'today_logins' => 0,
                'failed_attempts' => 0,
                'blocked_accounts' => 0,
                'recent_activity' => []
            ];
        }
    }

    /**
     * Registra un nuevo usuario desde el panel de administración
     */
    private function registerUser() {
        $email = $this->sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'usuario';
        
        // Validar campos requeridos
        if (!$this->validateRequiredFields(['email', 'password', 'confirm_password', 'role'], $_POST)) {
            $this->redirect('../vista/admin_main.php', 'Por favor, complete todos los campos requeridos.', 'error');
        }
        
        // Validar formato de email
        if (!$this->validateEmail($email)) {
            $this->redirect('../vista/admin_main.php', 'Formato de email inválido.', 'error');
        }
        
        // Validar que las contraseñas coincidan
        if ($password !== $confirmPassword) {
            $this->redirect('../vista/admin_main.php', 'Las contraseñas no coinciden.', 'error');
        }
        
        // Validar longitud de contraseña
        if (strlen($password) < 8) {
            $this->redirect('../vista/admin_main.php', 'La contraseña debe tener al menos 8 caracteres.', 'error');
        }
        
        // Validar rol
        if (!in_array($role, ['admin', 'usuario'])) {
            $this->redirect('../vista/admin_main.php', 'Rol inválido.', 'error');
        }
        
        try {
            // Registrar el usuario
            $result = $this->adminModel->registerUser($email, $password, $role);
            
            if ($result['success']) {
                $this->redirect('../vista/admin_main.php', 'Usuario creado exitosamente.');
            } else {
                $this->redirect('../vista/admin_main.php', $result['error'] ?? 'Error al crear el usuario.', 'error');
            }
        } catch (Exception $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            $this->redirect('../vista/admin_main.php', 'Error al crear el usuario. Por favor, intente nuevamente.', 'error');
        }
    }
}

// Crear instancia y procesar la solicitud
$controller = new AdminController();
$controller->processRequest();

    
?>