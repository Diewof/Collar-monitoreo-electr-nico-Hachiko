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
     * Realiza el proceso de login
     * @param string $email Correo electrónico del usuario
     * @param string $password Contraseña del usuario
     * @return array Resultado de la operación
     */
    public function login($email, $password) {
        try {
            // Preparar la consulta usando mysqli
            $stmt = $this->db->prepare("SELECT id, email, password FROM users WHERE email = ?");
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
                
                return [
                    'success' => true,
                    'user_id' => $user['id']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Email o contraseña incorrectos'
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