<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hachiko - Collar Emocional para tu Mascota</title>
    <!-- Importación de fuente Poppins de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Enlace a la hoja de estilos -->
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Sección Hero -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>El collar que entiende los sentimientos de tu mascota</h1>
                <p>Hachiko es el primer collar inteligente que monitorea el bienestar emocional de tu perro, ayudándote a entender sus necesidades y mejorar su calidad de vida.</p>
                <button class="cta-button">Conoce más</button>
            </div>
            <div class="hero-image">
                <img src="../images/collar-hero.png" alt="Collar Hachiko" width="500">
            </div>
        </section>

        <!-- Beneficios -->
        <section class="benefits-section">
            <h2>¿Por qué tu mascota necesita Hachiko?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <img src="../icons/heart-rate.png" alt="Monitoreo" width="60">
                    <h3>Monitoreo emocional 24/7</h3>
                    <p>Registra los estados de ánimo de tu perro y te alerta sobre situaciones de estrés o ansiedad.</p>
                </div>
                <div class="benefit-card">
                    <img src="../icons/dog-happy.png" alt="Feliz" width="60">
                    <h3>Mejora el bienestar</h3>
                    <p>Identifica patrones emocionales y recibe recomendaciones para aumentar su felicidad.</p>
                </div>
                <div class="benefit-card">
                    <img src="../icons/activity.png" alt="Actividad" width="60">
                    <h3>Seguimiento de actividad</h3>
                    <p>Controla su ejercicio diario y descanso para un equilibrio perfecto.</p>
                </div>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section class="features-section">
            <h2>Características principales</h2>
            <div class="feature-tabs">
                <div class="feature-tab active" data-tab="emotions">
                    <img src="../icons/emotion.png" alt="Emociones" width="40">
                    <span>Análisis emocional</span>
                </div>
                <div class="feature-tab" data-tab="alerts">
                    <img src="../icons/alert.png" alt="Alertas" width="40">
                    <span>Alertas tempranas</span>
                </div>
                <div class="feature-tab" data-tab="reports">
                    <img src="../icons/report.png" alt="Reportes" width="40">
                    <span>Reportes detallados</span>
                </div>
            </div>
            <div class="feature-content active" id="emotions-content">
                <div class="feature-text">
                    <h3>Entiende lo que siente tu peludo</h3>
                    <p>Nuestra tecnología patentada analiza frecuencia cardíaca, movimiento y vocalizaciones para determinar si tu mascota está feliz, estresada, ansiosa o relajada.</p>
                    <ul>
                        <li>Identifica disparadores de estrés</li>
                        <li>Reconoce patrones de felicidad</li>
                        <li>Detecta cambios emocionales</li>
                    </ul>
                </div>
                <div class="feature-image">
                    <img src="../images/emotion-chart.png" alt="Análisis emocional" width="450">
                </div>
            </div>
        </section>

        <!-- Testimonios -->
        <section class="testimonials-section">
            <h2>Dueños felices, perros más felices</h2>
            <div class="testimonials-carousel">
                <div class="testimonial">
                    <img src="../images/user1.jpg" alt="Cliente" class="user-avatar" width="80">
                    <p>"Hachiko me ayudó a entender que mi perra sufría ansiedad por separación. Con las recomendaciones, ahora está mucho más tranquila cuando salgo."</p>
                    <span>- María G., dueña de Luna</span>
                </div>
                <div class="testimonial">
                    <img src="../images/user2.jpg" alt="Cliente" class="user-avatar" width="80">
                    <p>"Gracias a las alertas del collar, descubrí que los ruidos fuertes afectaban a Rocky. Ahora tenemos un refugio seguro para él durante tormentas."</p>
                    <span>- Carlos M., dueño de Rocky</span>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="final-cta">
            <h2>Dale a tu mascota el regalo del bienestar emocional</h2>
            <p>Únete a miles de dueños responsables que ya están mejorando la vida de sus perros con Hachiko.</p>
            <button class="cta-button">Adquiere tu Hachiko hoy</button>
            <div class="guarantee-badge">
                <img src="../icons/shield.png" alt="Garantía" width="30">
                <span>30 días de garantía de satisfacción</span>
            </div>
        </section>
    </main>

    <!-- Pie de página -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../icons/dogmain.png" alt="Hachiko" width="40">
                <span>Hachiko - Bienestar emocional para tu mascota</span>
            </div>
            <div class="footer-links">
                <a href="#">Soporte</a>
                <a href="#">Preguntas frecuentes</a>
                <a href="#">Política de privacidad</a>
            </div>
            <div class="footer-social">
                <a href="#"><img src="../icons/facebook.png" alt="Facebook" width="24"></a>
                <a href="#"><img src="../icons/instagram.png" alt="Instagram" width="24"></a>
                <a href="#"><img src="../icons/twitter.png" alt="Twitter" width="24"></a>
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