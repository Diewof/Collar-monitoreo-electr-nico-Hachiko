import { useState, useEffect, useCallback } from 'react'
import { Link } from 'react-router-dom'
import { mascotaApi } from '../api/mascotaApi'
import { useNotification } from '../shared/Notification'
import { useAuth } from '../shared/AuthContext'
import { useTheme } from '../shared/ThemeContext'
import { authApi } from '../api/authApi'
import MascotaCard from './MascotaCard'
import MascotaForm from './MascotaForm'
import InactivityTimer from '../shared/InactivityTimer'
import type { MascotaDTO } from '../types/api'
import '../styles/main.css'

/**
 * Listado y gestión de mascotas.
 * Reemplaza: mascota_modal.php + perfil_mascota_modal.php + $_SESSION['propietario_id'].
 * El propietarioId lo resuelve el backend desde el JWT — el frontend nunca lo maneja.
 */
export default function MascotasPage() {
  const { user, logout } = useAuth()
  const { showError, showSuccess } = useNotification()

  const { theme, toggleTheme } = useTheme()
  const [mascotas, setMascotas] = useState<MascotaDTO[]>([])
  const [loading, setLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editando, setEditando] = useState<MascotaDTO | undefined>(undefined)

  const load = useCallback(async () => {
    try {
      const data = await mascotaApi.list()
      setMascotas(data)
    } catch {
      showError('No se pudieron cargar las mascotas')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { load() }, [load])

  const handleDelete = async (perroId: number) => {
    try {
      await mascotaApi.delete(perroId)
      showSuccess('Mascota eliminada')
      setMascotas((prev) => prev.filter((m) => m.perroId !== perroId))
    } catch {
      showError('Error al eliminar la mascota')
    }
  }

  const handleEdit = (mascota: MascotaDTO) => {
    setEditando(mascota)
    setShowForm(true)
  }

  const handleFormSuccess = () => {
    setShowForm(false)
    setEditando(undefined)
    load()
  }

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
            <li><Link to="/dashboard" className="nav-link">Dashboard</Link></li>
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
                  <button onClick={handleLogout}
                    className="dropdown-item"
                    style={{ background: 'none', border: 'none', width: '100%', textAlign: 'left', cursor: 'pointer' }}>
                    Cerrar sesión
                  </button>
                </li>
              </ul>
            </li>
          </ul>
          <button className="theme-toggle" onClick={toggleTheme} aria-label="Cambiar tema">
            <img src={theme === 'dark' ? '/icons/sun.avif' : '/icons/moon.avif'} alt="" width={24} height={24} />
          </button>
        </div>
      </nav>

      <main className="main-content">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
          <h2>Mis Mascotas</h2>
          <button className="submit-btn"
            style={{ padding: '10px 20px', width: 'auto' }}
            onClick={() => { setEditando(undefined); setShowForm(true) }}>
            + Agregar mascota
          </button>
        </div>

        {/* Modal inline del formulario */}
        {showForm && (
          <div style={{
            background: 'var(--color-card)', border: '1px solid var(--color-border)',
            borderRadius: '12px', padding: '24px', marginBottom: '24px'
          }}>
            <MascotaForm
              mascota={editando}
              onSuccess={handleFormSuccess}
              onCancel={() => { setShowForm(false); setEditando(undefined) }}
            />
          </div>
        )}

        {loading ? (
          <div style={{ textAlign: 'center', padding: '60px' }}>Cargando...</div>
        ) : mascotas.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '60px', color: 'var(--color-text-muted)' }}>
            <img src="/icons/dog-happy.avif" alt="" width={64} height={64} style={{ marginBottom: '16px', opacity: 0.5 }} />
            <p>Aún no tienes mascotas registradas.</p>
            <button className="submit-btn"
              style={{ marginTop: '16px', width: 'auto', padding: '12px 24px' }}
              onClick={() => setShowForm(true)}>
              Registrar primera mascota
            </button>
          </div>
        ) : (
          <div className="card-container">
            {mascotas.map((m) => (
              <MascotaCard key={m.perroId} mascota={m} onEdit={handleEdit} onDelete={handleDelete} />
            ))}
          </div>
        )}
      </main>

      <div className="inactivity-timer">
        <img src="/icons/warning.avif" alt="" className="timer-icon" width={16} height={16} />
        <InactivityTimer />
      </div>
    </div>
  )
}
