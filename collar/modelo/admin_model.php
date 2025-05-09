<?php
class AdminModel {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Obtiene el número total de usuarios registrados
     */
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            return $row['total'];
        }
        
        return 0;
    }
    
    /**
     * Obtiene el número de inicios de sesión exitosos para hoy
     */
    public function getTodayLogins() {
        $query = "SELECT COUNT(*) as total FROM users 
                  WHERE DATE(last_login) = CURDATE()";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            return $row['total'];
        }
        
        return 0;
    }
    
    /**
     * Obtiene el número de intentos fallidos de inicio de sesión para hoy
     */
    public function getFailedLoginAttempts() {
        $query = "SELECT COUNT(*) as total FROM login_attempts 
                  WHERE DATE(attempt_time) = CURDATE()";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            return $row['total'];
        }
        
        return 0;
    }
    
    /**
     * Obtiene el número de cuentas bloqueadas (simulado)
     * En un sistema real, habría que añadir un campo status a la tabla users
     */
    public function getBlockedAccounts() {
        // En esta implementación básica, simulamos que no hay cuentas bloqueadas
        // En un sistema real, podrías tener una consulta como:
        // SELECT COUNT(*) as total FROM users WHERE status = 'blocked'
        return 0;
    }
    
    /**
     * Obtiene la lista de usuarios para mostrar en la tabla
     */
    public function getUsers($limit = 10, $offset = 0) {
        $query = "SELECT id, email, role, created_at, last_login 
                  FROM users 
                  ORDER BY id DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Obtiene la actividad reciente de usuarios (inicios de sesión y registros)
     */
    public function getRecentActivity($limit = 5) {
        $activities = [];
        
        // Obtener inicios de sesión recientes
        $loginQuery = "SELECT email, last_login AS time, 'login' AS type 
                       FROM users 
                       WHERE last_login IS NOT NULL 
                       ORDER BY last_login DESC 
                       LIMIT ?";
        
        $stmt = $this->conn->prepare($loginQuery);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        // Obtener registros recientes
        $registerQuery = "SELECT email, created_at AS time, 'register' AS type 
                         FROM users 
                         ORDER BY created_at DESC 
                         LIMIT ?";
        
        $stmt = $this->conn->prepare($registerQuery);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        // Obtener intentos fallidos de inicio de sesión
        $failedQuery = "SELECT email, attempt_time AS time, 'failed' AS type, ip_address 
                        FROM login_attempts 
                        ORDER BY attempt_time DESC 
                        LIMIT ?";
        
        $stmt = $this->conn->prepare($failedQuery);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        // Ordenar actividades por fecha/hora, más recientes primero
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        // Limitar al número total de actividades solicitadas
        return array_slice($activities, 0, $limit);
    }
    
    /**
     * Elimina un usuario por su ID
     */
    public function deleteUser($userId) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Actualiza el rol de un usuario
     */
    public function updateUserRole($userId, $role) {
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $role, $userId);
        
        return $stmt->execute();
    }

    /**
 * Actualiza los datos de un usuario
 */
public function updateUser($userId, $email, $password, $role) {
    // Si no se proporciona contraseña, solo actualizar email y rol
    if (empty($password)) {
        $query = "UPDATE users SET email = ?, role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $email, $role, $userId);
        return $stmt->execute();
    }
    
    // Si se proporciona contraseña, actualizarla también
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET email = ?, password = ?, role = ? WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("sssi", $email, $hashedPassword, $role, $userId);
    return $stmt->execute();
}
}
?>