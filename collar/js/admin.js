/**
 * Script para gestionar funcionalidades específicas del panel de administración
 */

document.addEventListener('DOMContentLoaded', () => {
    // Gestionar eliminación de usuarios
    setupDeleteUserButtons();
    
    // Gestionar cambios de rol
    setupRoleChangeButtons();
    
    // Gestionar filtrado de la tabla de usuarios
    setupUserTableSearch();
    
    // Gestionar paginación (si se implementa)
    setupPagination();
    
    // Mostrar/ocultar elementos de la interfaz según la navegación
    setupNavigation();
    
    // Nueva función para editar usuarios
    setupEditUserButtons();
    
    // Configurar validación del formulario de edición
    const editForm = document.getElementById('edit-user-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormErrors();
            if (validateEditForm()) {
                // Mostrar mensaje de confirmación durante 1.5 segundos antes de enviar
                const confirmationMessage = document.getElementById('edit-confirmation');
                confirmationMessage.style.display = 'block';
                
                setTimeout(() => {
                    this.submit();
                }, 1500);
            }
        });
    }
    
    // Configurar medidor de fortaleza de contraseña
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const strengthBar = this.parentElement.querySelector('.strength-bar');
            if (strengthBar) {
                updatePasswordStrength(this.value, strengthBar);
            }
        });
    });
});

/**
 * Configura los botones para eliminar usuarios
 */
function setupDeleteUserButtons() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userId = this.getAttribute('data-user-id');
            const userEmail = this.getAttribute('data-user-email');
            
            if (confirm(`¿Está seguro de que desea eliminar al usuario ${userEmail}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../control/admin_controller.php';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_user';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                form.appendChild(actionInput);
                form.appendChild(userIdInput);
                document.body.appendChild(form);
                
                form.submit();
            }
        });
    });
}

/**
 * Configura los botones para cambiar el rol de los usuarios
 */
function setupRoleChangeButtons() {
    const roleSelects = document.querySelectorAll('.role-select');
    
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const newRole = this.value;
            
            if (confirm(`¿Está seguro de que desea cambiar el rol del usuario a ${newRole}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../control/admin_controller.php';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_role';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                const roleInput = document.createElement('input');
                roleInput.type = 'hidden';
                roleInput.name = 'role';
                roleInput.value = newRole;
                
                form.appendChild(actionInput);
                form.appendChild(userIdInput);
                form.appendChild(roleInput);
                document.body.appendChild(form);
                
                form.submit();
            } else {
                // Revertir la selección si el usuario cancela
                this.value = this.getAttribute('data-original-role');
            }
        });
    });
}

/**
 * Configura el buscador para la tabla de usuarios
 */
function setupUserTableSearch() {
    const searchInput = document.querySelector('.search-input');
    const tableRows = document.querySelectorAll('.admin-table tbody tr');
    
    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            tableRows.forEach(row => {
                const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const role = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                // Mostrar u ocultar filas según el término de búsqueda
                if (email.includes(searchTerm) || role.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

/**
 * Configura la paginación para la tabla de usuarios
 */
function setupPagination() {
    // Esta función podría implementarse en una versión futura
    // para manejar la paginación de la tabla de usuarios
}

/**
 * Configura la navegación entre diferentes secciones del panel
 */
function setupNavigation() {
    const navLinks = document.querySelectorAll('.dropdown-item');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const section = this.getAttribute('data-section');
            
            // Si el enlace no tiene una sección asociada, permitir la navegación normal
            if (!section) return;
            
            e.preventDefault();
            
            // Aquí podrías implementar navegación por AJAX o mostrar/ocultar secciones
            // Por ahora, simplemente redireccionamos con un parámetro
            window.location.href = 'admin_main.php?section=' + section;
        });
    });
}

/**
 * Configura los botones para editar usuarios
 */
function setupEditUserButtons() {
    const editButtons = document.querySelectorAll('.btn-edit');
    const modal = document.getElementById('edit-user-modal');
    
    // Asegurarse de que el modal esté oculto inicialmente
    if (modal) {
        modal.style.display = 'none';
    }
    
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamiento predeterminado
            
            const userId = this.getAttribute('data-user-id');
            const userRow = this.closest('tr');
            const userEmail = userRow.querySelector('td:nth-child(2)').textContent.trim();
            
            // Obtenemos el valor del rol actual directamente del select
            const roleSelect = userRow.querySelector('.role-select');
            const userRole = roleSelect ? roleSelect.value : 'usuario';
            
            // Actualizar el modal con los datos del usuario
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-email').value = userEmail;
            
            // Seleccionar el rol correcto en el dropdown
            const editRoleSelect = document.getElementById('edit-role');
            for (let i = 0; i < editRoleSelect.options.length; i++) {
                if (editRoleSelect.options[i].value === userRole) {
                    editRoleSelect.selectedIndex = i;
                    break;
                }
            }
            
            // Limpiar campos de contraseña
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-confirm-password').value = '';
            
            // Ocultar mensaje de confirmación si estuviera visible
            document.getElementById('edit-confirmation').style.display = 'none';
            
            // Mostrar el modal
            modal.style.display = 'block';
        });
    });
    
    // Configurar los botones de cerrar modal
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamiento predeterminado
            modal.style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera de él
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

/**
 * Validar formulario de edición
 */
function validateEditForm() {
    const emailInput = document.getElementById('edit-email');
    const passwordInput = document.getElementById('edit-password');
    const confirmPasswordInput = document.getElementById('edit-confirm-password');
    
    // Validar que el email tenga formato correcto
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailInput.value)) {
        showFormError(emailInput, 'Ingrese un correo electrónico válido');
        return false;
    }
    
    // Si se está cambiando la contraseña, validar que coincidan
    if (passwordInput.value !== '') {
        if (passwordInput.value.length < 8) {
            showFormError(passwordInput, 'La contraseña debe tener al menos 8 caracteres');
            return false;
        }
        
        if (passwordInput.value !== confirmPasswordInput.value) {
            showFormError(confirmPasswordInput, 'Las contraseñas no coinciden');
            return false;
        }
    }
    
    return true;
}

/**
 * Mostrar error en un campo del formulario
 */
function showFormError(inputElement, message) {
    // Eliminar mensajes de error previos
    const parentElement = inputElement.parentElement;
    const existingError = parentElement.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Crear y mostrar nuevo mensaje de error
    const errorElement = document.createElement('div');
    errorElement.className = 'form-error';
    errorElement.textContent = message;
    parentElement.appendChild(errorElement);
    
    // Enfocar el campo con error
    inputElement.focus();
}

/**
 * Limpia los mensajes de error del formulario
 */
function clearFormErrors() {
    const errorMessages = document.querySelectorAll('.form-error');
    errorMessages.forEach(error => error.remove());
}

/**
 * Actualiza el indicador de fortaleza de contraseña
 */
function updatePasswordStrength(password, strengthBar) {
    // Eliminar clases anteriores
    strengthBar.classList.remove('weak', 'medium', 'strong');
    
    if (password.length === 0) {
        strengthBar.style.width = '0';
        return;
    }
    
    // Criterios de fortaleza simple
    let strength = 0;
    
    // Longitud
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // Complejidad
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Actualizar barra según fortaleza
    if (strength <= 2) {
        strengthBar.classList.add('weak');
        strengthBar.style.width = '33%';
    } else if (strength <= 4) {
        strengthBar.classList.add('medium');
        strengthBar.style.width = '66%';
    } else {
        strengthBar.classList.add('strong');
        strengthBar.style.width = '100%';
    }
}