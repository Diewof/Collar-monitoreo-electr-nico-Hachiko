<?php
require_once 'BaseModel.php';

class MascotaModel extends BaseModel {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene todas las razas disponibles
     */
    public function getRazas() {
        try {
            error_log("MascotaModel: Intentando obtener razas");
            $query = "SELECT raza_id, nombre_raza as nombre FROM raza ORDER BY nombre_raza";
            error_log("MascotaModel: Query: " . $query);
            
            $result = $this->fetchAll($query);
            error_log("MascotaModel: Resultado: " . print_r($result, true));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error al obtener razas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un propietario tiene mascotas registradas
     */
    public function tieneMascotas($propietarioId) {
        try {
            $result = $this->fetchOne(
                "SELECT COUNT(*) as total FROM perro WHERE propietario_id = :propietario_id",
                [':propietario_id' => $propietarioId]
            );
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar mascotas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todas las mascotas de un propietario
     */
    public function getMascotasByPropietario($propietarioId) {
        try {
            $query = "SELECT p.*, r.nombre_raza as raza_nombre 
                     FROM perro p 
                     LEFT JOIN raza r ON p.raza_id = r.raza_id 
                     WHERE p.propietario_id = :propietario_id 
                     ORDER BY p.nombre";
            
            return $this->fetchAll($query, [':propietario_id' => $propietarioId]);
        } catch (Exception $e) {
            error_log("Error al obtener mascotas del propietario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda una nueva mascota
     */
    public function saveMascota($data) {
        try {
            error_log("MascotaModel: Intentando guardar mascota con datos: " . print_r($data, true));
            
            // Validar que la raza existe
            $raza = $this->fetchOne(
                "SELECT raza_id FROM raza WHERE raza_id = :raza_id",
                [':raza_id' => $data['raza_id']]
            );
            
            if (!$raza) {
                error_log("MascotaModel: Raza no encontrada: " . $data['raza_id']);
                throw new Exception("La raza seleccionada no existe");
            }
            
            // Validar que el propietario existe
            $propietario = $this->fetchOne(
                "SELECT propietario_id FROM propietario WHERE propietario_id = :propietario_id",
                [':propietario_id' => $data['propietario_id']]
            );
            
            if (!$propietario) {
                error_log("MascotaModel: Propietario no encontrado: " . $data['propietario_id']);
                throw new Exception("El propietario no existe");
            }
            
            // Validar y convertir el género
            $genero = strtoupper(substr($data['genero'], 0, 1));
            if (!in_array($genero, ['M', 'F'])) {
                throw new Exception("El género debe ser Masculino o Femenino");
            }
            
            $query = "INSERT INTO perro (nombre, fechanacimiento, peso, raza_id, propietario_id, esterilizado, genero) 
                     VALUES (:nombre, :fecha_nacimiento, :peso, :raza_id, :propietario_id, :esterilizado, :genero)";
            
            $params = [
                ':nombre' => $data['nombre'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':peso' => $data['peso'],
                ':raza_id' => $data['raza_id'],
                ':propietario_id' => $data['propietario_id'],
                ':esterilizado' => $data['esterilizado'],
                ':genero' => $genero
            ];
            
            error_log("MascotaModel: Query: " . $query);
            error_log("MascotaModel: Params: " . print_r($params, true));
            
            $result = $this->insert($query, $params);
            error_log("MascotaModel: Resultado de inserción: " . print_r($result, true));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error al guardar mascota: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los datos de una mascota específica
     */
    public function getMascotaById($mascotaId) {
        try {
            $query = "SELECT p.*, r.nombre_raza as raza_nombre 
                     FROM perro p 
                     LEFT JOIN raza r ON p.raza_id = r.raza_id 
                     WHERE p.perro_id = :mascota_id";
            
            return $this->fetchOne($query, [':mascota_id' => $mascotaId]);
        } catch (Exception $e) {
            error_log("Error al obtener mascota: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de una mascota
     */
    public function updateMascota($mascotaId, $data) {
        try {
            error_log("MascotaModel: Iniciando updateMascota");
            error_log("MascotaModel: ID de mascota: " . $mascotaId);
            error_log("MascotaModel: Datos recibidos: " . print_r($data, true));
            
            // Validar que la raza existe
            $raza = $this->fetchOne(
                "SELECT raza_id FROM raza WHERE raza_id = :raza_id",
                [':raza_id' => $data['raza_id']]
            );
            
            if (!$raza) {
                error_log("MascotaModel: Raza no encontrada: " . $data['raza_id']);
                throw new Exception("La raza seleccionada no existe");
            }
            
            // Validar y convertir el género
            $genero = strtoupper(substr($data['genero'], 0, 1));
            if (!in_array($genero, ['M', 'F'])) {
                error_log("MascotaModel: Género inválido: " . $data['genero']);
                throw new Exception("El género debe ser Masculino o Femenino");
            }
            
            $query = "UPDATE perro 
                     SET nombre = :nombre,
                         fechanacimiento = :fecha_nacimiento,
                         peso = :peso,
                         raza_id = :raza_id,
                         esterilizado = :esterilizado,
                         genero = :genero
                     WHERE perro_id = :mascota_id";
            
            $params = [
                ':nombre' => $data['nombre'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':peso' => $data['peso'],
                ':raza_id' => $data['raza_id'],
                ':esterilizado' => $data['esterilizado'],
                ':genero' => $genero,
                ':mascota_id' => $mascotaId
            ];
            
            error_log("MascotaModel: Query de actualización: " . $query);
            error_log("MascotaModel: Parámetros de actualización: " . print_r($params, true));
            
            $result = $this->update($query, $params);
            error_log("MascotaModel: Resultado de la actualización: " . print_r($result, true));
            
            if ($result === false) {
                error_log("MascotaModel: Error en la actualización - resultado false");
                return false;
            }
            
            error_log("MascotaModel: Actualización exitosa - filas afectadas: " . $result);
            return true;
        } catch (Exception $e) {
            error_log("Error en updateMascota del modelo: " . $e->getMessage());
            error_log("Error en updateMascota del modelo - Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
} 