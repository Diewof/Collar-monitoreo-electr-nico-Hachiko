import { useState, type FormEvent } from 'react'
import { useNavigate, useSearchParams, Link } from 'react-router-dom'
import { useNotification } from '../shared/Notification'
import { authApi } from '../api/authApi'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/auth.css'

export default function ResetPasswordPage() {
  const { showSuccess, showError } = useNotification()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''

  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [showPwd, setShowPwd] = useState(false)
  const [loading, setLoading] = useState(false)
  const [confirmTouched, setConfirmTouched] = useState(false)

  const pwdMatch     = password === confirmPassword && confirmPassword.length > 0
  const confirmState = confirmTouched ? (pwdMatch ? 'valid' : 'invalid') : ''

  if (!token) {
    return (
      <div className="auth-page">
        <div className="auth-container">
          <div className="auth-card">
            <div className="auth-card-header">
              <div className="auth-icon">
                <img src="/icons/password.avif" alt="" width={26} height={26} />
              </div>
              <h2>Enlace inválido</h2>
              <p>El enlace de recuperación ha expirado o no es válido.</p>
            </div>
            <Link to="/recuperar-password" className="auth-btn" style={{ textDecoration: 'none', textAlign: 'center' }}>
              Solicitar un nuevo enlace
            </Link>
          </div>
        </div>
      </div>
    )
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setConfirmTouched(true)
    if (!pwdMatch) { showError('Las contraseñas no coinciden'); return }
    setLoading(true)
    try {
      await authApi.resetPassword({ token, password, confirmPassword })
      showSuccess('Contraseña actualizada. Inicia sesión.')
      navigate('/login')
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al restablecer la contraseña')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-page">
      <div className="auth-container">
        <form className="auth-card" onSubmit={handleSubmit}>
          <div className="auth-card-header">
            <div className="auth-icon">
              <img src="/icons/password.avif" alt="" width={26} height={26} />
            </div>
            <h2>Nueva Contraseña</h2>
            <p>Crea una contraseña segura para tu cuenta.</p>
          </div>

          <div className="input-group">
            <img src="/icons/password.avif" alt="" className="input-icon" width={18} height={18} />
            <input
              type={showPwd ? 'text' : 'password'}
              className="auth-input"
              placeholder="Nueva contraseña"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              autoComplete="new-password"
            />
            <button
              type="button"
              className="password-toggle"
              onClick={() => setShowPwd((v) => !v)}
              aria-label={showPwd ? 'Ocultar contraseña' : 'Mostrar contraseña'}
            >
              <img src={showPwd ? '/icons/close-eye.avif' : '/icons/eye.avif'} alt="" width={18} height={18} />
            </button>
          </div>

          <div className="input-group">
            <img src="/icons/lock.avif" alt="" className="input-icon" width={18} height={18} />
            <input
              type={showPwd ? 'text' : 'password'}
              className={`auth-input ${confirmState}`}
              placeholder="Confirmar contraseña"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              onBlur={() => setConfirmTouched(true)}
              required
              autoComplete="new-password"
            />
            {confirmTouched && (
              <span className={`validation-message ${confirmState}`}>
                {pwdMatch ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden'}
              </span>
            )}
          </div>

          <button type="submit" className="auth-btn" disabled={loading || !pwdMatch}>
            {loading ? <span className="spinner" /> : 'Guardar nueva contraseña'}
          </button>
        </form>
      </div>
    </div>
  )
}
