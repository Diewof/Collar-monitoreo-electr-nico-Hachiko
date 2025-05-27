<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../modelo/propietario_model.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó el ID del departamento
if (!isset($_GET['departamento_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de departamento no proporcionado']);
    exit;
}

$departamento_id = intval($_GET['departamento_id']);
$propietarioModel = new PropietarioModel();

try {
    $ciudades = $propietarioModel->getCiudades($departamento_id);
    header('Content-Type: application/json');
    echo json_encode($ciudades);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener ciudades: ' . $e->getMessage()]);
} 