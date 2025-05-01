<?php
/**
 * Modelo de autenticación
 * Maneja la lógica de datos y la comunicación con la base de datos
 * para los procesos de autenticación
 */
require_once '../conexion/conexion.php';

class AuthModel {
    /**
     * Conexión a la base de datos
     * @var mysqli
     */
    private $db;
    
    /**
     * Constructor - establece conexión a la base de datos
     */
    public function __construct() {
        global $conexion;
        $this->db = $conexion;
        
        if ($this->db->connect_error) {
            error_log("Error de conexión a la BD: " . $this->db->connect_error);
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }
    
    /**
     * Verifica si un usuario está bloqueado por intentos fallidos de login
     * @param string $email Correo electrónico del usuario
     * @param string $ip_address Dirección IP del cliente
     * @return array Estado de bloqueo y tiempo restante
     */
    public function isUserLocked($email, $ip_address) {
        try {
            // Eliminamos registros antiguos (más de 15 minutos)
            $this->cleanupLoginAttempts();
            
            // Contamos los intentos fallidos en los últimos 15 minutos
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts, 
                       MAX(attempt_time) as last_attempt,
                       TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MAX(attempt_time), INTERVAL 15 MINUTE)) as seconds_left
                FROM login_attempts 
                WHERE (email = ? OR ip_address = ?)
                AND attempt_time > (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmt->bind_param("ss", $email, $ip_address);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            // Si hay 3 o más intentos fallidos, bloqueamos
            if ($data['attempts'] >= 3) {
                // Usamos el tiempo restante calculado directamente por MySQL
                $time_left = max(0, $data['seconds_left']);
                
                return [
                    'locked' => true,
                    'time_left' => $time_left, // Tiempo restante en segundos
                    'minutes_left' => ceil($time_left / 60) // Minutos restantes (redondeado hacia arriba)
                ];
            }
            
            return ['locked' => false];
        } catch(Exception $e) {
            error_log("Error al verificar bloqueo: " . $e->getMessage());
            // En caso de error, permitimos el acceso para evitar bloqueos permanentes
            return ['locked' => false];
        }
    }
    /**
     * Registra un intento fallido de login
     * @param string $email Correo electrónico del usuario
     * @param string $ip_address Dirección IP del cliente
     */
    public function recordFailedAttempt($email, $ip_address) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (email, ip_address) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("ss", $email, $ip_address);
            $stmt->execute();
        } catch(Exception $e) {
            error_log("Error al registrar intento fallido: " . $e->getMessage());
        }
    }
    
    /**
     * Elimina los registros de intentos fallidos para un usuario
     * @param string $email Correo electrónico del usuario
     * @param string $ip_address Dirección IP del cliente
     */
    public function clearFailedAttempts($email, $ip_address) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM login_attempts 
                WHERE email = ? OR ip_address = ?
            ");
            $stmt->bind_param("ss", $email, $ip_address);
            $stmt->execute();
        } catch(Exception $e) {
            error_log("Error al limpiar intentos fallidos: " . $e->getMessage());
        }
    }
    
    /**
     * Elimina registros antiguos de intentos fallidos (más de 15 minutos)
     */
    private function cleanupLoginAttempts() {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM login_attempts 
                WHERE attempt_time < (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmt->execute();
        } catch(Exception $e) {
            error_log("Error al limpiar intentos fallidos antiguos: " . $e->getMessage());
        }
    }
    
/**
 * Realiza el proceso de login
 * @param string $email Correo electrónico del usuario
 * @param string $password Contraseña del usuario
 * @param string $ip_address Dirección IP del cliente
 * @return array Resultado de la operación
 */
