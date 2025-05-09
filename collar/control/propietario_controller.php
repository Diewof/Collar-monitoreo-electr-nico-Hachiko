<?php
require_once '../modelo/propietario_model.php';
require_once 'BaseController.php';

class PropietarioController extends BaseController {
    private $propietarioModel;
    
    public function __construct() {
        parent::__construct();
        $this->propietarioModel = new PropietarioModel();
    }
    
    /**
     * Procesa la acción solicitada
     */
    public function processRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        switch ($action) {
            case 'get_departamentos':
                $this->getDepartamentos();
                break;
                
            case 'get_ciudades':
                $this->getCiudades();
                break;
                
            case 'save_propietario':
                $this->savePropietario();
                break;
                
            default:
                $this->sendJsonResponse(['success' => false, 'error' => 'Acción no válida']);
        }
    }
    
    /**
     * Obtiene los departamentos de un país
     */
    private function getDepartamentos() {
        if (!isset($_GET['pais_id']) || !is_numeric($_GET['pais_id'])) {
            $this->sendJsonResponse(['success' => false, 'error' => 'ID de país inválido']);
            return;
        }
        
        $departamentos = $this->propietarioModel->getDepartamentos($_GET['pais_id']);
        if ($departamentos === false) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Error al obtener departamentos']);
            return;
        }
        
        $this->sendJsonResponse(['success' => true, 'data' => $departamentos]);
    }
    
    /**
     * Obtiene las ciudades de un departamento
     */
    private function getCiudades() {
        if (!isset($_GET['departamento_id']) || !is_numeric($_GET['departamento_id'])) {
            $this->sendJsonResponse(['success' => false, 'error' => 'ID de departamento inválido']);
            return;
        }
        
        $ciudades = $this->propietarioModel->getCiudades($_GET['departamento_id']);
        if ($ciudades === false) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Error al obtener ciudades']);
            return;
        }
        
        $this->sendJsonResponse(['success' => true, 'data' => $ciudades]);
    }
    
    /**
     * Guarda los datos del propietario
     */
    private function savePropietario() {
        try {
            // Verificar que todos los campos requeridos estén presentes
            $requiredFields = [
                'primer_nombre', 'apellido', 'telefono', 'direccion',
                'pais', 'departamento', 'ciudad', 'plan'
            ];
            
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->sendJsonResponse(['success' => false, 'error' => "El campo $field es requerido"]);
                    return;
                }
            }
            
            // Preparar los datos
            $data = [
                'primer_nombre' => trim($_POST['primer_nombre']),
                'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
                'apellido' => trim($_POST['apellido']),
                'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
                'telefono' => trim($_POST['telefono']),
                'email' => $_SESSION['user_email'],
                'direccion' => trim($_POST['direccion']),
                'ciudad_id' => (int)$_POST['ciudad'],
                'plan_id' => (int)$_POST['plan']
            ];
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9+\-() ]{7,15}$/', $data['telefono'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de teléfono inválido']);
                return;
            }
            
            // Guardar los datos
            if ($this->propietarioModel->savePropietario($data)) {
                // Limpiar la bandera de primer inicio de sesión
                unset($_SESSION['is_first_login']);
                
                // Agregar notificación de confirmación
                $_SESSION['success'] = '¡Datos de propietario guardados correctamente!';
                
                $this->sendJsonResponse([
                    'success' => true, 
                    'message' => 'Datos guardados correctamente',
                    'redirect' => '../vista/main.php'
                ]);
            } else {
                $this->sendJsonResponse(['success' => false, 'error' => 'Error al guardar los datos']);
            }
        } catch (Exception $e) {
            error_log("Error en savePropietario: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'error' => 'Error al procesar la solicitud']);
        }
    }
    
    /**
     * Envía una respuesta JSON
     */
    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Procesar la solicitud
$controller = new PropietarioController();
$controller->processRequest();
?> 