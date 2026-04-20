import { useState, type FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import { propietarioApi } from '../api/propietarioApi'
import { useAuth } from '../shared/AuthContext'
import { useNotification } from '../shared/Notification'
import { useLocationCascade } from '../shared/useLocationCascade'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/main.css'

/**
 * Flujo de primer login — reemplaza propietario_modal.php.
 * Se muestra cuando LoginResponse.requiresProfileCompletion = true.
 * Reemplaza $_SESSION['is_first_login'] con el campo del JWT response.
 */
export default function CompletarPerfilPage() {
  const { showError, showSuccess } = useNotification()
  const { user } = useAuth()
  const navigate = useNavigate()

  const [primerNombre, setPrimerNombre] = useState('')
  const [segundoNombre, setSegundoNombre] = useState('')
  const [apellido, setApellido] = useState('')
  const [segundoApellido, setSegundoApellido] = useState('')
  const [telefono, setTelefono] = useState('')
  const [direccion, setDireccion] = useState('')
  const [planId, setPlanId] = useState<number | ''>('')
  const [loading, setLoading] = useState(false)

  const {
    paises, departamentos, ciudades, planes,
    paisId, setPaisId,
    departamentoId, setDepartamentoId,
    ciudadId, setCiudadId,
  } = useLocationCascade()

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    if (!ciudadId || !planId) {
      showError('Completa todos los campos requeridos')
      return
    }
    setLoading(true)
    try {
      await propietarioApi.create({
        usuarioId: user!.userId,
        primerNombre,
        segundoNombre: segundoNombre || undefined,
        apellido,
        segundoApellido: segundoApellido || undefined,
        telefono,
        direccion,
        ciudadId: Number(ciudadId),
        planId: Number(planId),
      })
      showSuccess('Perfil completado. ¡Bienvenido!')
      navigate('/dashboard')
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al guardar el perfil')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="main-content" style={{ maxWidth: 600, margin: '40px auto' }}>
      <h2>Completar Perfil</h2>
      <p style={{ color: 'var(--color-text-muted)', marginBottom: '24px' }}>
        Para continuar, completa tu información de propietario.
      </p>

      <form onSubmit={handleSubmit}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
          <div className="input-group">
            <input
              className="input-field"
              placeholder="Primer nombre *"
              value={primerNombre}
              onChange={(e) => setPrimerNombre(e.target.value)}
              required
              pattern="^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,45}$"
              style={{ padding: '14px' }}
            />
          </div>
          <div className="input-group">
            <input
              className="input-field"
              placeholder="Segundo nombre"
              value={segundoNombre}
              onChange={(e) => setSegundoNombre(e.target.value)}
              pattern="^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,45}$"
              style={{ padding: '14px' }}
            />
          </div>
          <div className="input-group">
            <input
              className="input-field"
              placeholder="Apellido *"
              value={apellido}
              onChange={(e) => setApellido(e.target.value)}
              required
              pattern="^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,45}$"
              style={{ padding: '14px' }}
            />
          </div>
          <div className="input-group">
            <input
              className="input-field"
              placeholder="Segundo apellido"
              value={segundoApellido}
              onChange={(e) => setSegundoApellido(e.target.value)}
              pattern="^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,45}$"
              style={{ padding: '14px' }}
            />
          </div>
          <div className="input-group" style={{ gridColumn: '1 / -1' }}>
            <input
              className="input-field"
              placeholder="Teléfono *"
              value={telefono}
              onChange={(e) => setTelefono(e.target.value)}
              required
              pattern="^\d{7,15}$"
              style={{ padding: '14px' }}
            />
          </div>
          <div className="input-group" style={{ gridColumn: '1 / -1' }}>
            <input
              className="input-field"
              placeholder="Dirección *"
              value={direccion}
              onChange={(e) => setDireccion(e.target.value)}
              required
              style={{ padding: '14px' }}
            />
          </div>
        </div>

        {/* Dropdowns en cascada — reemplaza propietario_modal.php fetch chain */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginTop: '16px' }}>
          <div className="input-group">
            <select
              className="input-field"
              value={paisId}
              onChange={(e) => setPaisId(Number(e.target.value))}
              required
              style={{ padding: '14px' }}
            >
              <option value="">País *</option>
              {paises.map((p) => (
                <option key={p.paisId} value={p.paisId}>{p.nombre}</option>
              ))}
            </select>
          </div>
          <div className="input-group">
            <select
              className="input-field"
              value={departamentoId}
              onChange={(e) => setDepartamentoId(Number(e.target.value))}
              required
              disabled={!paisId}
              style={{ padding: '14px' }}
            >
              <option value="">Departamento *</option>
              {departamentos.map((d) => (
                <option key={d.departamentoId} value={d.departamentoId}>{d.nombre}</option>
              ))}
            </select>
          </div>
          <div className="input-group">
            <select
              className="input-field"
              value={ciudadId}
              onChange={(e) => setCiudadId(Number(e.target.value))}
              required
              disabled={!departamentoId}
              style={{ padding: '14px' }}
            >
              <option value="">Ciudad *</option>
              {ciudades.map((c) => (
                <option key={c.ciudadId} value={c.ciudadId}>{c.nombre}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="input-group" style={{ marginTop: '16px' }}>
          <select
            className="input-field"
            value={planId}
            onChange={(e) => setPlanId(Number(e.target.value))}
            required
            style={{ padding: '14px' }}
          >
            <option value="">Plan *</option>
            {planes.map((p) => (
              <option key={p.planId} value={p.planId}>
                {p.nombre} — ${p.precio}
              </option>
            ))}
          </select>
        </div>

        <button
          type="submit"
          className="submit-btn"
          disabled={loading}
          style={{ marginTop: '24px', width: '100%' }}
        >
          {loading ? <span className="spinner" /> : 'Guardar y continuar'}
        </button>
      </form>
    </div>
  )
}
