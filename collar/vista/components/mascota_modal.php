<?php
// Obtener datos de razas
require_once '../modelo/mascota_model.php';
$mascotaModel = new MascotaModel();
$razas = $mascotaModel->getRazas();
?>

<div id="mascotaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Registro de Mascota</h2>
            <p>Por favor, complete los datos de su mascota para continuar</p>
        </div>
        
        <form id="mascotaForm" action="../control/mascota_controller.php" method="POST">
            <input type="hidden" name="action" value="save_mascota">
            <input type="hidden" name="propietario_id" value="<?php echo $_SESSION['propietario_id']; ?>">
            
            <div class="form-group">
                <label for="nombre">Nombre de la Mascota *</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required
                       maxlength="45"
                       pattern="^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$"
                       title="El nombre debe tener entre 2 y 45 caracteres y solo puede contener letras">
                <div class="error-message" id="nombre_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required
                       max="<?php echo date('Y-m-d'); ?>">
                <div class="error-message" id="fecha_nacimiento_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar una fecha válida</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="peso">Peso (kg) *</label>
                <input type="number" id="peso" name="peso" class="form-control" required
                       min="0.1" max="100" step="0.1"
                       title="El peso debe estar entre 0.1 y 100 kg">
                <div class="error-message" id="peso_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El peso debe estar entre 0.1 y 100 kg</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="raza">Raza *</label>
                <select id="raza" name="raza_id" class="form-control" required>
                    <option value="">Seleccione una raza</option>
                </select>
                <div class="error-message" id="raza_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar una raza</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="genero">Género *</label>
                <select id="genero" name="genero" class="form-control" required>
                    <option value="">Seleccione el género</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
                <div class="error-message" id="genero_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar el género</span>
                </div>
            </div>
            
            <div class="form-group checkbox-group">
                <label for="esterilizado">
                    <input type="checkbox" id="esterilizado" name="esterilizado" value="1">
                    ¿Está esterilizado?
                </label>
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
    font-size: 1.5rem;
}

.modal-header p {
    color: #666;
    font-size: 0.95rem;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: #fff;
    color: #333;
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
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    min-width: 120px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #45a049;
}

.btn-primary:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
    opacity: 0.7;
}

.error-message {
    display: none;
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    padding: 8px;
    border-radius: 4px;
    background-color: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.2);
}

.error-message.show {
    display: flex;
    align-items: center;
}

.error-icon {
    margin-right: 8px;
    font-weight: bold;
}

.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 0;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 8px;
    width: 16px;
    height: 16px;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 5% auto;
        padding: 15px;
    }
    
    .form-control {
        padding: 8px;
    }
    
    .btn-primary {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('mascotaModal');
    const form = document.getElementById('mascotaForm');
    const submitBtn = document.getElementById('submit-btn');
    const razaSelect = document.getElementById('raza');
    
    // Función para cargar las razas
    async function loadRazas() {
        try {
            console.log('Intentando cargar razas...');
            const response = await fetch('/collar/control/raza_controller.php?action=get_razas');
            console.log('Respuesta recibida:', response);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Datos recibidos:', data);
            
            if (data.success) {
                razaSelect.innerHTML = '<option value="">Seleccione una raza</option>';
                data.data.forEach(raza => {
                    razaSelect.innerHTML += `<option value="${raza.raza_id}">${raza.nombre}</option>`;
                });
            } else {
                throw new Error(data.error || 'Error al cargar las razas');
            }
        } catch (error) {
            console.error('Error detallado:', error);
            alert('Error al cargar las razas. Por favor, intente nuevamente. Detalles: ' + error.message);
        }
    }
    
    // Cargar razas cuando se abre el modal
    modal.addEventListener('show', loadRazas);
    
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
        form.querySelectorAll('input[required], select[required]').forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        submitBtn.disabled = !isValid;
        submitBtn.classList.toggle('disabled', !isValid);
        return isValid;
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
    
    // Validar el formulario al cargar
    validateForm();
    
    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar el formulario una última vez antes de enviar
        if (!validateForm()) {
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('../control/mascota_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = '/collar/vista/main.php';
        });
    });
});
</script> 