public function login($email, $password, $ip_address) {
    try {
        // Verificar si el usuario está bloqueado
        $lockStatus = $this->isUserLocked($email, $ip_address);
        if ($lockStatus['locked']) {
            return [
                'success' => false,
                'locked' => true,
                'minutes_left' => $lockStatus['minutes_left'],
                'error' => "Demasiados intentos fallidos. Cuenta bloqueada por " . $lockStatus['minutes_left'] . " minutos."
            ];
        }
        
        // Preparar la consulta usando mysqli
        $stmt = $this->db->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // Actualizar la última vez que inició sesión
            $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // Limpiar intentos fallidos al iniciar sesión correctamente
            $this->clearFailedAttempts($email, $ip_address);
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'user_role' => $user['role'] // Incluir el rol del usuario
            ];
        } else {
            // Registrar intento fallido
            $this->recordFailedAttempt($email, $ip_address);
            
            // Verificar si este intento ha causado un bloqueo
            $updatedLockStatus = $this->isUserLocked($email, $ip_address);
            if ($updatedLockStatus['locked']) {
                return [
                    'success' => false,
                    'locked' => true,
                    'minutes_left' => $updatedLockStatus['minutes_left'],
                    'error' => "Demasiados intentos fallidos. Cuenta bloqueada por " . $updatedLockStatus['minutes_left'] . " minutos."
                ];
            }
            
            // Calculamos los intentos restantes antes del bloqueo
            $attemptsLeft = 3 - $this->getFailedAttemptsCount($email, $ip_address);
            
            return [
                'success' => false,
                'error' => 'Email o contraseña incorrectos. Intentos restantes: ' . $attemptsLeft
            ];
        }
    } catch(Exception $e) {
        error_log("Error en login: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Error al iniciar sesión. Por favor, intente nuevamente.'
        ];
    }
}
    /**
     * Obtiene el número de intentos fallidos para un usuario
     * @param string $email Correo electrónico del usuario
     * @param string $ip_address Dirección IP del cliente
     * @return int Número de intentos fallidos
     */
    private function getFailedAttemptsCount($email, $ip_address) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts
                FROM login_attempts 
                WHERE (email = ? OR ip_address = ?)
                AND attempt_time > (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmt->bind_param("ss", $email, $ip_address);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            return $data['attempts'];
        } catch(Exception $e) {
            error_log("Error al contar intentos fallidos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Registra un nuevo usuario
     * @param string $email Correo electrónico del usuario
     * @param string $password Contraseña del usuario
     * @return array Resultado de la operación
     */
    public function register($email, $password) {
        try {
            // Verificar si el email ya está registrado
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => false,
                    'error' => 'Este correo electrónico ya está registrado'
                ];
            }
            
            // Generar hash de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar el nuevo usuario
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, created_at) 
                VALUES (?, ?, NOW())
            ");
            
            $stmt->bind_param("ss", $email, $hashedPassword);
            $stmt->execute();
            
            return [
                'success' => true,
                'user_id' => $this->db->insert_id
            ];
        } catch(Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al registrar el usuario. Por favor, intente nuevamente.'
            ];
        }
    }
    
    /**
     * Solicita un restablecimiento de contraseña
     * @param string $email Correo electrónico del usuario
     * @return array Resultado de la operación
     */
    public function requestPasswordReset($email) {
        try {
            // Verificar si el email existe
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // No informamos si el correo existe o no por seguridad
                return [
                    'success' => true
                ];
            }
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // Expira en 1 hora
            
            // Verificar si ya existe un token para este email
            $checkStmt = $this->db->prepare("SELECT email FROM password_resets WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Actualizar token existente
                $updateStmt = $this->db->prepare("
                    UPDATE password_resets 
                    SET token = ?, expires_at = ?, created_at = NOW() 
                    WHERE email = ?
                ");
                $updateStmt->bind_param("sss", $token, $expires, $email);
                $updateStmt->execute();
            } else {
                // Crear nuevo token
                $insertStmt = $this->db->prepare("
                    INSERT INTO password_resets (email, token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $insertStmt->bind_param("sss", $email, $token, $expires);
                $insertStmt->execute();
            }
            
            return [
                'success' => true
            ];
        } catch(Exception $e) {
            error_log("Error en solicitud de restablecimiento: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al procesar la solicitud. Por favor, intente nuevamente.'
            ];
        }
    }
    
    /**
     * Verifica si un token de restablecimiento es válido
     * @param string $token Token de restablecimiento
     * @return array Resultado de la verificación
     */
    public function verifyResetToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT email FROM password_resets 
                WHERE token = ? AND expires_at > NOW()
            ");
            
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            if ($data) {
                return [
                    'success' => true,
                    'email' => $data['email']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Token inválido o expirado'
                ];
            }
        } catch(Exception $e) {
            error_log("Error en verificación de token: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al verificar el token'
            ];
        }
    }
    
    /**
     * Cambia la contraseña de un usuario
     * @param string $email Correo del usuario
     * @param string $newPassword Nueva contraseña
     * @return array Resultado de la operación
     */
    public function resetPassword($email, $newPassword) {
        try {
            // Generar hash de la nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();
            
            // Eliminar tokens utilizados
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            return [
                'success' => true
            ];
        } catch(Exception $e) {
            error_log("Error en restablecimiento de contraseña: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al cambiar la contraseña'
            ];
        }
    }
}