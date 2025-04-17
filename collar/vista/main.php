<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Si no ha iniciado sesión, redirigir al login
    header('Location: login-registro.php');
    exit;
}

// Obtener información del usuario
$userEmail = $_SESSION['user_email'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hachiko - Panel Principal</title>
    <!-- Importación de fuente Poppins de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <!-- Botón para cambiar tema -->
    <button class="theme-toggle" id="theme-toggle">
        <img src="../icons/moon.png" alt="Cambiar tema" class="theme-icon" width="30" height="30">
    </button>
    
    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../icons/dogmain.png" alt="Logo" width="38" height="38">
                <span>¡Hachiko!</span>
            </div>
            
            <div class="search-container">
                <div class="search-box">
                    <img src="../icons/search.png" alt="Buscar" class="search-icon" width="18" height="18">
                    <input type="text" placeholder="Buscar..." class="search-input">
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Mi Mascota <img src="../icons/arrow-down.png" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Nueva mascota</a>
                        <a href="#" class="dropdown-item">Estado emocional</a>
                        <a href="#" class="dropdown-item">Perfil de mascota</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Análisis <img src="../icons/arrow-down.png" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Estado emocional actual</a>
                        <a href="#" class="dropdown-item">Soluciones etológicas</a>
                        <a href="#" class="dropdown-item">Programar paseo</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link">Informes <img src="../icons/arrow-down.png" alt="Expandir" width="12" height="12"></a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Reporte emocional mensual</a>
                        <a href="#" class="dropdown-item">Reporte anual</a>
                        <a href="#" class="dropdown-item">Exportar Datos</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">¡Acerca de Hachiko!</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">Ayuda</a>
                </li>
            </ul>
            
            <div class="user-menu">
                <div class="user-profile dropdown">
                    <img src="../icons/user.png" alt="Usuario" class="user-avatar" width="30" height="30">
                    <span class="user-name"><?php echo htmlspecialchars($userEmail); ?></span>
                    <img src="../icons/arrow-down.png" alt="Expandir" width="12" height="12">
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Mi Perfil</a>
                        <a href="#" class="dropdown-item">Configuración</a>
                        <div class="dropdown-divider"></div>
                        <a href="../control/logout_controller.php" class="dropdown-item">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mensajes de sistema -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
        ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
        ?>
    </div>
    <?php endif; ?>
    
    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Carrusel -->
        <div class="carousel-container">
            <div class="carousel">
                <div class="carousel-item active">
                    <img src="../images/slide1.png" alt="Collar Hachiko" class="carousel-image">
                    <div class="carousel-caption">
                        <h2>El collar que entiende los sentimientos de tu mascota</h2>
                        <p>Hachiko es el primer collar inteligente que monitorea el bienestar emocional de tu perro</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../images/slide2.png" alt="Bienestar animal" class="carousel-image">
                    <div class="carousel-caption">
                        <h2>Mejora el bienestar</h2>
                        <p>Identifica patrones emocionales y recibe recomendaciones para aumentar su felicidad</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../images/slide3.avif" alt="Monitoreo" class="carousel-image">
                    <div class="carousel-caption">
                        <h2>Monitoreo emocional 24/7</h2>
                        <p>Registra los estados de ánimo de tu perro y te alerta sobre situaciones de estrés o ansiedad</p>
                    </div>
                </div>
            </div>
            <div class="carousel-controls">
                <button class="carousel-control prev">❮</button>
                <div class="carousel-indicators">
                    <button class="indicator active" data-slide="0"></button>
                    <button class="indicator" data-slide="1"></button>
                    <button class="indicator" data-slide="2"></button>
                </div>
                <button class="carousel-control next">❯</button>
            </div>
        </div>
        
        <!-- Sección principal del collar (REDISEÑADA) -->
        <section class="collar-section">
            <h2>¿Por qué tu mascota necesita Hachiko?</h2>
            <div class="features-container">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/heart-rate.png" alt="Monitoreo">
                    </div>
                    <h3>Monitoreo emocional 24/7</h3>
                    <p>Registra los estados de ánimo de tu perro y te alerta sobre situaciones de estrés o ansiedad.</p>
                    <a href="#" class="card-link">Saber más</a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/dog-happy.png" alt="Bienestar">
                    </div>
                    <h3>Mejora el bienestar</h3>
                    <p>Identifica patrones emocionales y recibe recomendaciones para aumentar su felicidad.</p>
                    <a href="#" class="card-link">Descubrir cómo</a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/activity.png" alt="Actividad">
                    </div>
                    <h3>Seguimiento de actividad</h3>
                    <p>Controla su ejercicio diario y descanso para un equilibrio perfecto.</p>
                    <a href="#" class="card-link">Ver actividad</a>
                </div>
            </div>
        </section>
        
        <!-- Tarjetas informativas -->
        <div class="card-container">
            <div class="card">
                <div class="card-icon">
                    <img src="../icons/emotion.png" alt="Análisis Emocional" width="48" height="48">
                </div>
                <div class="card-content">
                    <h3>Análisis Emocional</h3>
                    <p>Nuestra tecnología patentada analiza frecuencia cardíaca, movimiento y vocalizaciones para determinar el estado emocional de tu mascota.</p>
                    <a href="#" class="card-link">Ver Análisis</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-icon">
                    <img src="../icons/alert.png" alt="Alertas" width="48" height="48">
                </div>
                <div class="card-content">
                    <h3>Alertas Tempranas</h3>
                    <p>Identifica disparadores de estrés y recibe alertas cuando tu mascota experimenta cambios emocionales significativos.</p>
                    <a href="#" class="card-link">Configurar Alertas</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-icon">
                    <img src="../icons/report.png" alt="Reportes" width="48" height="48">
                </div>
                <div class="card-content">
                    <h3>Reportes Detallados</h3>
                    <p>Accede a informes completos sobre el bienestar emocional de tu mascota a lo largo del tiempo.</p>
                    <a href="#" class="card-link">Ver Informes</a>
                </div>
            </div>
        </div>
        
        <!-- Testimonios (REDISEÑADOS) -->
        <section class="testimonials-section">
            <h2>Dueños felices, perros más felices</h2>
            <div class="testimonials-container">
                <div class="testimonial-card">
                    <div class="testimonial-avatar-container">
                        <img src="../images/user1.jpg" alt="María G." class="testimonial-avatar">
                    </div>
                    <p>"Hachiko me ayudó a entender que mi perra sufría ansiedad por separación. Con las recomendaciones, ahora está mucho más tranquila cuando salgo."</p>
                    <span class="testimonial-author">- María G., dueña de Luna</span>
                    <a href="#" class="card-link">Leer historia completa</a>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-avatar-container">
                        <img src="../images/user2.jpg" alt="Carlos M." class="testimonial-avatar">
                    </div>
                    <p>"Gracias a las alertas del collar, descubrí que los ruidos fuertes afectaban a Rocky. Ahora tenemos un refugio seguro para él durante tormentas."</p>
                    <span class="testimonial-author">- Carlos M., dueño de Rocky</span>
                    <a href="#" class="card-link">Leer historia completa</a>
                </div>
            </div>
        </section>
        
    
    <!-- Pie de página -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../icons/dogmain.png" alt="Logo" width="30" height="30">
                <span>Hachiko - Bienestar emocional para tu mascota</span>
            </div>
            <div class="footer-copyright">
                &copy; 2025 Hachiko. Todos los derechos reservados.
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="../js/main.js"></script>
</body>
</html>