import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../shared/AuthContext'
import { useTheme } from '../shared/ThemeContext'
import { authApi } from '../api/authApi'
import { adminApi } from '../api/adminApi'
import { useNotification } from '../shared/Notification'
import { timeAgo } from '../shared/utils'
import UsuarioTable from './UsuarioTable'
import UsuarioForm from './UsuarioForm'
import type { DashboardStatsDTO, UsuarioDTO } from '../types/api'
import '../styles/main.css'
import '../styles/admin.css'

type Section = 'dashboard' | 'usuarios' | 'add_user'

/**
 * Panel de administración — reemplaza admin_main.php.
 * Sin verificación de $_SESSION; la protección la da AdminRoute (role === ADMIN en JWT).
 * Los datos vienen de GET /api/admin/stats y GET /api/admin/usuarios.
 */
export default function AdminDashboardPage() {
  const { user, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const { showError } = useNotification()

  const [section, setSection] = useState<Section>('dashboard')
  const [stats, setStats] = useState<DashboardStatsDTO | null>(null)
  const [usuarios, setUsuarios] = useState<UsuarioDTO[]>([])
  const [loading, setLoading] = useState(true)

  const loadData = useCallback(async () => {
    setLoading(true)
    try {
      const [statsData, usuariosData] = await Promise.all([
        adminApi.getStats(),
        adminApi.listUsuarios(),
      ])
      setStats(statsData)
      setUsuarios(usuariosData)
    } catch {
      showError('Error al cargar datos del panel')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { loadData() }, [loadData])

  const handleLogout = async () => {
    try { await authApi.logout() } catch { /* token ya inválido */ }
    logout()
  }

  const activityIcon = (tipo: string) => {
    if (tipo.toLowerCase().includes('fail') || tipo.toLowerCase().includes('failed')) return '/icons/warning.avif'
    if (tipo.toLowerCase().includes('regist')) return '/icons/register.avif'
    return '/icons/activity.avif'
  }

  const activityClass = (tipo: string) => {
    if (tipo.toLowerCase().includes('fail')) return 'warning'
    if (tipo.toLowerCase().includes('regist')) return 'info'
    return 'success'
  }

  return (
    <div>
      {/* Navbar */}
      <nav className="navbar">
        <div className="nav-container">
          <div className="logo">
            <img src="/icons/dogmain.avif" alt="Hachiko" width={32} height={32} />
            Hachiko Admin
            <span className="admin-badge">ADMIN</span>
          </div>
          <ul className="nav-menu">
            <li>
              <button className="nav-link" onClick={() => setSection('dashboard')}>
                Dashboard
              </button>
            </li>
            <li>
              <button className="nav-link" onClick={() => setSection('usuarios')}>
                Usuarios
              </button>
            </li>
            <li>
              <button className="nav-link" onClick={() => setSection('add_user')}>
                + Nuevo usuario
              </button>
            </li>
            <li>
              <button className="nav-link" onClick={handleLogout} style={{ color: 'var(--color-error)' }}>
                Cerrar sesión
              </button>
            </li>
          </ul>
          <button className="theme-toggle" onClick={toggleTheme} aria-label="Cambiar tema">
            <img src={theme === 'dark' ? '/icons/sun.avif' : '/icons/moon.avif'} alt="" width={20} height={20} />
          </button>
        </div>
      </nav>

      <div className="admin-main-content">
        {/* Header */}
        <div className="admin-header">
          <div className="header-content">
            <div>
              <h1>Panel de Administración</h1>
              <p>Bienvenido, {user?.email}</p>
            </div>
          </div>
        </div>

        {loading ? (
          <div style={{ textAlign: 'center', padding: '60px' }}>Cargando...</div>
        ) : (
          <>
            {/* ── Dashboard ─────────────────────────────────────────────── */}
            {(section === 'dashboard' || section === 'usuarios') && stats && (
              <div className="stats-container">
                <div className="stat-card">
                  <div className="stat-icon">
                    <img src="/icons/user.avif" alt="" width={28} height={28} />
                  </div>
                  <div className="stat-details">
                    <h3>Total usuarios</h3>
                    <p className="stat-value">{stats.totalUsuarios}</p>
                  </div>
                </div>
                <div className="stat-card">
                  <div className="stat-icon">
                    <img src="/icons/activity.avif" alt="" width={28} height={28} />
                  </div>
                  <div className="stat-details">
                    <h3>Logins hoy</h3>
                    <p className="stat-value">{stats.loginHoy}</p>
                  </div>
                </div>
                <div className="stat-card">
                  <div className="stat-icon">
                    <img src="/icons/warning.avif" alt="" width={28} height={28} />
                  </div>
                  <div className="stat-details">
                    <h3>Intentos fallidos</h3>
                    <p className="stat-value">{stats.intentosFallidos}</p>
                  </div>
                </div>
                <div className="stat-card">
                  <div className="stat-icon">
                    <img src="/icons/lock.avif" alt="" width={28} height={28} />
                  </div>
                  <div className="stat-details">
                    <h3>Cuentas bloqueadas</h3>
                    <p className="stat-value">{stats.cuentasBloqueadas}</p>
                  </div>
                </div>
              </div>
            )}

            {/* ── Tabla de usuarios ─────────────────────────────────────── */}
            {(section === 'dashboard' || section === 'usuarios') && (
              <div className="admin-section">
                <div className="section-header">
                  <h2>Usuarios</h2>
                  <button className="btn btn-primary btn-sm" onClick={() => setSection('add_user')}>
                    + Nuevo usuario
                  </button>
                </div>
                <UsuarioTable usuarios={usuarios} onRefresh={loadData} />
              </div>
            )}

            {/* ── Actividad reciente ────────────────────────────────────── */}
            {section === 'dashboard' && stats && stats.actividad?.length > 0 && (
              <div className="admin-section">
                <h2>Actividad Reciente</h2>
                <div className="activity-log">
                  {stats.actividad.map((a, i) => (
                    <div key={i} className="activity-item">
                      <div className={`activity-icon ${activityClass(a.tipo)}`}>
                        <img src={activityIcon(a.tipo)} alt="" width={20} height={20} />
                      </div>
                      <div className="activity-details">
                        <p>{a.descripcion}</p>
                        <span className="activity-time">{timeAgo(a.fecha)}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* ── Crear usuario ─────────────────────────────────────────── */}
            {section === 'add_user' && (
              <div className="admin-section form-container">
                <UsuarioForm
                  onSuccess={() => { loadData(); setSection('usuarios') }}
                  onCancel={() => setSection('dashboard')}
                />
              </div>
            )}
          </>
        )}
      </div>
    </div>
  )
}
