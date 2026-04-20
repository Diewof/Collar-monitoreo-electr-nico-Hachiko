import { useState, type FormEvent } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useAuth } from '../shared/AuthContext'
import { useNotification } from '../shared/Notification'
import { authApi } from '../api/authApi'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/auth.css'

export default function LoginPage() {
  const { login } = useAuth()
  const { showError } = useNotification()
  const navigate = useNavigate()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPwd, setShowPwd] = useState(false)
  const [loading, setLoading] = useState(false)
  const [emailTouched, setEmailTouched] = useState(false)

  const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  const emailState = emailTouched ? (emailValid ? 'valid' : 'invalid') : ''

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setEmailTouched(true)
    if (!emailValid) return
    setLoading(true)
    try {
      const response = await authApi.login({ email, password })
      login(response)
      if (response.role === 'ADMIN') {
        navigate('/admin')
      } else if (response.requiresProfileCompletion) {
        navigate('/completar-perfil')
      } else {
        navigate('/dashboard')
      }
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      showError(error.response?.data?.message ?? 'Credenciales inválidas')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-page">
      <div className="auth-container">
        <div className="auth-tabs">
          <button className="auth-tab active">Iniciar Sesión</button>
          <Link to="/registro" className="auth-tab">Registrarse</Link>
        </div>

        <form className="auth-card" onSubmit={handleSubmit}>
          <div className="auth-card-header">
            <div className="auth-icon">
              <img src="/icons/login.avif" alt="" width={26} height={26} />
            </div>
            <h2>Iniciar Sesión</h2>
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
              autoComplete="email"
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
              autoComplete="current-password"
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

          <button type="submit" className="auth-btn" disabled={loading}>
            {loading ? <span className="spinner" /> : 'Iniciar Sesión'}
          </button>

          <div className="auth-link-row">
            <Link to="/recuperar-password">¿Olvidaste tu contraseña?</Link>
          </div>
        </form>
      </div>
    </div>
  )
}
