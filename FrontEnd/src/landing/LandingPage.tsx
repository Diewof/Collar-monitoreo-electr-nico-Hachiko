import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useTheme } from '../shared/ThemeContext'
import '../styles/landing.css'

const FEATURES = [
  { icon: '/icons/emotion.avif',    title: 'Detección de emociones',    text: 'Analiza el comportamiento de tu mascota con sensores de última generación.' },
  { icon: '/icons/activity.avif',   title: 'Análisis de comportamiento', text: 'Patrones de actividad diaria para entender mejor a tu perro.' },
  { icon: '/icons/alert.avif',      title: 'Alertas personalizadas',     text: 'Notificaciones instantáneas cuando algo inusual sucede.' },
  { icon: '/icons/intuitive.avif',  title: 'App intuitiva',              text: 'Interfaz simple y clara para que todo esté a un toque.' },
  { icon: '/icons/battery.avif',    title: 'Batería duradera',           text: 'Hasta 30 días de autonomía con una sola carga.' },
  { icon: '/icons/community.avif',  title: 'Comunidad',                  text: 'Únete a miles de dueños que ya confían en Hachiko.' },
]

const STEPS = [
  { num: 1, title: 'Coloca el collar',    text: 'Ajusta el collar Hachiko al cuello de tu mascota.' },
  { num: 2, title: 'Conecta la app',      text: 'Vincula el dispositivo desde el portal en segundos.' },
  { num: 3, title: 'Recibe insights',     text: 'Consulta estadísticas y alertas en tiempo real.' },
  { num: 4, title: 'Mejora su bienestar', text: 'Actúa con información real para cuidar mejor a tu perro.' },
]

const TESTIMONIALS = [
  { img: '/images/user1.avif',  name: 'María García',    role: 'Dueña de Luna',   text: '"Gracias a Hachiko supe que Luna estaba ansiosa antes de que yo lo notara. Increíble."' },
  { img: '/images/mochi.avif',  name: 'Carlos López',    role: 'Dueño de Mochi',  text: '"El análisis de actividad me ayudó a ajustar las rutinas de Mochi. Muy recomendado."' },
  { img: '/images/bailey.avif', name: 'Ana Martínez',    role: 'Dueña de Bailey', text: '"Las alertas son precisas. Me avisó de un problema de salud antes de que empeorara."' },
]

const PLANES = [
  { nombre: 'Básico',   precio: '9,99',  popular: false, features: ['1 mascota', 'Monitoreo básico', 'Alertas estándar', 'Historial 7 días'] },
  { nombre: 'Premium',  precio: '19,99', popular: true,  features: ['3 mascotas', 'Monitoreo avanzado', 'Alertas personalizadas', 'Historial 30 días', 'Soporte prioritario'] },
  { nombre: 'Familia',  precio: '29,99', popular: false, features: ['Mascotas ilimitadas', 'Análisis completo', 'Todas las alertas', 'Historial ilimitado', 'Soporte 24/7'] },
]

