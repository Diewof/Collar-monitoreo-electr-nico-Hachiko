<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hachiko - Monitoreo Emocional para tu Mejor Amigo</title>
    <!-- Importación de fuente Poppins de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/landing.css">
</head>
<body>
    <!-- Botón para cambiar tema -->
    <button class="theme-toggle" id="theme-toggle">
        <img src="../icons/moon.avif" alt="Cambiar tema" class="theme-icon" width="30" height="30">
    </button>
    
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="../icons/dogmain.avif" alt="Logo" width="38" height="38">
                    <span>Hachiko<span class="accent">.</span></span>
                </div>
                <div class="nav-links">
                    <a href="#features">Características</a>
                    <a href="#how-it-works">Cómo Funciona</a>
                    <a href="#testimonials">Testimonios</a>
                    <a href="#pricing">Precios</a>
                </div>
                <div class="auth-buttons">
                    <a href="login-registro.php" class="btn btn-login">Iniciar Sesión</a>
                    <a href="login-registro.php?form=register" class="btn btn-signup">Registrarse</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Comprende las <span>emociones</span> de tu mejor amigo</h1>
                    <p>Hachiko es el primer collar inteligente que monitorea y analiza el estado emocional de tu perro en tiempo real, ayudándote a entender sus necesidades y fortalecer su vínculo.</p>
                    <a href="login-registro.php?form=register" class="btn btn-signup">Comenzar ahora</a>
                </div>
                <div class="hero-image">
                    <img src="../images/hero-dog.avif" alt="Perro con collar Hachiko" class="hero-img">
                    <!-- Si la imagen no existe, usar un comentario para indicarlo -->
                    <!-- NOTA: Crear imagen hero-dog.avif de un perro feliz con collar inteligente -->
                    <div class="floating-badge badge-1 float-animation">
                        <div class="badge-icon">
                            <img src="../icons/heart-rate.avif" alt="Monitoreo" width="24" height="24">
                        </div>
                        <div class="badge-text">
                            <h3>Monitoreo constante</h3>
                            <p>24/7 en tiempo real</p>
                        </div>
                    </div>
                    <div class="floating-badge badge-2 float-animation">
                        <div class="badge-icon">
                            <img src="../icons/activity.avif" alt="Alertas" width="24" height="24">
                        </div>
                        <div class="badge-text">
                            <h3>Alertas inteligentes</h3>
                            <p>Directamente a tu móvil</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>¿Por qué elegir Hachiko?</h2>
                <p>Descubre cómo nuestro collar de monitoreo emocional revoluciona la forma en que entiendes a tu mascota.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/emotion.avif" alt="Detección de Emociones" width="48" height="48">
                    </div>
                    <h3>Detección de Emociones</h3>
                    <p>Sensores avanzados que captan los patrones fisiológicos asociados con diferentes estados emocionales como alegría, estrés, ansiedad o tranquilidad.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/report.avif" alt="Análisis de Comportamiento" width="48" height="48">
                    </div>
                    <h3>Análisis de Comportamiento</h3>
                    <p>Algoritmos de IA que analizan patrones de comportamiento a lo largo del tiempo para identificar cambios que podrían indicar problemas de salud.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/alert.avif" alt="Alertas Personalizadas" width="48" height="48">
                    </div>
                    <h3>Alertas Personalizadas</h3>
                    <p>Recibe notificaciones cuando tu perro experimente niveles inusuales de estrés o ansiedad, permitiendo una intervención temprana.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/intuitive.png" alt="App Intuitiva" width="48" height="48">
                        <!-- NOTA: Crear icono app.avif si no existe -->
                    </div>
                    <h3>App Intuitiva</h3>
                    <p>Interfaz fácil de usar que muestra análisis detallados, tendencias y recomendaciones personalizadas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/battery.png" alt="Batería de Larga Duración" width="48" height="48">
                        <!-- NOTA: Crear icono battery.avif si no existe -->
                    </div>
                    <h3>Batería de Larga Duración</h3>
                    <p>Hasta 7 días de autonomía con una sola carga y sistema de carga rápida que proporciona un día completo en solo 20 minutos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="../icons/community.png" alt="Comunidad Hachiko" width="48" height="48">
                        <!-- NOTA: Crear icono community.avif si no existe -->
                    </div>
                    <h3>Comunidad Hachiko</h3>
                    <p>Conecta con otros dueños de mascotas, comparte experiencias y recibe consejos de expertos en comportamiento animal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>¿Cómo funciona Hachiko?</h2>
                <p>Tres simples pasos para comenzar a entender mejor a tu compañero canino.</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Coloca el collar</h3>
                    <p>Ajusta cómodamente el collar inteligente Hachiko al cuello de tu perro.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Conecta la app</h3>
                    <p>Descarga la aplicación y sincroniza el collar a través de Bluetooth.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Obtén insights</h3>
                    <p>Recibe análisis y recomendaciones basadas en el estado emocional de tu mascota.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Mejora su bienestar</h3>
                    <p>Utiliza los datos para optimizar la rutina y el entorno de tu perro.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Lo que nuestros usuarios dicen</h2>
                <p>Descubre cómo Hachiko ha transformado la relación entre mascotas y sus dueños.</p>
            </div>
            <div class="testimonials-container">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Gracias a Hachiko descubrí que mi perro experimentaba ansiedad cuando me iba a trabajar. Ahora realizo una rutina calmante antes de salir y ha mejorado notablemente su comportamiento."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="../images/user1.avif" alt="María González" width="50" height="50">
                        </div>
                        <div class="author-info">
                            <h4>María González</h4>
                            <p>Dueña de Toby, Border Collie</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Como veterinario, recomiendo Hachiko a mis clientes. Los datos que proporciona son invaluables para entender patrones de comportamiento y detectar posibles problemas de salud tempranamente."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="../images/user2.avif" alt="Dr. Ramírez" width="50" height="50">
                        </div>
                        <div class="author-info">
                            <h4>Dr. Ramírez</h4>
                            <p>Veterinario especialista en comportamiento</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Mi perro rescatado tenía problemas de adaptación y comportamiento. Hachiko me ha ayudado a identificar sus desencadenantes de estrés y trabajar en ellos de manera efectiva."
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="../images/user3.jfif" alt="Laura Pérez" width="50" height="50">
                            <!-- NOTA: Crear imagen user3.avif si no existe -->
                        </div>
                        <div class="author-info">
                            <h4>Laura Pérez</h4>
                            <p>Dueña de Simba, mestizo rescatado</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Planes que se adaptan a ti</h2>
                <p>Elige el plan perfecto para ti y tu mejor amigo.</p>
            </div>
            <div class="pricing-options">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Plan Básico</h3>
                        <div class="price">€9.99<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li>Collar Hachiko estándar</li>
                        <li>Monitoreo emocional básico</li>
                        <li>Alertas de estrés y ansiedad</li>
                        <li>Acceso a la app móvil</li>
                        <li>Reportes semanales</li>
                    </ul>
                    <a href="login-registro.php?form=register" class="btn btn-login">Elegir plan</a>
                </div>
                <div class="pricing-card popular">
                    <div class="popular-tag">Más popular</div>
                    <div class="pricing-header">
                        <h3>Plan Premium</h3>
                        <div class="price">€19.99<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li>Collar Hachiko+ con GPS integrado</li>
                        <li>Monitoreo emocional avanzado</li>
                        <li>Análisis de comportamiento detallado</li>
                        <li>Alertas personalizadas</li>
                        <li>Reportes diarios</li>
                        <li>Acceso a veterinarios 24/7</li>
                    </ul>
                    <a href="login-registro.php?form=register" class="btn btn-signup">Elegir plan</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Plan Familiar</h3>
                        <div class="price">€29.99<span>/mes</span></div>
                    </div>
                    <ul class="pricing-features">
                        <li>Hasta 3 collares Hachiko+ con GPS</li>
                        <li>Todas las características Premium</li>
                        <li>Comparativa entre mascotas</li>
                        <li>Asesoramiento personalizado</li>
                        <li>Descuentos en accesorios</li>
                        <li>Garantía extendida</li>
                    </ul>
                    <a href="login-registro.php?form=register" class="btn btn-login">Elegir plan</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Transforma tu relación con tu mascota</h2>
            <p>Descubre un nuevo nivel de conexión con tu perro. Con Hachiko entenderás sus necesidades y emociones como nunca antes.</p>
            <a href="login-registro.php?form=register" class="btn btn-cta">Probar Hachiko 30 días gratis</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="logo">
                        <img src="../icons/dogmain.avif" alt="Logo" width="38" height="38">
                        <span>Hachiko<span class="accent">.</span></span>
                    </div>
                    <p>Construyendo puentes entre humanos y sus mejores amigos a través de la tecnología.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <img src="../icons/facebook.avif" alt="Facebook" width="24" height="24">
                            <!-- NOTA: Crear icono facebook.avif si no existe -->
                        </a>
                        <a href="#" class="social-icon">
                            <img src="../icons/twitter.avif" alt="Twitter" width="24" height="24">
                            <!-- NOTA: Crear icono twitter.avif si no existe -->
                        </a>
                        <a href="#" class="social-icon">
                            <img src="../icons/instagram.avif" alt="Instagram" width="24" height="24">
                            <!-- NOTA: Crear icono instagram.avif si no existe -->
                        </a>
                        <a href="#" class="social-icon">
                            <img src="../icons/linkedin.avif" alt="LinkedIn" width="24" height="24">
                            <!-- NOTA: Crear icono linkedin.avif si no existe -->
                        </a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Producto</h3>
                    <ul class="footer-links">
                        <li><a href="#features">Características</a></li>
                        <li><a href="#pricing">Precios</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#testimonials">Testimonios</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Empresa</h3>
                    <ul class="footer-links">
                        <li><a href="#">Sobre nosotros</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Prensa</a></li>
                        <li><a href="#">Trabaja con nosotros</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Soporte</h3>
                    <ul class="footer-links">
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Política de Privacidad</a></li>
                        <li><a href="#">Términos de Servicio</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Hachiko. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../js/landing.js"></script>
</body>
</html>