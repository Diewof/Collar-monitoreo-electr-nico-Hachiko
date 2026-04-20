import { useState, type FormEvent } from 'react'
import { adminApi } from '../api/adminApi'
import { useNotification } from '../shared/Notification'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'

interface Props {
  onSuccess: () => void
  onCancel: () => void
}

/**
 * Formulario para crear un usuario desde el panel admin.
 * Reemplaza la sección 'add_user' de admin_main.php.
 * Validación de formato en el cliente; la fuente de verdad es el backend (422).
 * No duplica las reglas de negocio de UserValidator.java.
 */
export default function UsuarioForm({ onSuccess, onCancel }: Props) {
  const { showError, showSuccess } = useNotification()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [loading, setLoading] = useState(false)

  const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  const pwdMatch = password === confirmPassword && confirmPassword.length > 0

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    if (!pwdMatch) { showError('Las contraseñas no coinciden'); return }
    setLoading(true)
    try {
      await adminApi.createUsuario({ email, password, confirmPassword })
      showSuccess('Usuario creado correctamente')
      onSuccess()
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al crear el usuario')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <form className="admin-form" onSubmit={handleSubmit}>
      <h3 style={{ marginBottom: '20px' }}>Crear Usuario</h3>

      <div className="form-group">
        <label>Correo electrónico *</label>
        <input
          type="email"
          className={`form-control ${email ? (emailValid ? 'valid' : 'error') : ''}`}
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          placeholder="usuario@ejemplo.com"
        />
      </div>

      <div className="form-group">
        <label>Contraseña *</label>
        <input
          type="password"
          className="form-control"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          placeholder="Mínimo 8 caracteres"
        />
        <div className="password-strength-meter">
          <div className={`strength-bar ${
            password.length === 0 ? '' :
            password.length < 8 ? 'weak' :
            /[A-Z]/.test(password) && /[0-9]/.test(password) ? 'strong' : 'medium'
          }`} />
        </div>
      </div>

      <div className="form-group">
        <label>Confirmar contraseña *</label>
        <input
          type="password"
          className={`form-control ${confirmPassword ? (pwdMatch ? 'valid' : 'error') : ''}`}
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          required
          placeholder="Repite la contraseña"
        />
        {confirmPassword && !pwdMatch && (
          <div className="error-message show">
            <span className="error-icon">✗</span>
            <span className="error-text">Las contraseñas no coinciden</span>
          </div>
        )}
      </div>

      <div className="form-actions">
        <button type="button" className="btn btn-secondary" onClick={onCancel}>Cancelar</button>
        <button type="submit" className="btn btn-primary" disabled={loading || !pwdMatch}>
          {loading ? <span className="spinner" /> : 'Crear usuario'}
        </button>
      </div>
    </form>
  )
}
