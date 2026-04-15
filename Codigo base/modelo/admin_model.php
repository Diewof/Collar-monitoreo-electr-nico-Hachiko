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

    /**
     * Ejecuta una consulta SQL directa
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }

    /**
     * Prepara una consulta SQL
     * @param string $sql La consulta SQL a preparar
     * @return mysqli_stmt|false El objeto de sentencia preparada o false si hay error
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    /**
     * Obtiene el último error de la base de datos
     */
    public function getLastError() {
        return $this->conn->error;
    }

    /**
     * Inicia una transacción
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    /**
     * Confirma una transacción
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * Revierte una transacción
     */
    public function rollback() {
        $this->conn->rollback();
    }

    /**
     * Actualiza los datos de un propietario
     */
    public function updatePropietario($propietarioId, $primerNombre, $segundoNombre, $apellido, 
                                    $segundoApellido, $telefono, $direccion, $paisId, $departamentoId, 
                                    $ciudadId, $planId) {
        try {
            // Primero actualizar la residencia
            $query = "UPDATE residencia SET direccion = ?, ciudad_id = ? 
                     WHERE residencia_id = (SELECT residencia_id FROM propietario WHERE propietario_id = ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sii", $direccion, $ciudadId, $propietarioId);
            
            if (!$stmt->execute()) {
                return false;
            }
            
            // Luego actualizar el propietario
            $query = "UPDATE propietario SET 
                     primer_nombre = ?, 
                     segundo_nombre = ?, 
                     apellido = ?, 
                     segundo_apellido = ?, 
                     telefono = ?,
                     plan_id = ?
                     WHERE propietario_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssii", 
                            $primerNombre, 
                            $segundoNombre, 
                            $apellido, 
                            $segundoApellido, 
                            $telefono,
                            $planId,
                            $propietarioId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en updatePropietario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta un nuevo propietario
     */
    public function insertPropietario($userId, $primerNombre, $segundoNombre, $apellido, 
                                    $segundoApellido, $telefono, $direccion, $paisId, $departamentoId, 
                                    $ciudadId, $planId) {
        try {
            // Primero insertar la residencia
            $query = "INSERT INTO residencia (direccion, ciudad_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $direccion, $ciudadId);
            
            if (!$stmt->execute()) {
                return false;
            }
            
            $residenciaId = $this->conn->insert_id;
            
            // Luego insertar el propietario
            $query = "INSERT INTO propietario (user_id, residencia_id, primer_nombre, 
                     segundo_nombre, apellido, segundo_apellido, telefono, plan_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iisssssi", 
                            $userId, 
                            $residenciaId,
                            $primerNombre, 
                            $segundoNombre, 
                            $apellido, 
                            $segundoApellido, 
                            $telefono,
                            $planId);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en insertPropietario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un nuevo usuario
     * @param string $email Correo electrónico del usuario
     * @param string $password Contraseña del usuario
     * @param string $role Rol del usuario
     * @return array Resultado de la operación
     */
    public function registerUser($email, $password, $role) {
        try {
            // Verificar si el email ya está registrado
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
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
            $stmt = $this->conn->prepare("
                INSERT INTO users (email, password, role, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("sss", $email, $hashedPassword, $role);
            $stmt->execute();
            
            return [
                'success' => true,
                'user_id' => $this->conn->insert_id
            ];
        } catch(Exception $e) {
            error_log("Error en registro de usuario: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al registrar el usuario. Por favor, intente nuevamente.'
            ];
        }
    }
}
?>