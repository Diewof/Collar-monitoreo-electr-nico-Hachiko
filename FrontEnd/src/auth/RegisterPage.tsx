import { useState, type FormEvent } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useNotification } from '../shared/Notification'
import { authApi } from '../api/authApi'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/auth.css'

export default function RegisterPage() {
  const { showError, showSuccess } = useNotification()
  const navigate = useNavigate()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [showPwd, setShowPwd] = useState(false)
  const [loading, setLoading] = useState(false)

  const [emailTouched, setEmailTouched] = useState(false)
  const [confirmTouched, setConfirmTouched] = useState(false)

  const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  const pwdMatch   = password === confirmPassword && confirmPassword.length > 0

  const emailState   = emailTouched   ? (emailValid ? 'valid' : 'invalid') : ''
  const confirmState = confirmTouched ? (pwdMatch   ? 'valid' : 'invalid') : ''

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setEmailTouched(true)
    setConfirmTouched(true)
    if (!emailValid || !pwdMatch) return
    setLoading(true)
    try {
      await authApi.register({ email, password, confirmPassword })
      showSuccess('Cuenta creada. Inicia sesión.')
      navigate('/login')
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      const errors = error.response?.data?.errors
      if (errors && errors.length > 0) {
        showError(errors.map((e) => e.message).join(' · '))
      } else {
        showError(error.response?.data?.message ?? 'Error al registrarse')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-page">
      <div className="auth-container">
        <div className="auth-tabs">
          <Link to="/login" className="auth-tab">Iniciar Sesión</Link>
          <button className="auth-tab active">Registrarse</button>
        </div>

        <form className="auth-card" onSubmit={handleSubmit}>
          <div className="auth-card-header">
            <div className="auth-icon">
              <img src="/icons/register.avif" alt="" width={26} height={26} />
            </div>
            <h2>Crear Cuenta</h2>
          </div>

          {/* Email */}
          <div className="input-group">
            <img src="/icons/email.avif" alt="" className="input-icon" width={18} height={18} />
            <input
              type="email"
              className={`auth-input ${emailState}`}
              placeholder="Correo electrónico"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              onBlur={() => setEmailTouched(true)}
              required
            />
            {emailTouched && (
              <span className={`validation-message ${emailState}`}>
                {emailValid ? 'Formato válido' : 'Ingresa un email válido'}
              </span>
            )}
          </div>

          {/* Contraseña */}
          <div className="input-group">
            <img src="/icons/password.avif" alt="" className="input-icon" width={18} height={18} />
            <input
              type={showPwd ? 'text' : 'password'}
              className="auth-input"
              placeholder="Contraseña"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
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

          {/* Confirmar contraseña */}
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
            />
            {confirmTouched && (
              <span className={`validation-message ${confirmState}`}>
                {pwdMatch ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden'}
              </span>
            )}
          </div>

          <button type="submit" className="auth-btn" disabled={loading}>
            {loading ? <span className="spinner" /> : 'Crear cuenta'}
          </button>

          <div className="auth-link-row">
            <Link to="/login">¿Ya tienes cuenta? Inicia sesión</Link>
          </div>
        </form>
      </div>
    </div>
  )
}
