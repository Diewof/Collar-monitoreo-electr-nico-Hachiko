<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../modelo/mascota_model.php';

$mascotaModel = new MascotaModel();
$mascotas = [];

if (isset($_SESSION['propietario_id'])) {
    $mascotas = $mascotaModel->getMascotasByPropietario($_SESSION['propietario_id']);
}
?>

<div id="perfilMascotaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Perfil de Mascotas</h2>
            <span class="close" id="closePerfilMascotaModal">&times;</span>
        </div>
        <div class="modal-body">
            <?php if (!empty($mascotas)): ?>
                <div class="mascotas-container">
                    <?php foreach ($mascotas as $mascota): ?>
                        <div class="mascota-card">
                            <div class="mascota-header">
                                <h3><?php echo htmlspecialchars($mascota['nombre']); ?></h3>
                                <span class="mascota-genero">
                                    <?php echo $mascota['genero'] === 'M' ? '♂' : '♀'; ?>
                                </span>
                            </div>
                            <div class="mascota-info">
                                <p><strong>Raza:</strong> <?php echo htmlspecialchars($mascota['raza_nombre']); ?></p>
                                <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($mascota['fechanacimiento'])); ?></p>
                                <p><strong>Peso:</strong> <?php echo htmlspecialchars($mascota['peso']); ?> kg</p>
                                <p><strong>Estado:</strong> <?php echo $mascota['esterilizado'] ? 'Esterilizado' : 'No esterilizado'; ?></p>
                            </div>
                            <div class="mascota-actions">
                                <button class="btn-editar" onclick="editarMascota(<?php echo $mascota['perro_id']; ?>)">Editar</button>
                                <button class="btn-ver-historial" onclick="verHistorial(<?php echo $mascota['perro_id']; ?>)">Ver Historial</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-mascotas">
                    <p>No tienes mascotas registradas.</p>
                    <button class="btn-agregar" onclick="openMascotaModal()">Agregar Mascota</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Edición de Mascota -->
<div id="editarMascotaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Mascota</h2>
            <span class="close" id="closeEditarMascotaModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editarMascotaForm" action="../control/mascota_controller.php" method="POST">
                <input type="hidden" name="action" value="update_mascota">
                <input type="hidden" name="mascota_id" id="edit_mascota_id">
                
                <div class="form-group">
                    <label for="edit_nombre">Nombre de la Mascota *</label>
                    <input type="text" id="edit_nombre" name="nombre" class="form-control" required
                           maxlength="45"
                           pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                           title="El nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                    <div class="error-message" id="edit_nombre_error">
                        <span class="error-icon">✕</span>
                        <span class="error-text">El nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_fecha_nacimiento">Fecha de Nacimiento *</label>
                    <input type="date" id="edit_fecha_nacimiento" name="fecha_nacimiento" class="form-control" required
                           max="<?php echo date('Y-m-d'); ?>">
                    <div class="error-message" id="edit_fecha_nacimiento_error">
                        <span class="error-icon">✕</span>
                        <span class="error-text">Debe seleccionar una fecha válida</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_peso">Peso (kg) *</label>
                    <input type="number" id="edit_peso" name="peso" class="form-control" required
                           min="0.1" max="100" step="0.1"
                           title="El peso debe estar entre 0.1 y 100 kg">
                    <div class="error-message" id="edit_peso_error">
                        <span class="error-icon">✕</span>
                        <span class="error-text">El peso debe estar entre 0.1 y 100 kg</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_raza">Raza *</label>
                    <select id="edit_raza" name="raza_id" class="form-control" required>
                        <option value="">Seleccione una raza</option>
                    </select>
                    <div class="error-message" id="edit_raza_error">
                        <span class="error-icon">✕</span>
                        <span class="error-text">Debe seleccionar una raza</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_genero">Género *</label>
                    <select id="edit_genero" name="genero" class="form-control" required>
                        <option value="">Seleccione el género</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                    <div class="error-message" id="edit_genero_error">
                        <span class="error-icon">✕</span>
                        <span class="error-text">Debe seleccionar el género</span>
                    </div>
                </div>
                
                <div class="form-group checkbox-group">
                    <label for="edit_esterilizado">
                        <input type="checkbox" id="edit_esterilizado" name="esterilizado" value="1">
                        ¿Está esterilizado?
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="edit-submit-btn">Guardar Cambios</button>
                </div>
            </form>
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
    margin: 2% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 800px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

.mascotas-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 10px;
}

.mascota-card {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.mascota-card:hover {
    transform: translateY(-2px);
}

.mascota-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.mascota-header h3 {
    margin: 0;
    color: #333;
}

.mascota-genero {
    font-size: 1.2em;
    color: #666;
}

.mascota-info {
    margin-bottom: 15px;
}

.mascota-info p {
    margin: 5px 0;
    color: #666;
}

.mascota-info strong {
    color: #333;
}

.mascota-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-editar, .btn-ver-historial {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.btn-editar {
    background-color: #4CAF50;
    color: white;
}

.btn-ver-historial {
    background-color: #2196F3;
    color: white;
}

.btn-editar:hover {
    background-color: #45a049;
}

.btn-ver-historial:hover {
    background-color: #1976D2;
}

.no-mascotas {
    text-align: center;
    padding: 30px;
}

.no-mascotas p {
    color: #666;
    margin-bottom: 20px;
}

.btn-agregar {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}

.btn-agregar:hover {
    background-color: #45a049;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
    
    .mascotas-container {
        grid-template-columns: 1fr;
    }
}

/* Estilos adicionales para el modal de edición */
#editarMascotaModal .modal-content {
    max-width: 600px;
}

#editarMascotaModal .form-group {
    margin-bottom: 20px;
}

#editarMascotaModal .form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

#editarMascotaModal .form-control:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

