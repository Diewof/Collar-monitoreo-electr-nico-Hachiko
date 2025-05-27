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

// Verificar si se proporcionó el ID del país
if (!isset($_GET['pais_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de país no proporcionado']);
    exit;
}

$pais_id = intval($_GET['pais_id']);
$propietarioModel = new PropietarioModel();

try {
    $departamentos = $propietarioModel->getDepartamentos($pais_id);
    header('Content-Type: application/json');
    echo json_encode($departamentos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener departamentos: ' . $e->getMessage()]);
} 