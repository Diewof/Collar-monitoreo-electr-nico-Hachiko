import { useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { useNotification } from '../shared/Notification'
import { authApi } from '../api/authApi'
import type { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'
import '../styles/auth.css'

const RESEND_COOLDOWN = 60 // segundos

export default function ForgotPasswordPage() {
  const { showSuccess, showError } = useNotification()

  const [email, setEmail]               = useState('')
  const [emailTouched, setEmailTouched] = useState(false)
  const [loading, setLoading]           = useState(false)
  const [sent, setSent]                 = useState(false)
  const [cooldown, setCooldown]         = useState(0)

  const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  const emailState = emailTouched ? (emailValid ? 'valid' : 'invalid') : ''

  const startCooldown = () => {
    setCooldown(RESEND_COOLDOWN)
    const interval = setInterval(() => {
      setCooldown((c) => {
        if (c <= 1) { clearInterval(interval); return 0 }
        return c - 1
      })
    }, 1000)
  }

  const doSend = async () => {
    setLoading(true)
    try {
      await authApi.forgotPassword({ email })
      setSent(true)
      startCooldown()
      showSuccess('Instrucciones enviadas a tu correo.')
    } catch (err) {
      const error = err as AxiosError<ApiErrorResponse>
      showError(error.response?.data?.message ?? 'Error al procesar la solicitud')
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault()
    setEmailTouched(true)
    if (!emailValid) return
    doSend()
  }

  const handleResend = () => {
    if (cooldown > 0) return
    doSend()
  }

  /* ── Estado éxito ─────────────────────────────────────────────────────── */
  if (sent) {
    return (
      <div className="auth-page">
        <div className="auth-container">
          <div className="auth-card">
            <div className="auth-success">
              <div className="auth-success-icon">✓</div>
              <h3>Revisa tu correo</h3>
              <p>
                Enviamos las instrucciones a<br />
                <strong>{email}</strong>
              </p>
              <p>El enlace expira en <strong>1 hora</strong>. Si no lo ves, revisa la carpeta de spam.</p>

              <Link to="/login" className="auth-btn" style={{ textDecoration: 'none', textAlign: 'center' }}>
                Volver al inicio de sesión
              </Link>

              <div className="auth-link-row" style={{ marginTop: '8px' }}>
                {cooldown > 0 ? (
                  <button className="auth-resend-btn" disabled>
                    Reenviar en {cooldown}s
                  </button>
                ) : (
                  <button className="auth-resend-btn" onClick={handleResend} disabled={loading}>
                    {loading ? 'Enviando…' : '¿No llegó? Reenviar'}
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    )
  }

  /* ── Estado idle ──────────────────────────────────────────────────────── */
  return (
    <div className="auth-page">
      <div className="auth-container">
        <form className="auth-card" onSubmit={handleSubmit}>
          <div className="auth-card-header">
            <div className="auth-icon">
              <img src="/icons/email.avif" alt="" width={26} height={26} />
            </div>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p>
              Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
              El enlace expira en 1 hora.
            </p>
          </div>

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

          <button type="submit" className="auth-btn" disabled={loading}>
            {loading ? <span className="spinner" /> : 'Enviar enlace de recuperación'}
          </button>

          <div className="auth-link-row">
            <Link to="/login">← Volver al inicio de sesión</Link>
          </div>
        </form>
      </div>
    </div>
  )
}
