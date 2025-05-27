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

    public function getAllPlans() {
        try {
            $sql = "SELECT plan_id, nombre_plan, descripcion, costo 
                    FROM plan 
                    ORDER BY costo ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $plans = array();
            while ($row = $result->fetch_assoc()) {
                $plans[] = $row;
            }
            
            return $plans;
        } catch (Exception $e) {
            error_log("Error al obtener todos los planes: " . $e->getMessage());
            return array();
        }
    }
}
?> 