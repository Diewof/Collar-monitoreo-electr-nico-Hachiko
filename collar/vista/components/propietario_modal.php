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
                <input type="text" id="primer_nombre" name="primer_nombre" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="segundo_nombre">Segundo Nombre</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="apellido">Primer Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="segundo_apellido">Segundo Apellido</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección *</label>
                <input type="text" id="direccion" name="direccion" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="pais">País *</label>
                <select id="pais" name="pais" class="form-control" required>
                    <option value="">Seleccione un país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo $pais['pais_id']; ?>"><?php echo htmlspecialchars($pais['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="departamento">Departamento *</label>
                <select id="departamento" name="departamento" class="form-control" required disabled>
                    <option value="">Seleccione un departamento</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ciudad">Ciudad *</label>
                <select id="ciudad" name="ciudad" class="form-control" required disabled>
                    <option value="">Seleccione una ciudad</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="plan">Plan *</label>
                <select id="plan" name="plan" class="form-control" required>
                    <option value="">Seleccione un plan</option>
                    <?php foreach ($planes as $plan): ?>
                        <option value="<?php echo $plan['plan_id']; ?>"><?php echo htmlspecialchars($plan['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('propietarioModal');
    const form = document.getElementById('propietarioForm');
    const paisSelect = document.getElementById('pais');
    const departamentoSelect = document.getElementById('departamento');
    const ciudadSelect = document.getElementById('ciudad');
    
    // Mostrar modal si es el primer inicio de sesión
    if (<?php echo isset($_SESSION['is_first_login']) ? 'true' : 'false'; ?>) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Cargar departamentos cuando se selecciona un país
    paisSelect.addEventListener('change', function() {
        const paisId = this.value;
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
        }
    });
    
    // Cargar ciudades cuando se selecciona un departamento
    departamentoSelect.addEventListener('change', function() {
        const deptoId = this.value;
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
        }
    });
    
    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../control/propietario_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirigir a la página principal donde se mostrará la notificación verde
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