<?php
require_once '../conexion/conexion.php';

class PropietarioModel {
    private $conn;
    
    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }
    
    /**
     * Obtiene la lista de países
     */
    public function getPaises() {
        $query = "SELECT pais_id, nombre FROM pais ORDER BY nombre";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene los departamentos de un país
     */
    public function getDepartamentos($pais_id) {
        $query = "SELECT departamento_id, nombre FROM departamento WHERE pais_id = ? ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $pais_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene las ciudades de un departamento
     */
    public function getCiudades($departamento_id) {
        $query = "SELECT ciudad_id, nombre FROM ciudad WHERE departamento_id = ? ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $departamento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene la lista de planes disponibles
     */
    public function getPlanes() {
        try {
            $sql = "SELECT plan_id, nombre_plan as nombre, descripcion, costo as precio 
                    FROM plan";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->conn->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Error al obtener resultados: " . $stmt->error);
            }
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getPlanes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Guarda los datos del propietario
     */
    public function savePropietario($data) {
        try {
            error_log("Datos recibidos en savePropietario: " . print_r($data, true));
            
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // 1. Insertar la residencia
            $residenciaQuery = "INSERT INTO residencia (direccion, ciudad_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($residenciaQuery);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de residencia: " . $this->conn->error);
            }
            
            error_log("Valores para residencia - direccion: " . $data['direccion'] . ", ciudad_id: " . $data['ciudad_id']);
            
            $stmt->bind_param("si", $data['direccion'], $data['ciudad_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de residencia: " . $stmt->error);
            }
            
            $residencia_id = $this->conn->insert_id;
            error_log("ID de residencia generado: " . $residencia_id);
            
            if (!$residencia_id) {
                throw new Exception("No se pudo obtener el ID de la residencia insertada");
            }
            
            // 2. Insertar el propietario
            $propietarioQuery = "INSERT INTO propietario (
                primer_nombre, segundo_nombre, apellido, segundo_apellido,
                telefono, email, residencia_id, plan_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($propietarioQuery);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de propietario: " . $this->conn->error);
            }
            
            error_log("Valores para propietario: " . print_r([
                'primer_nombre' => $data['primer_nombre'],
                'segundo_nombre' => $data['segundo_nombre'],
                'apellido' => $data['apellido'],
                'segundo_apellido' => $data['segundo_apellido'],
                'telefono' => $data['telefono'],
                'email' => $data['email'],
                'residencia_id' => $residencia_id,
                'plan_id' => $data['plan_id']
            ], true));
            
            $stmt->bind_param(
                "ssssssii",
                $data['primer_nombre'],
                $data['segundo_nombre'],
                $data['apellido'],
                $data['segundo_apellido'],
                $data['telefono'],
                $data['email'],
                $residencia_id,
                $data['plan_id']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de propietario: " . $stmt->error);
            }
            
            $propietario_id = $this->conn->insert_id;
            error_log("ID de propietario generado: " . $propietario_id);
            
            if (!$propietario_id) {
                throw new Exception("No se pudo obtener el ID del propietario insertado");
            }
            
            // Confirmar transacción
            $this->conn->commit();
            error_log("Transacción completada exitosamente");
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollback();
            error_log("Error en savePropietario: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function getPropietarioByUserId($userId) {
        try {
            $sql = "SELECT * FROM propietario WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener propietario por user_id: " . $e->getMessage());
            return false;
        }
    }
}
?> 