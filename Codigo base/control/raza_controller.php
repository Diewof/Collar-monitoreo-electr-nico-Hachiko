<?php
// Desactivar la salida de errores de PHP
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../modelo/mascota_model.php';
require_once __DIR__ . '/BaseController.php';

class RazaController extends BaseController {
    private $mascotaModel;
    
    public function __construct() {
        parent::__construct();
        $this->mascotaModel = new MascotaModel();
    }
    
    public function processRequest() {
        try {
            error_log("RazaController: Iniciando processRequest");
            $action = $_GET['action'] ?? '';
            error_log("RazaController: Action recibida: " . $action);
            
            switch ($action) {
                case 'get_razas':
                    $this->getRazas();
                    break;
                    
                default:
                    error_log("RazaController: Acción no válida: " . $action);
                    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
        } catch (Exception $e) {
            error_log("Error en processRequest: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }
    
    private function getRazas() {
        error_log("RazaController: Iniciando getRazas");
        try {
            $razas = $this->mascotaModel->getRazas();
            error_log("RazaController: Razas obtenidas: " . print_r($razas, true));
            
            if ($razas === false) {
                error_log("RazaController: Error al obtener razas");
                $_SESSION['notification'] = [
                    'message' => 'Error al obtener razas',
                    'type' => 'error'
                ];
                header('Location: /collar/vista/main.php');
                exit;
            }
            
            echo json_encode(['success' => true, 'data' => $razas]);
        } catch (Exception $e) {
            error_log("Error en getRazas: " . $e->getMessage());
            $_SESSION['notification'] = [
                'message' => 'Error al procesar la solicitud',
                'type' => 'error'
            ];
            header('Location: /collar/vista/main.php');
            exit;
        }
    }
}

// Crear instancia y procesar la solicitud
$controller = new RazaController();
$controller->processRequest(); 