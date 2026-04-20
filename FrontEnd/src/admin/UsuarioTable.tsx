import { useState } from 'react'
import { adminApi } from '../api/adminApi'
import { useNotification } from '../shared/Notification'
import { roleLabel } from '../shared/utils'
import type { UsuarioDTO } from '../types/api'

interface Props {
  usuarios: UsuarioDTO[]
  onRefresh: () => void
}

/**
 * Tabla de usuarios para el panel admin.
 * Reemplaza la tabla PHP de admin_main.php + edit_user_modal.php.
 * Cambio de rol: PUT /api/admin/usuarios/{id}/role — confirma antes de aplicar.
 * Eliminar: DELETE /api/admin/usuarios/{id} — siempre pide confirmación.
 */
export default function UsuarioTable({ usuarios, onRefresh }: Props) {
  const { showSuccess, showError } = useNotification()
  const [filterText, setFilterText] = useState('')

  const filteredUsuarios = usuarios.filter(
    (u) =>
      u.email.toLowerCase().includes(filterText.toLowerCase()) ||
      u.role.toLowerCase().includes(filterText.toLowerCase())
  )

  const handleRoleChange = async (userId: number, newRole: 'ADMIN' | 'USER') => {
    if (!window.confirm(`¿Cambiar el rol a ${roleLabel(newRole)}? Esto afecta el acceso del usuario.`)) {
      return
    }
    try {
      await adminApi.updateRole(userId, newRole)
      showSuccess('Rol actualizado')
      onRefresh()
    } catch {
      showError('Error al actualizar el rol')
    }
  }

  const handleDelete = async (usuario: UsuarioDTO) => {
    if (!window.confirm(`¿Eliminar permanentemente a ${usuario.email}? Esta acción no se puede deshacer.`)) return
    try {
      await adminApi.deleteUsuario(usuario.id)
      showSuccess('Usuario eliminado')
      onRefresh()
    } catch {
      showError('Error al eliminar el usuario')
    }
  }

  return (
    <div>
      <div style={{ marginBottom: '16px' }}>
        <input
          className="form-control"
          placeholder="Buscar por email o rol..."
          value={filterText}
          onChange={(e) => setFilterText(e.target.value)}
          style={{ maxWidth: '360px' }}
        />
      </div>

      <div className="table-container">
        <table className="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Creado</th>
              <th>Último login</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {filteredUsuarios.map((u) => (
              <tr key={u.id}>
                <td>{u.id}</td>
                <td>{u.email}</td>
                <td>
                  <select
                    className="role-select form-control"
                    value={u.role}
                    onChange={(e) => handleRoleChange(u.id, e.target.value as 'ADMIN' | 'USER')}
                  >
                    <option value="USER">Usuario</option>
                    <option value="ADMIN">Administrador</option>
                  </select>
                </td>
                <td>{new Date(u.createdAt).toLocaleDateString('es-CO')}</td>
                <td>{u.lastLogin ? new Date(u.lastLogin).toLocaleDateString('es-CO') : '—'}</td>
                <td>
                  <div className="action-buttons">
                    <button
                      className="btn btn-delete btn-sm"
                      onClick={() => handleDelete(u)}
                    >
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            ))}
            {filteredUsuarios.length === 0 && (
              <tr>
                <td colSpan={6} style={{ textAlign: 'center', padding: '24px', color: 'var(--secondary-text)' }}>
                  No se encontraron usuarios
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
