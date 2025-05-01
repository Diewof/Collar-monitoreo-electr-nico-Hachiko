/**
 * Script para manejar la interfaz de usuario del panel principal
 */

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const themeToggle = document.getElementById('theme-toggle');
    const carouselItems = document.querySelectorAll('.carousel-item');
    const prevButton = document.querySelector('.carousel-control.prev');
    const nextButton = document.querySelector('.carousel-control.next');
    const indicators = document.querySelectorAll('.indicator');
    
    // Variables del carrusel
    let currentSlide = 0;
    const totalSlides = carouselItems.length;
    let carouselInterval;
    
// Variables para el temporizador de inactividad
let inactivityTimeout;
const inactivityTime = 15 * 60 * 1000; // Tiempo real para cerrar sesión
let inactivitySeconds = 15 * 60; // Contador visual para el logout
let timerInterval;
const timerDisplay = document.getElementById('timer-countdown');

// Función para reiniciar ambos temporizadores
function resetTimers() {
    resetInactivityTimer();
    resetVisualTimer();
}

// Función para reiniciar el temporizador de inactividad
function resetInactivityTimer() {
    // Limpiar el temporizador anterior si existe
    clearTimeout(inactivityTimeout);

    // Establecer un nuevo temporizador de inactividad
    inactivityTimeout = setTimeout(() => {
        window.location.href = '../control/auto_logout.php';
    }, inactivityTime);
}

// Función para actualizar la visualización del temporizador
function updateTimerDisplay() {
    const minutes = Math.floor(inactivitySeconds / 60);
    const seconds = inactivitySeconds % 60;

    // Formatear el tiempo como MM:SS
    timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    // Disminuir el contador
    inactivitySeconds--;

    // Redirigir al cerrar sesión si el tiempo se agota
    if (inactivitySeconds < 0) {
        clearInterval(timerInterval);
        window.location.href = '../control/auto_logout.php';
    }
}

// Función para reiniciar el temporizador visual
function resetVisualTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    inactivitySeconds = 15 * 60; // Resetear a 15 minutos
    updateTimerDisplay();
    timerInterval = setInterval(updateTimerDisplay, 1000);
}

// Eventos que reinician ambos temporizadores
['mousemove', 'keypress', 'click', 'scroll'].forEach(event => {
    document.addEventListener(event, resetTimers);
});

// Iniciar ambos temporizadores al cargar la página
resetTimers();

    // Configurar toggle de tema
    if (themeToggle) {
        const themeIcon = themeToggle.querySelector('.theme-icon');
        
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            themeIcon.src = "../icons/sun.avif";
        }
        
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('light-theme');
            
            if (document.body.classList.contains('light-theme')) {
                localStorage.setItem('theme', 'light');
                themeIcon.src = "../icons/sun.avif";
            } else {
                localStorage.setItem('theme', 'dark');
                themeIcon.src = "../icons/moon.avif";
            }
        });
    }
    
    // Función para mostrar una diapositiva específica
    function showSlide(index) {
        // Validar el índice
        if (index < 0) {
            index = totalSlides - 1;
        } else if (index >= totalSlides) {
            index = 0;
        }
        
        // Actualizar el índice actual
        currentSlide = index;
        
        // Ocultar todas las diapositivas y quitar las clases activas de los indicadores
        carouselItems.forEach(item => item.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Mostrar la diapositiva actual y activar el indicador correspondiente
        carouselItems[currentSlide].classList.add('active');
        indicators[currentSlide].classList.add('active');
    }
    
    // Función para avanzar a la siguiente diapositiva
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    // Función para volver a la diapositiva anterior
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    // Configurar el intervalo automático para el carrusel
    function startCarouselInterval() {
        carouselInterval = setInterval(nextSlide, 5000); // Cambiar cada 5 segundos
    }
    
    // Detener el intervalo automático
    function stopCarouselInterval() {
        clearInterval(carouselInterval);
    }
    
    // Configurar eventos de botones de control
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            prevSlide();
            // Reiniciar el intervalo para evitar cambios rápidos
            stopCarouselInterval();
            startCarouselInterval();
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            nextSlide();
            // Reiniciar el intervalo
            stopCarouselInterval();
            startCarouselInterval();
        });
    }
    
    // Configurar eventos de indicadores
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showSlide(index);
            // Reiniciar el intervalo
            stopCarouselInterval();
            startCarouselInterval();
        });
    });
    
    // Pausar el carrusel al pasar el ratón sobre él
    const carouselContainer = document.querySelector('.carousel-container');
    if (carouselContainer) {
        carouselContainer.addEventListener('mouseenter', stopCarouselInterval);
        carouselContainer.addEventListener('mouseleave', startCarouselInterval);
    }
    
    // Iniciar el carrusel
    startCarouselInterval();
    
    // Manejar eventos de navegación desplegable para dispositivos táctiles
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('.nav-link');
        
        // Solo necesitamos manejar eventos táctiles especiales, no de ratón
        if ('ontouchstart' in window) {
            link.addEventListener('touchstart', (e) => {
                // Evitar que todos los demás dropdowns estén abiertos
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('touch-open');
                    }
                });
                
                // Alternar clase touch-open para el dropdown actual
                dropdown.classList.toggle('touch-open');
                
                // Si el dropdown ahora está abierto, evitar la navegación
                if (dropdown.classList.contains('touch-open')) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('touch-open');
            });
        }
    });
});