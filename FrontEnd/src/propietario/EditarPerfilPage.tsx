import { useState, useEffect, type FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import { propietarioApi } from '../api/propietarioApi'
import { referenciaApi } from '../api/referenciaApi'
import { useNotification } from '../shared/Notification'
import { useLocationCascade } from '../shared/useLocationCascade'
import type { PropietarioDTO } from '../types/api'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/main.css'

/**
 * Editar perfil de propietario.
 * Pre-rellena los datos con GET /api/propietario/me.
 * Reemplaza editar_perfil.php y perfil_modal.php.
 */
export default function EditarPerfilPage() {
  const { showError, showSuccess } = useNotification()
  const navigate = useNavigate()

  const [propietario, setPropietario] = useState<PropietarioDTO | null>(null)
  const [primerNombre, setPrimerNombre] = useState('')
  const [segundoNombre, setSegundoNombre] = useState('')
  const [apellido, setApellido] = useState('')
  const [segundoApellido, setSegundoApellido] = useState('')
  const [telefono, setTelefono] = useState('')
  const [direccion, setDireccion] = useState('')
  const [planId, setPlanId] = useState<number | ''>('')
  const [loading, setLoading] = useState(false)
  const [fetching, setFetching] = useState(true)

  const {
    paises, departamentos, ciudades, planes,
    paisId, setPaisId,
    departamentoId, setDepartamentoId,
    ciudadId, setCiudadId,
  } = useLocationCascade()

  // Cargar datos actuales del propietario y pre-rellenar el formulario
  useEffect(() => {
    propietarioApi.getMe()
      .then(async (data) => {
        setPropietario(data)
        setPrimerNombre(data.primerNombre)
        setSegundoNombre(data.segundoNombre ?? '')
        setApellido(data.apellido)
        setSegundoApellido(data.segundoApellido ?? '')
        setTelefono(data.telefono)
        setDireccion(data.residencia.direccion)
        setPlanId(data.planId)

        // Pre-rellenar cascada de localización
        setPaisId(data.residencia.paisId)
        // Los departamentos se cargarán automáticamente por el useEffect del hook
        // Esperamos a que estén disponibles para seleccionar departamento y ciudad
        const deptos = await referenciaApi.getDepartamentos(data.residencia.paisId)
        if (deptos.length > 0) {
          setDepartamentoId(data.residencia.departamentoId)
          const ciudadesData = await referenciaApi.getCiudades(data.residencia.departamentoId)
          if (ciudadesData.length > 0) {
            setCiudadId(data.residencia.ciudadId)
          }
        }
      })
      .catch(() => showError('No se pudo cargar el perfil'))
      .finally(() => setFetching(false))
  }, [])

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    if (!propietario || !ciudadId || !planId) return
    setLoading(true)
    try {
      await propietarioApi.update(propietario.propietarioId, {
        propietarioId: propietario.propietarioId,
        primerNombre,
        segundoNombre: segundoNombre || undefined,
        apellido,
        segundoApellido: segundoApellido || undefined,
        telefono,
        direccion,
        ciudadId: Number(ciudadId),
        planId: Number(planId),
      })
      showSuccess('Perfil actualizado')
      navigate('/dashboard')
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al actualizar el perfil')
      }
    } finally {
      setLoading(false)
    }
  }

  if (fetching) {
    return <div className="main-content" style={{ textAlign: 'center', paddingTop: '60px' }}>Cargando...</div>
  }

  return (
    <div className="main-content" style={{ maxWidth: 600, margin: '40px auto' }}>
      <h2>Editar Perfil</h2>
      <p style={{ color: 'var(--color-text-muted)', marginBottom: '24px' }}>
        Actualiza tu información de propietario.
      </p>

      <form onSubmit={handleSubmit}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
          <div className="input-group">
            <input className="input-field" placeholder="Primer nombre *" value={primerNombre}
              onChange={(e) => setPrimerNombre(e.target.value)} required style={{ padding: '14px' }} />
          </div>
          <div className="input-group">
            <input className="input-field" placeholder="Segundo nombre" value={segundoNombre}
              onChange={(e) => setSegundoNombre(e.target.value)} style={{ padding: '14px' }} />
          </div>
          <div className="input-group">
            <input className="input-field" placeholder="Apellido *" value={apellido}
              onChange={(e) => setApellido(e.target.value)} required style={{ padding: '14px' }} />
          </div>
          <div className="input-group">
            <input className="input-field" placeholder="Segundo apellido" value={segundoApellido}
              onChange={(e) => setSegundoApellido(e.target.value)} style={{ padding: '14px' }} />
          </div>
          <div className="input-group" style={{ gridColumn: '1 / -1' }}>
            <input className="input-field" placeholder="Teléfono *" value={telefono}
              onChange={(e) => setTelefono(e.target.value)} required pattern="^\d{7,15}$"
              style={{ padding: '14px' }} />
          </div>
          <div className="input-group" style={{ gridColumn: '1 / -1' }}>
            <input className="input-field" placeholder="Dirección *" value={direccion}
              onChange={(e) => setDireccion(e.target.value)} required
              style={{ padding: '14px' }} />
          </div>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginTop: '16px' }}>
          <select className="input-field" value={paisId}
            onChange={(e) => setPaisId(Number(e.target.value))} required style={{ padding: '14px' }}>
            <option value="">País *</option>
            {paises.map((p) => <option key={p.paisId} value={p.paisId}>{p.nombre}</option>)}
          </select>
          <select className="input-field" value={departamentoId}
            onChange={(e) => setDepartamentoId(Number(e.target.value))}
            required disabled={!paisId} style={{ padding: '14px' }}>
            <option value="">Departamento *</option>
            {departamentos.map((d) => <option key={d.departamentoId} value={d.departamentoId}>{d.nombre}</option>)}
          </select>
          <select className="input-field" value={ciudadId}
            onChange={(e) => setCiudadId(Number(e.target.value))}
            required disabled={!departamentoId} style={{ padding: '14px' }}>
            <option value="">Ciudad *</option>
            {ciudades.map((c) => <option key={c.ciudadId} value={c.ciudadId}>{c.nombre}</option>)}
          </select>
        </div>

        <div className="input-group" style={{ marginTop: '16px' }}>
          <select className="input-field" value={planId}
            onChange={(e) => setPlanId(Number(e.target.value))} required style={{ padding: '14px' }}>
            <option value="">Plan *</option>
            {planes.map((p) => <option key={p.planId} value={p.planId}>{p.nombre} — ${p.precio}</option>)}
          </select>
        </div>

        <div style={{ display: 'flex', gap: '12px', marginTop: '24px' }}>
          <button type="button" className="submit-btn"
            style={{ background: 'var(--color-hover)', flex: 1 }}
            onClick={() => navigate('/dashboard')}>
            Cancelar
          </button>
          <button type="submit" className="submit-btn" disabled={loading} style={{ flex: 2 }}>
            {loading ? <span className="spinner" /> : 'Guardar cambios'}
          </button>
        </div>
      </form>
    </div>
  )
}
