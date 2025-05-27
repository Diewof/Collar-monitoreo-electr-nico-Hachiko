<?php
// Obtener datos de países, departamentos y planes
require_once '../modelo/propietario_model.php';
$propietarioModel = new PropietarioModel();
$paises = $propietarioModel->getPaises();
$planes = $propietarioModel->getPlanes();
?>

<div id="propietarioModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Completar Información de Propietario</h2>
            <p>Por favor, complete sus datos para continuar</p>
        </div>
        
        <form id="propietarioForm" action="../control/propietario_controller.php" method="POST">
            <input type="hidden" name="action" value="save_propietario">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            
            <div class="form-group">
                <label for="primer_nombre">Primer Nombre *</label>
                <input type="text" id="primer_nombre" name="primer_nombre" class="form-control" required
                       maxlength="45"
                       pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                       title="El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                <div class="error-message" id="primer_nombre_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="segundo_nombre">Segundo Nombre</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" class="form-control"
                       maxlength="45"
                       pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                       title="El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                <div class="error-message" id="segundo_nombre_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="apellido">Primer Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-control" required
                       maxlength="45"
                       pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                       title="El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras">
                <div class="error-message" id="apellido_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="segundo_apellido">Segundo Apellido</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control"
                       maxlength="45"
                       pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                       title="El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras">
                <div class="error-message" id="segundo_apellido_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" class="form-control" required
                       maxlength="15"
                       pattern="^[0-9]{7,15}$"
                       title="El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números">
                <div class="error-message" id="telefono_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección *</label>
                <input type="text" id="direccion" name="direccion" class="form-control" required
                       maxlength="100"
                       pattern="^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$"
                       title="La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos">
                <div class="error-message" id="direccion_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="pais">País *</label>
                <select id="pais" name="pais" class="form-control" required>
                    <option value="">Seleccione un país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo $pais['pais_id']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="pais_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un país</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="departamento">Departamento *</label>
                <select id="departamento" name="departamento" class="form-control" required disabled>
                    <option value="">Seleccione un departamento</option>
                </select>
                <div class="error-message" id="departamento_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un departamento</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ciudad">Ciudad *</label>
                <select id="ciudad" name="ciudad" class="form-control" required disabled>
                    <option value="">Seleccione una ciudad</option>
                </select>
                <div class="error-message" id="ciudad_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar una ciudad</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="plan">Plan *</label>
                <select id="plan" name="plan" class="form-control" required>
                    <option value="">Seleccione un plan</option>
                    <?php foreach ($planes as $plan): ?>
                        <option value="<?php echo $plan['plan_id']; ?>"><?php echo htmlspecialchars($plan['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="plan_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un plan</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submit-btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 2% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    margin-bottom: 20px;
    text-align: center;
}

.modal-header h2 {
    color: #333;
    margin-bottom: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.form-actions {
    margin-top: 20px;
    text-align: center;
}

.btn-primary {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    min-width: 120px;
}

.btn-primary:hover {
    background-color: #45a049;
}

/* Estilos para los selectores */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 15px;
    padding-right: 30px;
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

.submit-btn:disabled {
    background-color: #95a5a6;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('propietarioModal');
    const form = document.getElementById('propietarioForm');
    const paisSelect = document.getElementById('pais');
    const departamentoSelect = document.getElementById('departamento');
    const ciudadSelect = document.getElementById('ciudad');
    const submitBtn = document.getElementById('submit-btn');
    const requiredInputs = form.querySelectorAll('input[required]');
    const requiredSelects = form.querySelectorAll('select[required]');
    
    // Mostrar modal si es el primer inicio de sesión
    if (<?php echo isset($_SESSION['is_first_login']) ? 'true' : 'false'; ?>) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
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
        
        // Validar inputs requeridos
        requiredInputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        // Validar selects requeridos
        requiredSelects.forEach(select => {
            if (!select.disabled && !validateField(select)) {
                isValid = false;
            }
        });
        
        // Deshabilitar/habilitar el botón de guardar
        submitBtn.disabled = !isValid;
        if (!isValid) {
            submitBtn.classList.add('disabled');
        } else {
            submitBtn.classList.remove('disabled');
        }
    }
    
    // Cargar departamentos cuando se selecciona un país
    paisSelect.addEventListener('change', function() {
        const paisId = this.value;
        validateField(this);
        
        if (paisId) {
            fetch(`../control/propietario_controller.php?action=get_departamentos&pais_id=${paisId}`)
                .then(response => response.json())
                .then(data => {
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
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los departamentos');
                });
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
            fetch(`../control/propietario_controller.php?action=get_ciudades&departamento_id=${deptoId}`)
                .then(response => response.json())
                .then(data => {
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
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar las ciudades');
                });
        } else {
            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
            ciudadSelect.disabled = true;
            validateForm();
        }
    });
    
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
    
    // Validar el formulario al cargar
    validateForm();
    
    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar el formulario una última vez antes de enviar
        validateForm();
        
        if (submitBtn.disabled) {
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('../control/propietario_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.error || 'Error al guardar los datos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });
});
</script> 