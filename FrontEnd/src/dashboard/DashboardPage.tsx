import { useState, useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../shared/AuthContext'
import { useTheme } from '../shared/ThemeContext'
import { authApi } from '../api/authApi'
import InactivityTimer from '../shared/InactivityTimer'
import '../styles/main.css'

const SLIDES = [
  { img: '/images/slide1.avif', title: 'Monitorea a tu mejor amigo', text: 'Accede a estadísticas de actividad y comportamiento en tiempo real.' },
  { img: '/images/slide2.avif', title: 'Alertas inteligentes', text: 'Recibe notificaciones cuando Hachiko detecte patrones inusuales.' },
  { img: '/images/slide3.avif', title: 'Historial completo', text: 'Consulta el historial de salud y actividad de tu mascota.' },
]

const FEATURES = [
  { icon: '/icons/emotion.avif', title: 'Detección de emociones', text: 'Análisis de comportamiento basado en sensores avanzados.' },
  { icon: '/icons/activity.avif', title: 'Análisis de actividad', text: 'Monitoreo continuo de los niveles de actividad física.' },
  { icon: '/icons/alert.avif', title: 'Alertas personalizadas', text: 'Configura alertas para eventos específicos.' },
]

/**
 * Dashboard del usuario autenticado.
 * Reemplaza main.php: sin verificación de $_SESSION, sin consultas directas a BD.
 * El timer visual reemplaza el setTimeout de main.js que llamaba a auto_logout.php.
 */
export default function DashboardPage() {
  const { user, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const [activeSlide, setActiveSlide] = useState(0)
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null)

  // Carrusel automático — igual que main.php (cada 5 segundos)
  useEffect(() => {
    intervalRef.current = setInterval(() => {
      setActiveSlide((s) => (s + 1) % SLIDES.length)
    }, 5000)
    return () => { if (intervalRef.current) clearInterval(intervalRef.current) }
  }, [])

  const handleLogout = async () => {
    try { await authApi.logout() } catch { /* token ya inválido */ }
    logout()
  }

  return (
    <div>
      {/* Navbar */}
      <nav className="navbar">
        <div className="nav-container">
          <div className="logo">
            <img src="/icons/dogmain.avif" alt="Hachiko" width={32} height={32} />
            Hachiko
          </div>

          <ul className="nav-menu">
            <li className="nav-item">
              <Link to="/mascotas" className="nav-link">
                <img src="/icons/heart-rate.avif" alt="" width={18} height={18} />
                Mis Mascotas
              </Link>
            </li>
            <li className="nav-item dropdown">
              <span className="nav-link user-profile">
                <img src="/icons/user.avif" alt="" className="user-avatar" width={28} height={28} />
                <span className="user-name">{user?.email}</span>
                <img src="/icons/arrow-down.avif" alt="" width={16} height={16} />
              </span>
              <ul className="dropdown-menu">
                <li><Link to="/perfil" className="dropdown-item">Editar perfil</Link></li>
                <li><hr className="dropdown-divider" /></li>
                <li>
                  <button
                    onClick={handleLogout}
                    className="dropdown-item"
                    style={{ background: 'none', border: 'none', width: '100%', textAlign: 'left', cursor: 'pointer' }}
                  >
                    Cerrar sesión
                  </button>
                </li>
              </ul>
            </li>
          </ul>

          <button
            className="theme-toggle"
            onClick={toggleTheme}
            aria-label="Cambiar tema"
          >
            <img src={theme === 'dark' ? '/icons/sun.avif' : '/icons/moon.avif'} alt="" width={24} height={24} />
          </button>
        </div>
      </nav>

      {/* Contenido principal */}
      <main className="main-content">
        {/* Carrusel — migrado de main.php */}
        <div className="carousel-container">
          <div className="carousel">
            {SLIDES.map((slide, i) => (
              <div key={i} className={`carousel-item ${i === activeSlide ? 'active' : ''}`}>
                <img src={slide.img} alt={slide.title} className="carousel-image" />
                <div className="carousel-caption">
                  <h2>{slide.title}</h2>
                  <p>{slide.text}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="carousel-controls">
            <button
              className="carousel-control"
              onClick={() => setActiveSlide((s) => (s - 1 + SLIDES.length) % SLIDES.length)}
            >‹</button>
            <button
              className="carousel-control"
              onClick={() => setActiveSlide((s) => (s + 1) % SLIDES.length)}
            >›</button>
          </div>

          <div className="carousel-indicators">
            {SLIDES.map((_, i) => (
              <button
                key={i}
                className={`indicator ${i === activeSlide ? 'active' : ''}`}
                onClick={() => setActiveSlide(i)}
              />
            ))}
          </div>
        </div>

        {/* Tarjetas de características */}
        <div className="features-container">
          {FEATURES.map((f, i) => (
            <div key={i} className="feature-card">
              <div className="feature-icon">
                <img src={f.icon} alt={f.title} width={40} height={40} />
              </div>
              <h3>{f.title}</h3>
              <p>{f.text}</p>
            </div>
          ))}
        </div>

        {/* Acceso rápido a mascotas */}
        <div style={{ marginTop: '40px', textAlign: 'center' }}>
          <Link to="/mascotas" className="card-link" style={{ fontSize: '16px', padding: '14px 30px', border: '1px solid var(--color-primary)', borderRadius: '8px', display: 'inline-flex' }}>
            Ver mis mascotas →
          </Link>
        </div>
      </main>

      {/* Timer visual de inactividad — decorativo, reemplaza setTimeout→auto_logout.php */}
      <div className="inactivity-timer">
        <img src="/icons/warning.avif" alt="" className="timer-icon" width={16} height={16} />
        <InactivityTimer />
      </div>
    </div>
  )
}
