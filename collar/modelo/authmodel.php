<?php
/**
 * Modelo de autenticación
 * Maneja la lógica de datos y la comunicación con la base de datos
 * para los procesos de autenticación
 */
class AuthModel {
    /**
     * Conexión a la base de datos
     * @var PDO
     */
    private $db;
    
    /**
     * Constructor - establece conexión a la base de datos
     */
    public function __construct() {
        // Configuración de la base de datos (idealmente esto estaría en un archivo de configuración)
        $host = 'localhost';
        $dbname = 'auth_system';
        $username = 'root';
        $password = '';
        
        try {
            // Crear conexión PDO
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            
            // Configurar errores PDO
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            // En producción, no mostrar el mensaje de error directamente
            error_log("Error de conexión a la BD: " . $e->getMessage());
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
            // Preparar la consulta para buscar al usuario por email
            $stmt = $this->db->prepare("SELECT id, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                // Actualizar la última vez que inició sesión
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
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
        } catch(PDOException $e) {
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
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
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
            
            $stmt->execute([$email, $hashedPassword]);
            
            return [
                'success' => true,
                'user_id' => $this->db->lastInsertId()
            ];
        } catch(PDOException $e) {
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
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                // No informamos si el correo existe o no por seguridad
                return [
                    'success' => true
                ];
            }
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // Expira en 1 hora
            
            // Guardar token en la base de datos
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (email, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()
            ");
            
            $stmt->execute([$email, $token, $expires]);
            
            // En una aplicación real, aquí enviaríamos un correo con el enlace para restablecer la contraseña
            // Por ejemplo, usando PHPMailer o la función mail() de PHP
            
            /*
            $resetLink = "https://tudominio.com/vista/reset-password.php?token=" . $token;
            $subject = "Restablecer tu contraseña";
            $message = "Hola,\n\nPara restablecer tu contraseña, haz clic en el siguiente enlace:\n$resetLink\n\nEste enlace expirará en 1 hora.\n\nSi no solicitaste este cambio, puedes ignorar este correo.";
            $headers = "From: noreply@tudominio.com";
            
            mail($email, $subject, $message, $headers);
            */
            
            return [
                'success' => true
            ];
        } catch(PDOException $e) {
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
            
            $stmt->execute([$token]);
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'success' => true,
                    'email' => $result['email']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Token inválido o expirado'
                ];
            }
        } catch(PDOException $e) {
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
            $stmt->execute([$hashedPassword, $email]);
            
            // Eliminar tokens utilizados
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            return [
                'success' => true
            ];
        } catch(PDOException $e) {
            error_log("Error en restablecimiento de contraseña: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al cambiar la contraseña'
            ];
        }
    }
}