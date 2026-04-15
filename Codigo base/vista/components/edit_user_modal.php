<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../modelo/propietario_model.php';
require_once __DIR__ . '/../../modelo/residencia_model.php';
require_once __DIR__ . '/../../modelo/plan_model.php';

$propietarioModel = new PropietarioModel();
$residenciaModel = new ResidenciaModel();
$planModel = new PlanModel();

// Obtener lista de planes y países
$planes = $planModel->getAllPlans();
$paises = $propietarioModel->getPaises();
?>

<div id="edit-user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Usuario</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-user-form" action="../control/admin_controller.php" method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="propietario_id" id="edit_propietario_id">
                
                <!-- Datos de Usuario -->
                <div class="form-section">
                    <h3>Datos de Usuario</h3>
                    <div class="form-group">
                        <label for="edit_email">Correo Electrónico *</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required
                               maxlength="45"
                               pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                               title="El correo debe tener un formato válido">
                        <div class="error-message" id="edit_email_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El correo debe tener un formato válido</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_password">Nueva Contraseña</label>
                        <input type="password" id="edit_password" name="password" class="form-control"
                               pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,25}$"
                               title="La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número">
                        <div class="error-message" id="edit_password_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">La contraseña debe tener entre 8 y 25 caracteres, incluyendo al menos una letra y un número</span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                            <span class="strength-text">Seguridad de la contraseña</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">Rol *</label>
                        <select id="edit_role" name="role" class="form-control" required>
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                
                <!-- Datos de Propietario -->
                <div class="form-section">
                    <h3>Datos de Propietario</h3>
                    <div class="form-group">
                        <label for="edit_primer_nombre">Primer Nombre *</label>
                        <input type="text" id="edit_primer_nombre" name="primer_nombre" class="form-control" required
                               maxlength="45"
                               pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                               title="El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                        <div class="error-message" id="edit_primer_nombre_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_segundo_nombre">Segundo Nombre</label>
                        <input type="text" id="edit_segundo_nombre" name="segundo_nombre" class="form-control"
                               maxlength="45"
                               pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                               title="El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                        <div class="error-message" id="edit_segundo_nombre_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_apellido">Apellido *</label>
                        <input type="text" id="edit_apellido" name="apellido" class="form-control" required
                               maxlength="45"
                               pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                               title="El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras">
                        <div class="error-message" id="edit_apellido_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_segundo_apellido">Segundo Apellido</label>
                        <input type="text" id="edit_segundo_apellido" name="segundo_apellido" class="form-control"
                               maxlength="45"
                               pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                               title="El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras">
                        <div class="error-message" id="edit_segundo_apellido_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_telefono">Teléfono *</label>
                        <input type="tel" id="edit_telefono" name="telefono" class="form-control" required
                               maxlength="15"
                               pattern="^[0-9]{7,15}$"
                               title="El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números">
                        <div class="error-message" id="edit_telefono_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_direccion">Dirección *</label>
                        <input type="text" id="edit_direccion" name="direccion" class="form-control" required
                               maxlength="100"
                               pattern="^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$"
                               title="La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos">
                        <div class="error-message" id="edit_direccion_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_pais">País *</label>
                        <select id="edit_pais" name="pais" class="form-control" required>
                            <option value="">Seleccione un país</option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo $pais['pais_id']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="edit_pais_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">Debe seleccionar un país</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_departamento">Departamento *</label>
                        <select id="edit_departamento" name="departamento" class="form-control" required disabled>
                            <option value="">Seleccione un departamento</option>
                        </select>
                        <div class="error-message" id="edit_departamento_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">Debe seleccionar un departamento</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_ciudad">Ciudad *</label>
                        <select id="edit_ciudad" name="ciudad" class="form-control" required disabled>
                            <option value="">Seleccione una ciudad</option>
                        </select>
                        <div class="error-message" id="edit_ciudad_error">
                            <span class="error-icon">✕</span>
                            <span class="error-text">Debe seleccionar una ciudad</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_plan_id">Plan *</label>
                        <select id="edit_plan_id" name="plan_id" class="form-control" required>
                            <option value="">Seleccione un plan</option>
                            <?php foreach ($planes as $plan): ?>
                            <option value="<?php echo $plan['plan_id']; ?>">
                                <?php echo htmlspecialchars($plan['nombre_plan']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-confirmation" class="confirmation-message">
    <p>Guardando cambios...</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-user-form');
    const paisSelect = document.getElementById('edit_pais');
    const departamentoSelect = document.getElementById('edit_departamento');
    const ciudadSelect = document.getElementById('edit_ciudad');
    
    // Función para limpiar todos los mensajes de error
    function clearAllErrorMessages() {
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(element => {
            element.classList.remove('show');
        });
        
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.classList.remove('error', 'valid');
        });
    }
    
    // Función para validar un campo
    function validateField(input) {
        const errorElement = document.getElementById(`${input.id}_error`);
        let isValid = true;
        
        if (input.tagName === 'SELECT') {
            isValid = input.value !== '';
        } else {
            isValid = input.checkValidity();
        }
        
        if (isValid) {
            input.classList.remove('error');
            input.classList.add('valid');
            if (errorElement) {
                errorElement.classList.remove('show');
            }
        } else {
            input.classList.remove('valid');
            input.classList.add('error');
            if (errorElement) {
                errorElement.classList.add('show');
            }
        }
        
        return isValid;
    }
    
    // Función para validar todo el formulario
    function validateForm() {
        let isValid = true;
        
        // Validar todos los campos requeridos
        form.querySelectorAll('input[required], select[required]').forEach(field => {
            if (!field.disabled && !validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Función para cargar departamentos
    async function loadDepartamentos(paisId) {
        try {
            const response = await fetch(`../control/propietario_controller.php?action=get_departamentos&pais_id=${paisId}`);
            const data = await response.json();
            
            if (data.success) {
                departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
                data.data.forEach(depto => {
                    departamentoSelect.innerHTML += `<option value="${depto.departamento_id}">${depto.nombre}</option>`;
                });
                departamentoSelect.disabled = false;
                ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                ciudadSelect.disabled = true;
                validateForm();
            } else {
                console.error('Error:', data.error);
                alert('Error al cargar los departamentos');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cargar los departamentos');
        }
    }
    
    // Función para cargar ciudades
    async function loadCiudades(departamentoId) {
        try {
            const response = await fetch(`../control/propietario_controller.php?action=get_ciudades&departamento_id=${departamentoId}`);
            const data = await response.json();
            
            if (data.success) {
                ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                data.data.forEach(ciudad => {
                    ciudadSelect.innerHTML += `<option value="${ciudad.ciudad_id}">${ciudad.nombre}</option>`;
                });
                ciudadSelect.disabled = false;
                validateForm();
            } else {
                console.error('Error:', data.error);
                alert('Error al cargar las ciudades');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cargar las ciudades');
        }
    }
    
    // Cargar departamentos cuando se selecciona un país
    paisSelect.addEventListener('change', function() {
        const paisId = this.value;
        validateField(this);
        
        if (paisId) {
            loadDepartamentos(paisId);
        } else {
            departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
            departamentoSelect.disabled = true;
            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
            ciudadSelect.disabled = true;
            validateForm();
        }
    });
    
    // Cargar ciudades cuando se selecciona un departamento
    departamentoSelect.addEventListener('change', function() {
        const deptoId = this.value;
        validateField(this);
        
        if (deptoId) {
            loadCiudades(deptoId);
        } else {
            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
            ciudadSelect.disabled = true;
            validateForm();
        }
    });
    
    // Función para cargar los datos iniciales de ubicación
    async function loadInitialLocationData(paisId, departamentoId, ciudadId) {
        clearAllErrorMessages(); // Limpiar mensajes de error antes de cargar nuevos datos
        
        if (paisId) {
            await loadDepartamentos(paisId);
            if (departamentoId) {
                departamentoSelect.value = departamentoId;
                await loadCiudades(departamentoId);
                if (ciudadId) {
                    ciudadSelect.value = ciudadId;
                }
            }
        }
    }
    
    // Agregar eventos de validación a cada campo
    form.querySelectorAll('input, select').forEach(field => {
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
    
    // Función para abrir el modal
    window.openEditModal = function() {
        const modal = document.getElementById('edit-user-modal');
        modal.style.display = 'block';
        clearAllErrorMessages(); // Limpiar mensajes de error al abrir el modal
        
        // Configurar el botón de cierre
        const closeBtn = modal.querySelector('.close');
        closeBtn.onclick = closeEditModal;
        
        // Cerrar al hacer clic fuera del modal
        window.onclick = function(event) {
            if (event.target === modal) {
                closeEditModal();
            }
        };
    };
    
    // Función para cerrar el modal
    window.closeEditModal = function() {
        const modal = document.getElementById('edit-user-modal');
        modal.style.display = 'none';
        clearAllErrorMessages(); // Limpiar mensajes de error al cerrar el modal
    };
    
    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('../control/admin_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    // Esperar 2 segundos antes de recargar para que se muestre la notificación
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert(data.error || 'Error al guardar los datos');
                }
            } catch (e) {
                // Si la respuesta no es JSON, asumimos que fue exitosa
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Si hay un error pero los cambios se guardaron, recargamos la página
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
    });
    
    // Exponer la función loadInitialLocationData globalmente
    window.loadInitialLocationData = loadInitialLocationData;
});
</script>

<style>
.error-message {
    display: none;
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    padding: 5px;
    border-radius: 4px;
    background-color: rgba(231, 76, 60, 0.1);
}

.error-message.show {
    display: flex;
    align-items: center;
}

.error-icon {
    margin-right: 5px;
    font-weight: bold;
}

.form-control.error {
    border-color: #e74c3c;
}

.form-control.valid {
    border-color: #2ecc71;
}

/* Estilos para campos requeridos */
label[for*="*"] {
    color: #e74c3c;
}

/* Estilos para campos deshabilitados */
select:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.btn-primary.disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
    opacity: 0.7;
}

.btn-primary:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
    opacity: 0.7;
}
</style> 