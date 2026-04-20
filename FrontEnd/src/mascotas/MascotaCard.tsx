import type { MascotaDTO } from '../types/api'

interface Props {
  mascota: MascotaDTO
  onEdit: (mascota: MascotaDTO) => void
  onDelete: (perroId: number) => void
}

/**
 * Tarjeta de mascota — reemplaza perfil_mascota_modal.php.
 * No consulta la BD directamente; recibe los datos ya obtenidos via GET /api/mascotas.
 */
export default function MascotaCard({ mascota, onEdit, onDelete }: Props) {
  const generoLabel = mascota.genero === 'M' ? 'Macho' : 'Hembra'
  const esterilizadoLabel = mascota.esterilizado ? 'Sí' : 'No'

  const handleDelete = () => {
    if (window.confirm(`¿Eliminar a ${mascota.nombre}? Esta acción no se puede deshacer.`)) {
      onDelete(mascota.perroId)
    }
  }

  return (
    <div className="card" style={{ position: 'relative' }}>
      <div className="card-icon">
        <img src="/icons/dog-happy.avif" alt={mascota.nombre} width={40} height={40} />
      </div>
      <div className="card-content">
        <h3>{mascota.nombre}</h3>
        <p><strong>Raza:</strong> {mascota.nombreRaza}</p>
        <p><strong>Género:</strong> {generoLabel}</p>
        <p><strong>Peso:</strong> {mascota.peso} kg</p>
        <p><strong>Nacimiento:</strong> {new Date(mascota.fechaNacimiento).toLocaleDateString('es-CO')}</p>
        <p><strong>Esterilizado:</strong> {esterilizadoLabel}</p>
      </div>
      <div style={{ display: 'flex', gap: '8px', marginTop: '12px' }}>
        <button
          className="submit-btn"
          style={{ flex: 1, padding: '8px', fontSize: '13px' }}
          onClick={() => onEdit(mascota)}
        >
          Editar
        </button>
        <button
          className="submit-btn"
          style={{ flex: 1, padding: '8px', fontSize: '13px', background: '#dc3545' }}
          onClick={handleDelete}
        >
          Eliminar
        </button>
      </div>
    </div>
  )
}
