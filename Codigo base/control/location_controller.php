<?php
require_once '../conexion/conexion.php';
require_once 'BaseController.php';

class LocationController extends BaseController {
    private $conn;
    
    public function __construct() {
        parent::__construct();
        global $conexion;
        $this->conn = $conexion;
    }
    
    public function processRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_paises':
                $this->getPaises();
                break;
                
            case 'get_departamentos':
                $this->getDepartamentos();
                break;
                
            case 'get_ciudades':
                $this->getCiudades();
                break;
                
            case 'get_ciudad_data':
                $this->getCiudadData();
                break;
                
            default:
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
    
    private function getPaises() {
        $query = "SELECT pais_id, nombre FROM pais ORDER BY nombre";
        $result = $this->conn->query($query);
        
        $paises = [];
        while ($row = $result->fetch_assoc()) {
            $paises[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($paises);
    }
    
    private function getDepartamentos() {
        if (!isset($_GET['pais_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID de país no proporcionado']);
            return;
        }
        
        $paisId = (int)$_GET['pais_id'];
        $query = "SELECT departamento_id, nombre FROM departamento WHERE pais_id = ? ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $paisId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $departamentos = [];
        while ($row = $result->fetch_assoc()) {
            $departamentos[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($departamentos);
    }
    
    private function getCiudades() {
        if (!isset($_GET['departamento_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID de departamento no proporcionado']);
            return;
        }
        
        $departamentoId = (int)$_GET['departamento_id'];
        $query = "SELECT ciudad_id, nombre FROM ciudad WHERE departamento_id = ? ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $departamentoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ciudades = [];
        while ($row = $result->fetch_assoc()) {
            $ciudades[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($ciudades);
    }
    
    private function getCiudadData() {
        if (!isset($_GET['ciudad_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID de ciudad no proporcionado']);
            return;
        }
        
        $ciudadId = (int)$_GET['ciudad_id'];
        $query = "SELECT c.ciudad_id, c.nombre as ciudad_nombre, 
                        d.departamento_id, d.nombre as departamento_nombre,
                        p.pais_id, p.nombre as pais_nombre
                 FROM ciudad c
                 JOIN departamento d ON c.departamento_id = d.departamento_id
                 JOIN pais p ON d.pais_id = p.pais_id
                 WHERE c.ciudad_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $ciudadId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            header('Content-Type: application/json');
            echo json_encode($row);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Ciudad no encontrada']);
        }
    }
}

// Crear instancia y procesar la solicitud
$controller = new LocationController();
$controller->processRequest();
?> 