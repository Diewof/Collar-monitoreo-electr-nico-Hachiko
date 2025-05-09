<?php
require_once '../conexion/conexion.php';

class PlanModel {
    private $conn;
    
    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }
    
    public function getPlanById($plan_id) {
        try {
            $sql = "SELECT plan_id, nombre_plan as nombre, descripcion, costo as precio 
                    FROM plan 
                    WHERE plan_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $plan_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener plan por ID: " . $e->getMessage());
            return false;
        }
    }
}
?> 