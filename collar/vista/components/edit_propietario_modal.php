<?php
// Obtener datos de países, departamentos y planes
require_once '../modelo/propietario_model.php';
$propietarioModel = new PropietarioModel();
$paises = $propietarioModel->getPaises();
$planes = $propietarioModel->getPlanes();
?>

<div id="editPropietarioModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Información de Propietario</h2>
            <span class="close">&times;</span>
        </div>
        
        <form id="editPropietarioForm" action="../control/propietario_controller.php" method="POST">
            <input type="hidden" name="action" value="update_propietario">
            <input type="hidden" name="propietario_id" id="edit_propietario_id">
            
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
            </div>
            
            <div class="form-group">
                <label for="edit_telefono">Teléfono *</label>
                <input type="tel" id="edit_telefono" name="telefono" class="form-control" required
                       maxlength="15"
                       pattern="^[0-9]{7,15}$"
                       title="El teléfono debe tener entre 7 y 15 dígitos">
                <div class="error-message" id="edit_telefono_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El teléfono debe tener entre 7 y 15 dígitos</span>
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
                <select id="edit_departamento" name="departamento" class="form-control" required>
                    <option value="">Seleccione un departamento</option>
                </select>
                <div class="error-message" id="edit_departamento_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un departamento</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_ciudad">Ciudad *</label>
                <select id="edit_ciudad" name="ciudad" class="form-control" required>
                    <option value="">Seleccione una ciudad</option>
                </select>
                <div class="error-message" id="edit_ciudad_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar una ciudad</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="edit_plan">Plan *</label>
                <select id="edit_plan" name="plan" class="form-control" required>
                    <option value="">Seleccione un plan</option>
                    <?php foreach ($planes as $plan): ?>
                        <option value="<?php echo $plan['plan_id']; ?>"><?php echo htmlspecialchars($plan['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="edit_plan_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un plan</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="edit_cancel_btn">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="edit_submit_btn">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editPropietarioModal');
    const form = document.getElementById('editPropietarioForm');
    const closeBtn = document.querySelector('#editPropietarioModal .close');
    const cancelBtn = document.getElementById('edit_cancel_btn');
    const paisSelect = document.getElementById('edit_pais');
    const departamentoSelect = document.getElementById('edit_departamento');
    const ciudadSelect = document.getElementById('edit_ciudad');
    
    // Función para cargar departamentos
    function loadDepartamentos(paisId) {
        fetch(`../control/propietario_controller.php?action=get_departamentos&pais_id=${paisId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
                    data.data.forEach(depto => {
                        departamentoSelect.innerHTML += `<option value="${depto.departamento_id}">${depto.nombre}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Función para cargar ciudades
    function loadCiudades(departamentoId) {
        fetch(`../control/propietario_controller.php?action=get_ciudades&departamento_id=${departamentoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                    data.data.forEach(ciudad => {
                        ciudadSelect.innerHTML += `<option value="${ciudad.ciudad_id}">${ciudad.nombre}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Event listeners para los selects
    paisSelect.addEventListener('change', function() {
        if (this.value) {
            loadDepartamentos(this.value);
        } else {
            departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
        }
    });
    
    departamentoSelect.addEventListener('change', function() {
        if (this.value) {
            loadCiudades(this.value);
        } else {
            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
        }
    });
    
    // Cerrar modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('../control/propietario_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeModal();
                location.reload(); // Recargar la página para ver los cambios
            } else {
                alert(data.error || 'Error al actualizar los datos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });
});
</script> 