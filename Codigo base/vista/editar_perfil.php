<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../modelo/propietario_model.php';
require_once '../modelo/residencia_model.php';
require_once '../control/BaseController.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login-registro.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$propietarioModel = new PropietarioModel();
$residenciaModel = new ResidenciaModel();

// Obtener datos actuales
$propietario = $propietarioModel->getPropietarioByUserId($userId);
$residencia = $residenciaModel->getResidenciaById($propietario['residencia_id']);

// Obtener jerarquía de ubicación
$jerarquia = $propietarioModel->getJerarquiaUbicacion($residencia['ciudad_id']);
$pais_id = $jerarquia['pais_id'] ?? '';
$departamento_id = $jerarquia['departamento_id'] ?? '';
$ciudad_id = $jerarquia['ciudad_id'] ?? '';

// Obtener lista de países y departamentos
$paises = $propietarioModel->getPaises();
$departamentos = $pais_id ? $propietarioModel->getDepartamentos($pais_id) : [];
$ciudades = $departamento_id ? $propietarioModel->getCiudades($departamento_id) : [];

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $segundo_nombre = $_POST['segundo_nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $segundo_apellido = $_POST['segundo_apellido'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $pais_id = $_POST['pais_id'] ?? '';
    $departamento_id = $_POST['departamento_id'] ?? '';
    $ciudad_id = $_POST['ciudad_id'] ?? '';

    // Actualizar datos del propietario
    $propietarioData = [
        'primer_nombre' => $primer_nombre,
        'segundo_nombre' => $segundo_nombre,
        'apellido' => $apellido,
        'segundo_apellido' => $segundo_apellido,
        'telefono' => $telefono
    ];

    // Actualizar datos de residencia
    $residenciaData = [
        'direccion' => $direccion,
        'ciudad_id' => $ciudad_id
    ];

    try {
        $propietarioModel->updatePropietario($propietario['propietario_id'], $propietarioData);
        $residenciaModel->updateResidencia($residencia['residencia_id'], $residenciaData);
        
        $_SESSION['success'] = 'Perfil actualizado exitosamente';
        header('Location: main.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al actualizar el perfil: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Hachiko</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .edit-profile-container {
            max-width: 800px;
            min-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .form-group select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .form-actions {
            margin-top: 30px;
            text-align: right;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #f44336;
            color: white;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #da190b;
        }

        .section-title {
            color: #4CAF50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
        }

        /* Estilos para mensajes de error */
        .error-message {
            display: none;
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            padding: 5px;
            border-radius: 4px;
            background-color: rgba(231, 76, 60, 0.1);
            min-width: 300px;
            max-width: 100%;
            white-space: normal;
            word-wrap: break-word;
        }

        .error-message.show {
            display: flex;
            align-items: center;
        }

        .error-icon {
            margin-right: 5px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .error-text {
            flex: 1;
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
    </style>
</head>
<body>
    <div class="edit-profile-container">
        <h1 class="section-title">Editar Perfil</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../control/propietario_controller.php" id="editProfileForm">
            <input type="hidden" name="action" value="update_propietario">
            <input type="hidden" name="propietario_id" value="<?php echo htmlspecialchars($propietario['propietario_id'] ?? ''); ?>">
            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($propietario['plan_id'] ?? ''); ?>">
            <h2>Información Personal</h2>
            <div class="form-group">
                <label for="primer_nombre">Primer Nombre *</label>
                <input type="text" id="primer_nombre" name="primer_nombre" class="form-control" required
                       maxlength="45"
                       title="El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras"
                       value="<?php echo htmlspecialchars($propietario['primer_nombre'] ?? ''); ?>">
                <div class="error-message" id="primer_nombre_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El primer nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>

            <div class="form-group">
                <label for="segundo_nombre">Segundo Nombre</label>
                <input type="text" id="segundo_nombre" name="segundo_nombre" class="form-control"
                       maxlength="45"
                       title="El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras"
                       value="<?php echo htmlspecialchars($propietario['segundo_nombre'] ?? ''); ?>">
                <div class="error-message" id="segundo_nombre_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El segundo nombre debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>

            <div class="form-group">
                <label for="apellido">Primer Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-control" required
                       maxlength="45"
                       title="El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras"
                       value="<?php echo htmlspecialchars($propietario['apellido'] ?? ''); ?>">
                <div class="error-message" id="apellido_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>

            <div class="form-group">
                <label for="segundo_apellido">Segundo Apellido</label>
                <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control"
                       maxlength="45"
                       title="El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras"
                       value="<?php echo htmlspecialchars($propietario['segundo_apellido'] ?? ''); ?>">
                <div class="error-message" id="segundo_apellido_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El segundo apellido debe tener entre 2 y 45 caracteres y solo puede contener letras</span>
                </div>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" class="form-control" required
                       maxlength="15"
                       title="El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números"
                       value="<?php echo htmlspecialchars($propietario['telefono'] ?? ''); ?>">
                <div class="error-message" id="telefono_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">El teléfono debe tener entre 7 y 15 caracteres y solo puede contener números</span>
                </div>
            </div>

            <h2>Dirección</h2>
            <div class="form-group">
                <label for="direccion">Dirección *</label>
                <input type="text" id="direccion" name="direccion" class="form-control" required
                       maxlength="100"
                       title="La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos"
                       value="<?php echo htmlspecialchars($residencia['direccion'] ?? ''); ?>">
                <div class="error-message" id="direccion_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">La dirección debe tener entre 5 y 100 caracteres y puede contener letras, números y caracteres especiales básicos</span>
                </div>
            </div>

            <div class="form-group">
                <label for="pais_id">País *</label>
                <select id="pais_id" name="pais" class="form-control" required>
                    <option value="">Seleccione un país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo $pais['pais_id']; ?>" <?php echo ($pais_id == $pais['pais_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pais['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="pais_id_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un país</span>
                </div>
            </div>

            <div class="form-group">
                <label for="departamento_id">Departamento *</label>
                <select id="departamento_id" name="departamento" class="form-control" required>
                    <option value="">Seleccione un departamento</option>
                    <?php foreach ($departamentos as $departamento): ?>
                        <option value="<?php echo $departamento['departamento_id']; ?>" <?php echo ($departamento_id == $departamento['departamento_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($departamento['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="departamento_id_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar un departamento</span>
                </div>
            </div>

            <div class="form-group">
                <label for="ciudad_id">Ciudad *</label>
                <select id="ciudad_id" name="ciudad" class="form-control" required>
                    <option value="">Seleccione una ciudad</option>
                    <?php foreach ($ciudades as $ciudad): ?>
                        <option value="<?php echo $ciudad['ciudad_id']; ?>" <?php echo ($ciudad_id == $ciudad['ciudad_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ciudad['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="ciudad_id_error">
                    <span class="error-icon">✕</span>
                    <span class="error-text">Debe seleccionar una ciudad</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submit-btn">Guardar Cambios</button>
                <a href="main.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado');
        
        const form = document.getElementById('editProfileForm');
        const submitBtn = document.getElementById('submit-btn');
        
        if (!form) {
            console.error('No se encontró el formulario');
            return;
        }
        
        if (!submitBtn) {
            console.error('No se encontró el botón de submit');
            return;
        }
        
        console.log('Formulario y botón encontrados');
        
        const paisSelect = document.getElementById('pais_id');
        const departamentoSelect = document.getElementById('departamento_id');
        const ciudadSelect = document.getElementById('ciudad_id');
        const direccionInput = document.getElementById('direccion');
        
        // Agregar evento click al botón
        submitBtn.addEventListener('click', function(e) {
            console.log('Botón clickeado');
        });
        
        // Agregar evento submit al formulario
        form.addEventListener('submit', function(e) {
            console.log('Formulario submit iniciado');
            e.preventDefault();
            
            if (!validateForm()) {
                console.log('Validación fallida');
                return;
            }
            
            console.log('Validación exitosa');
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
            
            // Crear FormData y agregar los campos necesarios
            const formData = new FormData(this);
            
            // Asegurarse de que los campos de ubicación tengan los valores correctos
            formData.set('pais', paisSelect.value);
            formData.set('departamento', departamentoSelect.value);
            formData.set('ciudad', ciudadSelect.value);
            
            // Mostrar los datos que se están enviando
            console.log('Enviando datos:', Object.fromEntries(formData));
            
            // Obtener la URL correcta del formulario
            const formAction = this.getAttribute('action');
            console.log('URL de envío:', formAction);
            
            fetch(formAction, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Perfil actualizado exitosamente');
                        window.location.href = 'main.php';
                    }
                } else {
                    throw new Error(data.error || 'Error al actualizar el perfil');
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                handleAjaxError(error, 'Error al actualizar el perfil. Por favor, intente nuevamente.');
            });
        });
        
        // Función para validar todo el formulario
        function validateForm() {
            let isValid = true;
            console.log('Iniciando validación del formulario');
            
            // Validar todos los campos requeridos
            form.querySelectorAll('input[required], select[required]').forEach(field => {
                console.log(`Validando campo ${field.id}:`, {
                    valor: field.value,
                    tipo: field.type,
                    requerido: field.required,
                    deshabilitado: field.disabled
                });
                
                if (!validateField(field)) {
                    console.log(`Campo ${field.id} falló en la validación`);
                    isValid = false;
                } else {
                    console.log(`Campo ${field.id} pasó la validación`);
                }
            });
            
            // Deshabilitar/habilitar el botón de guardar
            submitBtn.disabled = !isValid;
            if (!isValid) {
                submitBtn.classList.add('disabled');
                console.log('Formulario inválido - botón deshabilitado');
            } else {
                submitBtn.classList.remove('disabled');
                console.log('Formulario válido - botón habilitado');
            }
            
            return isValid;
        }
        
        // Función para validar un campo
        function validateField(input) {
            const errorElement = document.getElementById(`${input.id}_error`);
            let isValid = true;
            
            console.log(`Validando campo ${input.id}:`, {
                valor: input.value,
                tipo: input.type,
                requerido: input.required,
                deshabilitado: input.disabled
            });
            
            if (input.tagName === 'SELECT') {
                isValid = input.value !== '' && !input.disabled;
                console.log(`Validación SELECT ${input.id}:`, { valor: input.value, deshabilitado: input.disabled, esValido: isValid });
            } else if (input.id === 'direccion') {
                // Validación especial para dirección que incluye caracteres acentuados
                const direccionPattern = /^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$/;
                isValid = direccionPattern.test(input.value);
                console.log(`Validación dirección:`, { valor: input.value, esValido: isValid });
            } else if (input.id === 'primer_nombre' || input.id === 'segundo_nombre' || 
                      input.id === 'apellido' || input.id === 'segundo_apellido') {
                // Validación para nombres y apellidos
                const namePattern = /^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,'-]{2,45}$/;
                isValid = namePattern.test(input.value);
                console.log(`Validación nombre/apellido ${input.id}:`, { valor: input.value, esValido: isValid });
            } else if (input.id === 'telefono') {
                // Validación para teléfono
                const phonePattern = /^[0-9]{7,15}$/;
                isValid = phonePattern.test(input.value);
                console.log(`Validación teléfono:`, { valor: input.value, esValido: isValid });
            } else {
                isValid = input.value.length > 0;
                console.log(`Validación básica ${input.id}:`, { valor: input.value, esValido: isValid });
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
        
        // Mejorar el manejo de errores en las llamadas AJAX
        function handleAjaxError(error, message) {
            console.error('Error:', error);
            alert(message);
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        }

        // Cargar departamentos cuando se selecciona un país
        paisSelect.addEventListener('change', function() {
            const paisId = this.value;
            validateField(this);
            
            if (paisId) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                
                fetch(`../control/propietario_controller.php?action=get_departamentos&pais_id=${paisId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
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
                            throw new Error(data.error || 'Error al cargar los departamentos');
                        }
                    })
                    .catch(error => handleAjaxError(error, 'Error al cargar los departamentos. Por favor, intente nuevamente.'));
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
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                
                fetch(`../control/propietario_controller.php?action=get_ciudades&departamento_id=${deptoId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                            data.data.forEach(ciudad => {
                                ciudadSelect.innerHTML += `<option value="${ciudad.ciudad_id}">${ciudad.nombre}</option>`;
                            });
                            ciudadSelect.disabled = false;
                            validateForm();
                        } else {
                            throw new Error(data.error || 'Error al cargar las ciudades');
                        }
                    })
                    .catch(error => handleAjaxError(error, 'Error al cargar las ciudades. Por favor, intente nuevamente.'));
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
    });
    </script>
</body>
</html> 