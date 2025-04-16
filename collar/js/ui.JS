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
    const eyeIcon = toggle.querySelector('img'); // Selecciona la imagen dentro del toggle
    
    toggle.addEventListener('click', function() {
        const input = this.previousElementSibling;
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.src = "../icons/eye.png"; // Ruta al icono de ojo abierto
            eyeIcon.alt = "Ocultar contraseña";
            eyeIcon.dataset.state = "open";
        } else {
            input.type = 'password';
            eyeIcon.src = "../icons/close-eye.png"; // Ruta al icono de ojo cerrado
            eyeIcon.alt = "Mostrar contraseña";
            eyeIcon.dataset.state = "closed";
        }
    });
});
    // Configurar toggle de tema
    if (themeToggle) {
        // Recuperar preferencia de tema guardada en localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            themeToggle.textContent = '🌙'; // ICONO: Puedes cambiar este icono
        }
        
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('light-theme');
            
            if (document.body.classList.contains('light-theme')) {
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙'; // ICONO: Puedes cambiar este icono
            } else {
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️'; // ICONO: Puedes cambiar este icono
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
                }, 500); // Tiempo de la animación de desvanecimiento
            });
        }, 5000);
    }
});