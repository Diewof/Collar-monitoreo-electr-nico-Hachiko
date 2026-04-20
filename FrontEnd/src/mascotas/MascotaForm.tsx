import { useState, useEffect, type FormEvent } from 'react'
import { mascotaApi } from '../api/mascotaApi'
import { referenciaApi } from '../api/referenciaApi'
import { useNotification } from '../shared/Notification'
import type { MascotaDTO, RazaDTO } from '../types/api'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'

interface Props {
  mascota?: MascotaDTO       // Si se pasa, es modo edición
  onSuccess: () => void
  onCancel: () => void
}

/**
 * Formulario crear/editar mascota — reemplaza mascota_modal.php y perfil_mascota_modal.php.
 * Ya no consulta la BD directamente; usa POST/PUT /api/mascotas.
 * La resolución de propietarioId la hace el backend desde el JWT (no $_SESSION).
 */
export default function MascotaForm({ mascota, onSuccess, onCancel }: Props) {
  const { showError, showSuccess } = useNotification()
  const isEdit = !!mascota

  const [nombre, setNombre] = useState(mascota?.nombre ?? '')
  const [fechaNacimiento, setFechaNacimiento] = useState(mascota?.fechaNacimiento?.split('T')[0] ?? '')
  const [peso, setPeso] = useState(mascota?.peso?.toString() ?? '')
  const [genero, setGenero] = useState<'M' | 'F'>(mascota?.genero ?? 'M')
  const [esterilizado, setEsterilizado] = useState(mascota?.esterilizado ?? false)
  const [razaId, setRazaId] = useState<number | ''>(mascota?.razaId ?? '')
  const [razas, setRazas] = useState<RazaDTO[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    referenciaApi.getRazas().then(setRazas)
  }, [])

  const today = new Date().toISOString().split('T')[0]

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    if (!razaId) { showError('Selecciona una raza'); return }
    setLoading(true)
    try {
      const payload = {
        nombre,
        fechaNacimiento,
        peso: parseFloat(peso),
        genero,
        esterilizado,
        razaId: Number(razaId),
      }
      if (isEdit) {
        await mascotaApi.update(mascota!.perroId, payload)
        showSuccess('Mascota actualizada')
      } else {
        await mascotaApi.create(payload)
        showSuccess('Mascota registrada')
      }
      onSuccess()
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al guardar la mascota')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
      <h3 style={{ marginBottom: '8px' }}>{isEdit ? 'Editar mascota' : 'Registrar mascota'}</h3>

      <div className="input-group">
        <input className="input-field" placeholder="Nombre *" value={nombre}
          onChange={(e) => setNombre(e.target.value)} required
          pattern="^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,45}$" style={{ padding: '14px' }} />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
        <div className="input-group">
          <input type="date" className="input-field" value={fechaNacimiento}
            onChange={(e) => setFechaNacimiento(e.target.value)} required
            max={today} style={{ padding: '14px' }} />
        </div>
        <div className="input-group">
          <input type="number" className="input-field" placeholder="Peso (kg) *" value={peso}
            onChange={(e) => setPeso(e.target.value)} required
            min="0.1" max="100" step="0.1" style={{ padding: '14px' }} />
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
        <div className="input-group">
          <select className="input-field" value={razaId}
            onChange={(e) => setRazaId(Number(e.target.value))} required style={{ padding: '14px' }}>
            <option value="">Raza *</option>
            {razas.map((r) => <option key={r.razaId} value={r.razaId}>{r.nombre}</option>)}
          </select>
        </div>
        <div className="input-group">
          <select className="input-field" value={genero}
            onChange={(e) => setGenero(e.target.value as 'M' | 'F')} required style={{ padding: '14px' }}>
            <option value="M">Macho</option>
            <option value="F">Hembra</option>
          </select>
        </div>
      </div>

      <label style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
        <input type="checkbox" checked={esterilizado}
          onChange={(e) => setEsterilizado(e.target.checked)} />
        Esterilizado/a
      </label>

      <div style={{ display: 'flex', gap: '12px' }}>
        <button type="button" className="submit-btn"
          style={{ flex: 1, background: 'var(--color-hover)' }} onClick={onCancel}>
          Cancelar
        </button>
        <button type="submit" className="submit-btn" disabled={loading} style={{ flex: 2 }}>
          {loading ? <span className="spinner" /> : (isEdit ? 'Guardar cambios' : 'Registrar')}
        </button>
      </div>
    </form>
  )
}
