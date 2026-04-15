<?php
require_once '../conexion/conexion.php';
require_once 'BaseController.php';

class PlanController extends BaseController {
    private $conn;
    
    public function __construct() {
        parent::__construct();
        global $conexion;
        $this->conn = $conexion;
    }
    
    public function processRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_planes':
                $this->getPlanes();
                break;
                
            default:
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
    
    private function getPlanes() {
        $query = "SELECT plan_id, nombre_plan, descripcion, costo FROM plan ORDER BY nombre_plan";
        $result = $this->conn->query($query);
        
        if (!$result) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Error al obtener los planes']);
            return;
        }
        
        $planes = [];
        while ($row = $result->fetch_assoc()) {
            $planes[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($planes);
    }
}

// Crear instancia y procesar la solicitud
$controller = new PlanController();
$controller->processRequest();
?> 