<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../modelo/propietario_model.php';
require_once __DIR__ . '/../../modelo/residencia_model.php';
require_once __DIR__ . '/../../modelo/plan_model.php';

// Obtener el ID del usuario actual
global $userId;
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $propietarioModel = new PropietarioModel();
    $residenciaModel = new ResidenciaModel();
    $planModel = new PlanModel();
    
    // Obtener datos del propietario
    $propietario = $propietarioModel->getPropietarioByUserId($userId);
    
    if ($propietario) {
        // Obtener datos de residencia
        $residencia = $residenciaModel->getResidenciaById($propietario['residencia_id']);
        // Obtener datos del plan
        $plan = $planModel->getPlanById($propietario['plan_id']);
    }
}
?>

<div id="perfilModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Mi Perfil</h2>
            <span class="close" id="closePerfilModal">&times;</span>
        </div>
        <div class="modal-body">
            <?php if (isset($propietario)): ?>
                <div class="profile-section">
                    <h3>Información Personal</h3>
                    <div class="profile-info">
                        <p><strong>Nombre completo:</strong> <?php 
                            echo htmlspecialchars(
                                trim(
                                    (
                                        ($propietario['primer_nombre'] ?? '') . ' ' .
                                        ($propietario['segundo_nombre'] ?? '') . ' ' .
                                        ($propietario['apellido'] ?? '') . ' ' .
                                        ($propietario['segundo_apellido'] ?? '')
                                    )
                                )
                            ); 
                        ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($propietario['telefono']); ?></p>
                        <?php if ($propietario['email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($propietario['email']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Dirección</h3>
                    <div class="profile-info">
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($residencia['direccion']); ?></p>
                        <p><strong>País:</strong> <?php echo htmlspecialchars($residencia['pais']); ?></p>
                        <p><strong>Departamento:</strong> <?php echo htmlspecialchars($residencia['departamento']); ?></p>
                        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($residencia['ciudad']); ?></p>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Plan Actual</h3>
                    <div class="profile-info">
                        <p><strong>Plan:</strong> <?php echo htmlspecialchars($plan['nombre']); ?></p>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($plan['descripcion']); ?></p>
                        <p><strong>Precio:</strong> $<?php echo number_format($plan['precio'], 2); ?></p>
                    </div>
                </div>

                <div class="profile-actions">
                    <button id="editProfileBtn" class="edit-profile-btn">Editar Perfil</button>
                </div>
            <?php else: ?>
                <div class="no-profile">
                    <p>No se encontró información del perfil. Por favor, complete sus datos de propietario.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: #fefefe;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 12px 16px;
    border: 1px solid #888;
    width: 95%;
    max-width: 420px;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.close {
    color: #aaa;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 16px;
    top: 10px;
    z-index: 10;
}

.close:hover {
    color: #333;
}

.profile-section {
    margin-bottom: 10px;
    padding: 8px 0 8px 0;
    background-color: #f9f9f9;
    border-radius: 5px;
}

.profile-section h3 {
    color: #4CAF50;
    margin-bottom: 8px;
    font-size: 1rem;
}

.profile-info p {
    margin: 4px 0;
    color: #666;
    line-height: 1.3;
    font-size: 0.95rem;
}

.profile-info strong {
    color: #333;
    margin-right: 3px;
}

.no-profile {
    text-align: center;
    padding: 10px;
    color: #666;
    font-size: 0.95rem;
}

.profile-actions {
    margin-top: 20px;
    text-align: center;
}

.edit-profile-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.edit-profile-btn:hover {
    background-color: #45a049;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('perfilModal');
    const closeBtn = document.getElementById('closePerfilModal');
    const editProfileBtn = document.getElementById('editProfileBtn');
    
    // Función para abrir el modal
    window.openPerfilModal = function() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Cerrar modal al hacer clic en la X
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Manejar clic en el botón de editar perfil
    editProfileBtn.onclick = function() {
        // Redirigir a la página de edición de perfil
        window.location.href = 'editar_perfil.php';
    }
});
</script> 