export default function LandingPage() {
  const { theme, toggleTheme } = useTheme()
  const [menuOpen, setMenuOpen] = useState(false)

  return (
    <>
      {/* ── Header / Navbar ───────────────────────────────────────────── */}
      <header className="landing-header">
        <div className="landing-container">
          <nav className="landing-nav">
            {/* Logo */}
            <div className="landing-logo">
              <img src="/icons/dogmain.avif" alt="Hachiko" width={36} height={36} />
              <span>Hachi<span className="accent">ko</span></span>
            </div>

            {/* Links — ocultos en tablet/mobile */}
            <ul className="landing-nav-links">
              <li><a href="#features">Características</a></li>
              <li><a href="#how-it-works">Cómo funciona</a></li>
              <li><a href="#testimonials">Testimonios</a></li>
              <li><a href="#pricing">Precios</a></li>
            </ul>

            {/* Acciones derechas */}
            <div className="landing-nav-end">
              {/* Theme toggle — dentro del nav, no fijo */}
              <button className="landing-theme-btn" onClick={toggleTheme} aria-label="Cambiar tema">
                <img
                  src={theme === 'dark' ? '/icons/sun.avif' : '/icons/moon.avif'}
                  alt=""
                  width={20}
                  height={20}
                />
              </button>

              {/* Auth buttons — ocultos en mobile */}
              <div className="landing-auth-btns">
                <Link to="/login"    className="lbtn lbtn-outline">Iniciar Sesión</Link>
                <Link to="/registro" className="lbtn lbtn-primary">Registrarse</Link>
              </div>

              {/* Hamburger — visible solo en mobile */}
              <button
                className="landing-hamburger"
                onClick={() => setMenuOpen((o) => !o)}
                aria-label="Menú"
                aria-expanded={menuOpen}
              >
                <span /><span /><span />
              </button>
            </div>
          </nav>
        </div>

        {/* Mobile dropdown */}
        {menuOpen && (
          <div className="landing-mobile-menu">
            <a href="#features"     onClick={() => setMenuOpen(false)}>Características</a>
            <a href="#how-it-works" onClick={() => setMenuOpen(false)}>Cómo funciona</a>
            <a href="#testimonials" onClick={() => setMenuOpen(false)}>Testimonios</a>
            <a href="#pricing"      onClick={() => setMenuOpen(false)}>Precios</a>
            <div className="landing-mobile-auth">
              <Link to="/login"    className="lbtn lbtn-outline" onClick={() => setMenuOpen(false)}>Iniciar Sesión</Link>
              <Link to="/registro" className="lbtn lbtn-primary" onClick={() => setMenuOpen(false)}>Registrarse</Link>
            </div>
          </div>
        )}
      </header>

      {/* ── Hero ────────────────────────────────────────────────────────── */}
      <section className="hero">
        <div className="landing-container">
          <div className="hero-content">
            <div className="hero-text">
              <h1>Conoce el <span>bienestar</span> de tu mascota</h1>
              <p>Hachiko es el collar inteligente que monitorea las emociones y actividad de tu perro, enviándote alertas en tiempo real.</p>
              <Link to="/registro" className="lbtn lbtn-primary lbtn-cta">Comenzar ahora</Link>
            </div>
            <div className="hero-image float-animation">
              <img src="/images/slide1.avif" alt="Hachiko collar" className="hero-img" />
              <div className="floating-badge badge-1">
                <div className="badge-icon"><img src="/icons/heart-rate.avif" alt="" width={24} height={24} /></div>
                <div className="badge-text"><h3>Frecuencia cardiaca</h3><p>Monitoreo continuo</p></div>
              </div>
              <div className="floating-badge badge-2">
                <div className="badge-icon"><img src="/icons/activity.avif" alt="" width={24} height={24} /></div>
                <div className="badge-text"><h3>Actividad diaria</h3><p>En tiempo real</p></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── Features ────────────────────────────────────────────────────── */}
      <section className="features" id="features">
        <div className="landing-container">
          <div className="section-title">
            <h2>Características</h2>
            <p>Todo lo que necesitas para cuidar a tu mascota de manera inteligente.</p>
          </div>
          <div className="features-grid">
            {FEATURES.map((f, i) => (
              <div key={i} className="feature-card">
                <div className="feature-icon">
                  <img src={f.icon} alt={f.title} width={36} height={36} />
                </div>
                <h3>{f.title}</h3>
                <p>{f.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── How it works ────────────────────────────────────────────────── */}
      <section className="how-it-works" id="how-it-works">
        <div className="landing-container">
          <div className="section-title">
            <h2>Cómo funciona</h2>
            <p>Empieza en cuatro sencillos pasos.</p>
          </div>
          <div className="steps">
            {STEPS.map((s) => (
              <div key={s.num} className="step">
                <div className="step-number">{s.num}</div>
                <h3>{s.title}</h3>
                <p>{s.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Testimonials ────────────────────────────────────────────────── */}
      <section className="testimonials" id="testimonials">
        <div className="landing-container">
          <div className="section-title">
            <h2>Lo que dicen nuestros usuarios</h2>
          </div>
          <div className="testimonials-container">
            {TESTIMONIALS.map((t, i) => (
              <div key={i} className="testimonial-card">
                <p className="testimonial-content">{t.text}</p>
                <div className="testimonial-author">
                  <div className="author-image">
                    <img src={t.img} alt={t.name} />
                  </div>
                  <div className="author-info">
                    <h4>{t.name}</h4>
                    <p>{t.role}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Pricing ─────────────────────────────────────────────────────── */}
      <section className="pricing" id="pricing">
        <div className="landing-container">
          <div className="section-title">
            <h2>Planes y precios</h2>
            <p>Elige el plan que mejor se adapte a ti y a tu mascota.</p>
          </div>
          <div className="pricing-options">
            {PLANES.map((p) => (
              <div key={p.nombre} className={`pricing-card ${p.popular ? 'popular' : ''}`}>
                {p.popular && <span className="popular-tag">Más popular</span>}
                <div className="pricing-header">
                  <h3>{p.nombre}</h3>
                  <div className="price">€{p.precio}<span>/mes</span></div>
                </div>
                <ul className="pricing-features">
                  {p.features.map((f, i) => <li key={i}>{f}</li>)}
                </ul>
                <Link to="/registro" className="lbtn lbtn-primary" style={{ display: 'block', textAlign: 'center' }}>
                  Empezar ahora
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ─────────────────────────────────────────────────────────── */}
      <section className="cta">
        <div className="landing-container">
          <h2>Transforma tu relación con tu mascota</h2>
          <p>Únete a miles de propietarios que ya confían en Hachiko para cuidar a sus perros.</p>
          <Link to="/registro" className="lbtn lbtn-primary lbtn-cta">Crear cuenta gratis</Link>
        </div>
      </section>

      {/* ── Footer ──────────────────────────────────────────────────────── */}
      <footer className="footer">
        <div className="landing-container">
          <div className="footer-content">
            <div className="footer-column">
              <div className="landing-logo">
                <img src="/icons/dogmain.avif" alt="Hachiko" width={28} height={28} />
                Hachiko
              </div>
              <p>El collar inteligente para el bienestar de tu mascota.</p>
              <div className="social-links">
                <a href="#" className="social-icon"><img src="/icons/community.avif" alt="Social" width={18} height={18} /></a>
              </div>
            </div>
            <div className="footer-column">
              <h3>Producto</h3>
              <ul className="footer-links">
                <li><a href="#features">Características</a></li>
                <li><a href="#how-it-works">Cómo funciona</a></li>
                <li><a href="#pricing">Precios</a></li>
              </ul>
            </div>
            <div className="footer-column">
              <h3>Cuenta</h3>
              <ul className="footer-links">
                <li><Link to="/login">Iniciar sesión</Link></li>
                <li><Link to="/registro">Registrarse</Link></li>
                <li><Link to="/recuperar-password">Recuperar contraseña</Link></li>
              </ul>
            </div>
            <div className="footer-column">
              <h3>Soporte</h3>
              <ul className="footer-links">
                <li><a href="#">Centro de ayuda</a></li>
                <li><a href="#">Contacto</a></li>
              </ul>
            </div>
          </div>
          <div className="copyright">
            <p>© {new Date().getFullYear()} Hachiko. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </>
  )
}
