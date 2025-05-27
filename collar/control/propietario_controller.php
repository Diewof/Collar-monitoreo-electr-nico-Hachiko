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
                
            case 'update_propietario':
                $this->updatePropietario();
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
                'plan_id' => (int)$_POST['plan'],
                'user_id' => $_SESSION['user_id']
            ];
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9]{7,15}$/', $data['telefono'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de teléfono inválido']);
                return;
            }
            
            // Guardar los datos
            try {
                $propietario_id = $this->propietarioModel->savePropietario($data);
                if ($propietario_id) {
                    // Guardar el propietario_id en la sesión
                    $_SESSION['propietario_id'] = $propietario_id;
                    
                    // Limpiar la bandera de primer inicio de sesión
                    unset($_SESSION['is_first_login']);
                    
                    // Agregar notificación de confirmación
                    $_SESSION['success'] = '¡Datos de propietario guardados correctamente!';
                    
                    $this->sendJsonResponse([
                        'success' => true, 
                        'message' => 'Datos guardados correctamente',
                        'redirect' => '../vista/main.php'
                    ]);
                }
            } catch (Exception $e) {
                error_log("Error al guardar propietario: " . $e->getMessage());
                $this->sendJsonResponse([
                    'success' => false, 
                    'error' => 'Error al guardar los datos: ' . $e->getMessage()
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en savePropietario: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Actualiza los datos del propietario
     */
    private function updatePropietario() {
        try {
            // Verificar que todos los campos requeridos estén presentes
            $requiredFields = [
                'propietario_id', 'primer_nombre', 'apellido', 'telefono', 'direccion',
                'pais', 'departamento', 'ciudad', 'plan'
            ];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || $_POST[$field] === '') {
                    $this->sendJsonResponse(['success' => false, 'error' => "El campo $field es requerido"]);
                    return;
                }
            }
            
            // Preparar los datos
            $data = [
                'propietario_id' => (int)$_POST['propietario_id'],
                'primer_nombre' => trim($_POST['primer_nombre']),
                'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
                'apellido' => trim($_POST['apellido']),
                'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion']),
                'ciudad_id' => (int)$_POST['ciudad'],
                'plan_id' => (int)$_POST['plan']
            ];
            
            // Validar formato de nombres y apellidos
            $namePattern = '/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$/';
            if (!preg_match($namePattern, $data['primer_nombre'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de primer nombre inválido']);
                return;
            }
            if (!empty($data['segundo_nombre']) && !preg_match($namePattern, $data['segundo_nombre'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de segundo nombre inválido']);
                return;
            }
            if (!preg_match($namePattern, $data['apellido'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de apellido inválido']);
                return;
            }
            if (!empty($data['segundo_apellido']) && !preg_match($namePattern, $data['segundo_apellido'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de segundo apellido inválido']);
                return;
            }
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9]{7,15}$/', $data['telefono'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de teléfono inválido']);
                return;
            }
            
            // Validar formato de dirección
            if (!preg_match('/^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$/', $data['direccion'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Formato de dirección inválido']);
                return;
            }
            
            // Actualizar los datos
            try {
                if ($this->propietarioModel->updatePropietario($data)) {
                    $_SESSION['success'] = 'Perfil actualizado exitosamente';
                    $this->sendJsonResponse([
                        'success' => true, 
                        'message' => 'Perfil actualizado exitosamente',
                        'redirect' => '../vista/main.php'
                    ]);
                } else {
                    throw new Exception('Error al actualizar el perfil');
                }
            } catch (Exception $e) {
                error_log("Error al actualizar propietario: " . $e->getMessage());
                $this->sendJsonResponse([
                    'success' => false, 
                    'error' => 'Error al actualizar el perfil: ' . $e->getMessage()
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en updatePropietario: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false, 
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
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