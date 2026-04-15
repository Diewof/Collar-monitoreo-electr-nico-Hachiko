<?php
class BaseModel {
    protected $db;
    
    public function __construct() {
        try {
            // Configuración de la base de datos
            $host = 'localhost';
            $dbname = 'collar';
            $username = 'root';
            $password = '54747454';
            
            // Crear conexión PDO
            $this->db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
        }
    }
    
    /**
     * Ejecuta una consulta SQL y retorna el resultado
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error al ejecutar consulta: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta");
        }
    }
    
    /**
     * Obtiene un solo registro
     */
    protected function fetchOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene múltiples registros
     */
    protected function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Inserta un registro y retorna el ID
     */
    protected function insert($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza registros y retorna el número de filas afectadas
     */
    protected function update($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Elimina registros y retorna el número de filas afectadas
     */
    protected function delete($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt->rowCount();
    }
} 