#editarMascotaModal .error-message {
    display: none;
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    padding: 8px;
    border-radius: 4px;
    background-color: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.2);
}

#editarMascotaModal .error-message.show {
    display: flex;
    align-items: center;
}

#editarMascotaModal .checkbox-group {
    display: flex;
    align-items: center;
}

#editarMascotaModal .checkbox-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
}

#editarMascotaModal .checkbox-group input[type="checkbox"] {
    margin-right: 8px;
    width: 16px;
    height: 16px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const perfilModal = document.getElementById('perfilMascotaModal');
    const editarModal = document.getElementById('editarMascotaModal');
    const closePerfilBtn = document.getElementById('closePerfilMascotaModal');
    const closeEditarBtn = document.getElementById('closeEditarMascotaModal');
    const editarForm = document.getElementById('editarMascotaForm');
    const submitBtn = document.getElementById('edit-submit-btn');
    const razaSelect = document.getElementById('edit_raza');
    
    // Función para abrir el modal de perfil
    window.openPerfilMascotaModal = function() {
        perfilModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Cerrar modales
    function closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    closePerfilBtn.onclick = () => closeModal(perfilModal);
    closeEditarBtn.onclick = () => closeModal(editarModal);
    
    // Cerrar modales al hacer clic fuera del contenido
    window.onclick = function(event) {
        if (event.target == perfilModal) closeModal(perfilModal);
        if (event.target == editarModal) closeModal(editarModal);
    }
    
    // Función para cargar las razas
    async function loadRazas() {
        try {
            const response = await fetch('/collar/control/raza_controller.php?action=get_razas');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                razaSelect.innerHTML = '<option value="">Seleccione una raza</option>';
                data.data.forEach(raza => {
                    razaSelect.innerHTML += `<option value="${raza.raza_id}">${raza.nombre}</option>`;
                });
            } else {
                throw new Error(data.error || 'Error al cargar las razas');
            }
        } catch (error) {
            console.error('Error:', error);
            window.location.href = '/collar/vista/main.php';
        }
    }
    
    // Función para validar un campo
    function validateField(field) {
        const errorElement = document.getElementById(`${field.id}_error`);
        
        if (field.validity.valid) {
            field.classList.remove('invalid');
            field.classList.add('valid');
            if (errorElement) errorElement.style.display = 'none';
            return true;
        } else {
            field.classList.remove('valid');
            field.classList.add('invalid');
            if (errorElement) errorElement.style.display = 'block';
            return false;
        }
    }
    
    // Función para validar todo el formulario
    function validateForm() {
        let isValid = true;
        editarForm.querySelectorAll('input[required], select[required]').forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        submitBtn.disabled = !isValid;
        submitBtn.classList.toggle('disabled', !isValid);
        return isValid;
    }
    
    // Agregar eventos de validación a cada campo
    editarForm.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
            validateField(this);
            validateForm();
        });
        
        field.addEventListener('blur', function() {
            validateField(this);
            validateForm();
        });
        
        field.addEventListener('change', function() {
            validateField(this);
            validateForm();
        });
    });
    
    // Función para editar mascota
    window.editarMascota = async function(mascotaId) {
        try {
            // Cargar razas primero
            await loadRazas();
            
            // Obtener datos de la mascota
            const formData = new FormData();
            formData.append('action', 'get_mascota');
            formData.append('mascota_id', mascotaId);
            
            const response = await fetch('../control/mascota_controller.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                const mascota = data.data;
                
                // Llenar el formulario con los datos
                document.getElementById('edit_mascota_id').value = mascota.perro_id;
                document.getElementById('edit_nombre').value = mascota.nombre;
                document.getElementById('edit_fecha_nacimiento').value = mascota.fechanacimiento;
                document.getElementById('edit_peso').value = mascota.peso;
                document.getElementById('edit_raza').value = mascota.raza_id;
                document.getElementById('edit_genero').value = mascota.genero;
                document.getElementById('edit_esterilizado').checked = mascota.esterilizado == 1;
                
                // Validar el formulario
                validateForm();
                
                // Mostrar el modal de edición
                editarModal.style.display = 'block';
                perfilModal.style.display = 'none';
            } else {
                throw new Error(data.error || 'Error al obtener los datos de la mascota');
            }
        } catch (error) {
            console.error('Error:', error);
            window.location.href = '/collar/vista/main.php';
        }
    }
    
    // Manejar el envío del formulario de edición
    editarForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('Formulario de edición enviado');
        
        if (!validateForm()) {
            console.log('Validación del formulario falló');
            return;
        }
        
        const formData = new FormData(this);
        console.log('Datos del formulario:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        try {
            console.log('Enviando solicitud de actualización...');
            const response = await fetch('../control/mascota_controller.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Respuesta recibida:', response);
            
            const data = await response.json();
            console.log('Datos de respuesta:', data);
            
            if (data.success) {
                console.log('Actualización exitosa');
                // Esperar 2 segundos antes de redirigir para poder ver los mensajes
                setTimeout(() => {
                    window.location.href = '/collar/vista/main.php';
                }, 2000);
            } else {
                console.error('Error en la actualización:', data.error);
                alert('Error al actualizar la mascota: ' + (data.error || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error en la actualización:', error);
            alert('Error al actualizar la mascota: ' + error.message);
        }
    });
    
    // Validar el formulario al cargar
    validateForm();
});

function verHistorial(mascotaId) {
    // Implementar la lógica para ver historial
    console.log('Ver historial:', mascotaId);
}

function openMascotaModal() {
    // Implementar la lógica para abrir el modal de nueva mascota
    const mascotaModal = document.getElementById('mascotaModal');
    if (mascotaModal) {
        mascotaModal.style.display = 'block';
    }
}
</script> 