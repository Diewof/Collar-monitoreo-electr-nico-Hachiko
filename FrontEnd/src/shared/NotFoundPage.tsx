import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'

/**
 * Página 404 — se muestra cuando el usuario navega a una ruta inexistente.
 * Redirige automáticamente a "/" tras 5 segundos.
 */
export default function NotFoundPage() {
  const navigate = useNavigate()
  const [countdown, setCountdown] = useState(5)

  useEffect(() => {
    if (countdown <= 0) {
      navigate('/', { replace: true })
      return
    }
    const timer = setTimeout(() => setCountdown((c) => c - 1), 1000)
    return () => clearTimeout(timer)
  }, [countdown, navigate])

  return (
    <div className="error-page">
      <h1>404 — Página no encontrada</h1>
      <p>La ruta que buscas no existe.</p>
      <p style={{ fontSize: '14px', color: 'var(--text-muted, #888)' }}>
        Serás redirigido al inicio en {countdown} segundo{countdown !== 1 ? 's' : ''}…
      </p>
      <button className="auth-btn" onClick={() => navigate('/', { replace: true })}>
        Ir al inicio ahora
      </button>
    </div>
  )
}
