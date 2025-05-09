<?php
require_once '../conexion/conexion.php';

class ResidenciaModel {
    private $conn;
    
    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }
    
    public function getResidenciaById($residencia_id) {
        try {
            $sql = "SELECT r.*, c.nombre as ciudad, d.nombre as departamento, p.nombre as pais 
                    FROM residencia r 
                    JOIN ciudad c ON r.ciudad_id = c.ciudad_id 
                    JOIN departamento d ON c.departamento_id = d.departamento_id 
                    JOIN pais p ON d.pais_id = p.pais_id 
                    WHERE r.residencia_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $residencia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener residencia por ID: " . $e->getMessage());
            return false;
        }
    }
}
?> 