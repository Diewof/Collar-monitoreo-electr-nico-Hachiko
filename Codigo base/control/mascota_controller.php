<?php
// Desactivar la salida de errores de PHP
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../modelo/mascota_model.php';
require_once 'BaseController.php';

class MascotaController extends BaseController {
    private $mascotaModel;
    
    public function __construct() {
        parent::__construct();
        $this->mascotaModel = new MascotaModel();
    }
    
    public function processRequest() {
        try {
            error_log("MascotaController: Iniciando processRequest");
            error_log("MascotaController: POST data: " . print_r($_POST, true));
            error_log("MascotaController: SESSION data: " . print_r($_SESSION, true));
            
            $action = $_POST['action'] ?? '';
            error_log("MascotaController: Action recibida: " . $action);
            
            switch ($action) {
                case 'save_mascota':
                    $this->saveMascota();
                    break;
                    
                case 'update_mascota':
                    error_log("MascotaController: Ejecutando update_mascota");
                    $this->updateMascota();
                    break;
                    
                case 'get_mascota':
                    $this->getMascota();
                    break;
                    
                default:
                    error_log("MascotaController: Acción no válida: " . $action);
                    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
        } catch (Exception $e) {
            error_log("Error en processRequest: " . $e->getMessage());
            error_log("Error en processRequest - Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    private function saveMascota() {
        try {
            error_log("MascotaController: Iniciando saveMascota");
            
            // Validar campos requeridos
            $requiredFields = ['nombre', 'fecha_nacimiento', 'peso', 'raza_id', 'propietario_id', 'genero'];
            foreach ($requiredFields as $field) {
                error_log("MascotaController: Validando campo: " . $field . " = " . ($_POST[$field] ?? 'no definido'));
                if (empty($_POST[$field]) && $_POST[$field] !== '0') { // Permitir valor 0 para algunos campos si aplica
                    error_log("MascotaController: Campo requerido faltante o vacío: " . $field);
                    $_SESSION['notification'] = [
                        'message' => "El campo {$field} es requerido",
                        'type' => 'error'
                    ];
                    header('Location: /collar/vista/main.php');
                    exit;
                }
            }
            
            // Preparar datos
            $data = [
                'nombre' => $_POST['nombre'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'peso' => $_POST['peso'],
                'raza_id' => $_POST['raza_id'],
                'propietario_id' => $_POST['propietario_id'],
                'esterilizado' => isset($_POST['esterilizado']) ? 1 : 0,
                'genero' => $_POST['genero']
            ];
            
            error_log("MascotaController: Datos preparados: " . print_r($data, true));
            
            // Guardar mascota
            error_log("MascotaController: Llamando a saveMascota en el modelo");
            $result = $this->mascotaModel->saveMascota($data);
            error_log("MascotaController: Resultado de saveMascota: " . print_r($result, true));
            
            if ($result) {
                error_log("MascotaController: Mascota guardada exitosamente");
                $_SESSION['notification'] = [
                    'message' => 'Mascota registrada exitosamente',
                    'type' => 'success'
                ];
                header('Location: /collar/vista/main.php');
                exit;
            } else {
                error_log("MascotaController: Error al guardar mascota - resultado false");
                $_SESSION['notification'] = [
                    'message' => 'Error al guardar la mascota',
                    'type' => 'error'
                ];
                header('Location: /collar/vista/main.php');
                exit;
            }
        } catch (Exception $e) {
            error_log("Error en saveMascota: " . $e->getMessage());
            error_log("Error en saveMascota - Stack trace: " . $e->getTraceAsString());
            $_SESSION['notification'] = [
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'type' => 'error'
            ];
            header('Location: /collar/vista/main.php');
            exit;
        }
    }

    private function getMascota() {
        try {
            $mascotaId = $_POST['mascota_id'] ?? null;
            
            if (!$mascotaId) {
                $_SESSION['notification'] = [
                    'message' => 'ID de mascota no proporcionado',
                    'type' => 'error'
                ];
                header('Location: /collar/vista/main.php');
                exit;
            }
            
            $mascota = $this->mascotaModel->getMascotaById($mascotaId);
            
            if ($mascota) {
                echo json_encode(['success' => true, 'data' => $mascota]);
            } else {
                $_SESSION['notification'] = [
                    'message' => 'Mascota no encontrada',
                    'type' => 'error'
                ];
                header('Location: /collar/vista/main.php');
                exit;
            }
        } catch (Exception $e) {
            error_log("Error en getMascota: " . $e->getMessage());
            $_SESSION['notification'] = [
                'message' => 'Error al obtener la mascota: ' . $e->getMessage(),
                'type' => 'error'
            ];
            header('Location: /collar/vista/main.php');
            exit;
        }
    }

    private function updateMascota() {
        try {
            error_log("MascotaController: Iniciando updateMascota");
            error_log("MascotaController: POST data en updateMascota: " . print_r($_POST, true));
            error_log("MascotaController: SESSION data en updateMascota: " . print_r($_SESSION, true));
            
            // Validar campos requeridos
            $requiredFields = ['mascota_id', 'nombre', 'fecha_nacimiento', 'peso', 'raza_id', 'genero'];
            foreach ($requiredFields as $field) {
                error_log("MascotaController: Validando campo {$field}: " . ($_POST[$field] ?? 'no definido'));
                if (empty($_POST[$field]) && $_POST[$field] !== '0') {
                    error_log("MascotaController: Campo requerido faltante o vacío: {$field}");
                    echo json_encode([
                        'success' => false,
                        'error' => "El campo {$field} es requerido"
                    ]);
                    return;
                }
            }
            
            // Preparar datos
            $data = [
                'nombre' => $_POST['nombre'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'peso' => $_POST['peso'],
                'raza_id' => $_POST['raza_id'],
                'esterilizado' => isset($_POST['esterilizado']) ? 1 : 0,
                'genero' => $_POST['genero']
            ];
            
            error_log("MascotaController: Datos preparados para actualización: " . print_r($data, true));
            
            // Actualizar mascota
            error_log("MascotaController: Llamando a updateMascota en el modelo con ID: " . $_POST['mascota_id']);
            $result = $this->mascotaModel->updateMascota($_POST['mascota_id'], $data);
            error_log("MascotaController: Resultado de updateMascota en el modelo: " . print_r($result, true));
            
            if ($result) {
                error_log("MascotaController: Mascota actualizada exitosamente");
                $_SESSION['notification'] = [
                    'message' => 'Mascota actualizada exitosamente',
                    'type' => 'success'
                ];
                echo json_encode(['success' => true]);
            } else {
                error_log("MascotaController: Error al actualizar mascota - resultado false");
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al actualizar la mascota'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en updateMascota: " . $e->getMessage());
            error_log("Error en updateMascota - Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
    }
}

// Crear instancia y procesar la solicitud
$controller = new MascotaController();
$controller->processRequest(); 