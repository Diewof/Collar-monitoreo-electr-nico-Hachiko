/**
 * Script para manejar la interfaz de usuario del lado del cliente
 */

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const passwordToggles = document.querySelectorAll('.password-toggle');
    const themeToggle = document.getElementById('theme-toggle');
    const messageElements = document.querySelectorAll('.message');
    
    // Configurar toggles de contraseña
    passwordToggles.forEach(toggle => {
        const eyeIcon = toggle.querySelector('img');
        
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.src = "../icons/eye.avif";
                eyeIcon.alt = "Ocultar contraseña";
                eyeIcon.dataset.state = "open";
            } else {
                input.type = 'password';
                eyeIcon.src = "../icons/close-eye.avif";
                eyeIcon.alt = "Mostrar contraseña";
                eyeIcon.dataset.state = "closed";
            }
        });
    });

    // Configurar toggle de tema
    if (themeToggle) {
        themeToggle.innerHTML = '<img src="../icons/moon.avif" alt="Cambiar tema" class="theme-icon" width="30" height="30">';
        
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            themeToggle.querySelector('.theme-icon').src = "../icons/sun.avif";
        }
        
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('light-theme');
            const themeIcon = themeToggle.querySelector('.theme-icon');
            
            if (document.body.classList.contains('light-theme')) {
                localStorage.setItem('theme', 'light');
                themeIcon.src = "../icons/sun.avif";
            } else {
                localStorage.setItem('theme', 'dark');
                themeIcon.src = "../icons/moon.avif";
            }
        });
    }
    
    // Ocultar mensajes después de 5 segundos
    if (messageElements.length > 0) {
        setTimeout(() => {
            messageElements.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }

    // Función de validación de email
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Función de validación de contraseña
    function validatePassword(password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return passwordRegex.test(password);
    }

    // Configurar validación de email
    function setupEmailValidation(input, form) {
        const validationMessage = document.createElement('div');
        validationMessage.className = 'validation-message';
        input.parentNode.appendChild(validationMessage);

        input.addEventListener('input', function() {
            updateEmailValidation(this.value.trim(), input, validationMessage);
            updateSubmitButtonState(form);
        });

        input.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email !== '') {
                updateEmailValidation(email, input, validationMessage);
                updateSubmitButtonState(form);
            }
        });
    }

    function updateEmailValidation(email, input, messageElement) {
        if (email === '') {
            messageElement.textContent = '';
            messageElement.className = 'validation-message';
            input.classList.remove('valid', 'invalid');
            return;
        }

        if (validateEmail(email)) {
            messageElement.textContent = '✓ Correo válido';
            messageElement.className = 'validation-message valid';
            input.classList.add('valid');
            input.classList.remove('invalid');
        } else {
            messageElement.textContent = '✗ Formato de correo inválido';
            messageElement.className = 'validation-message invalid';
            input.classList.add('invalid');
            input.classList.remove('valid');
        }
    }

    // Configurar validación de contraseña principal
    function setupPasswordValidation(input, form) {
        const validationMessage = document.createElement('div');
        validationMessage.className = 'validation-message';
        input.parentNode.appendChild(validationMessage);

        input.addEventListener('input', function() {
            updatePasswordValidation(this.value, input, validationMessage);
            updateSubmitButtonState(form);
        });

        input.addEventListener('blur', function() {
            const password = this.value;
            if (password !== '') {
                updatePasswordValidation(password, input, validationMessage);
                updateSubmitButtonState(form);
            }
        });
    }

    function updatePasswordValidation(password, input, messageElement) {
        if (password === '') {
            messageElement.textContent = '';
            messageElement.className = 'validation-message';
            input.classList.remove('valid', 'invalid');
            return;
        }

        if (validatePassword(password)) {
            messageElement.textContent = '✓ Contraseña válida';
            messageElement.className = 'validation-message valid';
            input.classList.add('valid');
            input.classList.remove('invalid');
        } else {
            messageElement.textContent = '✗ Mínimo 8 caracteres, 1 mayúscula, 1 minúscula y 1 número';
            messageElement.className = 'validation-message invalid';
            input.classList.add('invalid');
            input.classList.remove('valid');
        }
    }

    // Configurar validación de confirmación de contraseña
    function setupConfirmPasswordValidation(passwordInput, confirmInput, form) {
        const validationMessage = document.createElement('div');
        validationMessage.className = 'validation-message';
        confirmInput.parentNode.appendChild(validationMessage);

        function validatePasswordMatch(password, confirmPassword) {
            return password === confirmPassword;
        }

        function updateConfirmValidation() {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            if (confirmPassword === '') {
                validationMessage.textContent = '';
                validationMessage.className = 'validation-message';
                confirmInput.classList.remove('valid', 'invalid');
                return;
            }

            if (validatePasswordMatch(password, confirmPassword)) {
                validationMessage.textContent = '✓ Las contraseñas coinciden';
                validationMessage.className = 'validation-message valid';
                confirmInput.classList.add('valid');
                confirmInput.classList.remove('invalid');
            } else {
                validationMessage.textContent = '✗ Las contraseñas no coinciden';
                validationMessage.className = 'validation-message invalid';
                confirmInput.classList.add('invalid');
                confirmInput.classList.remove('valid');
            }
            
            updateSubmitButtonState(form);
        }

        confirmInput.addEventListener('input', updateConfirmValidation);
        confirmInput.addEventListener('blur', updateConfirmValidation);
        passwordInput.addEventListener('input', updateConfirmValidation);
    }

    // Función para verificar si todo el formulario es válido
    function isFormValid(form) {
        let isValid = true;

        // Validar email si existe
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput) {
            const email = emailInput.value.trim();
            if (email === '' || !validateEmail(email)) {
                isValid = false;
            }
        }

        // Validar contraseña principal si existe
        const passwordInput = form.querySelector('input[name="password"]');
        if (passwordInput) {
            const password = passwordInput.value;
            if (password === '' || !validatePassword(password)) {
                isValid = false;
            }
        }

        // Validar confirmación si existe
        const confirmInput = form.querySelector('input[name="confirm_password"]');
        if (confirmInput && passwordInput) {
            const confirmPassword = confirmInput.value;
            if (confirmPassword === '' || passwordInput.value !== confirmPassword) {
                isValid = false;
            }
        }

        return isValid;
    }

    // Función para actualizar el estado del botón de submit
    function updateSubmitButtonState(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;

        if (isFormValid(form)) {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
        }
    }

    // Configurar validación para todos los formularios
    document.querySelectorAll('form').forEach(form => {
        // Configurar validaciones
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput) setupEmailValidation(emailInput, form);

        const passwordInput = form.querySelector('input[name="password"]');
        if (passwordInput) setupPasswordValidation(passwordInput, form);

        const confirmInput = form.querySelector('input[name="confirm_password"]');
        if (confirmInput && passwordInput) setupConfirmPasswordValidation(passwordInput, confirmInput, form);

        // Deshabilitar botón inicialmente
        updateSubmitButtonState(form);

        // Validación adicional antes de enviar
        form.addEventListener('submit', function(e) {
            if (!isFormValid(form)) {
                e.preventDefault();
            }
        });
    });
});