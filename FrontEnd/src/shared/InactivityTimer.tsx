import { useEffect, useRef, useState } from 'react'

const INACTIVITY_MS = 15 * 60 * 1000 // 15 minutos (igual que main.js original)

/**
 * Timer visual de inactividad — solo decorativo.
 * La seguridad real la garantiza el JWT: cualquier llamada con token expirado
 * retorna 401 y el interceptor de Axios cierra la sesión automáticamente.
 * Reemplaza el setTimeout de main.js que redirigía a auto_logout.php.
 */
export default function InactivityTimer() {
  const [remaining, setRemaining] = useState(INACTIVITY_MS)
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const lastActivityRef = useRef(Date.now())

  const reset = () => {
    lastActivityRef.current = Date.now()
    setRemaining(INACTIVITY_MS)
  }

  useEffect(() => {
    const events = ['mousemove', 'keypress', 'click', 'scroll']
    events.forEach((e) => window.addEventListener(e, reset))

    intervalRef.current = setInterval(() => {
      const elapsed = Date.now() - lastActivityRef.current
      const left = Math.max(0, INACTIVITY_MS - elapsed)
      setRemaining(left)
    }, 1000)

    return () => {
      events.forEach((e) => window.removeEventListener(e, reset))
      if (timerRef.current) clearTimeout(timerRef.current)
      if (intervalRef.current) clearInterval(intervalRef.current)
    }
  }, [])

  const minutes = Math.floor(remaining / 60000)
  const seconds = Math.floor((remaining % 60000) / 1000)
  const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`

  return (
    <span id="timer-countdown" className="inactivity-timer" title="Tiempo hasta cierre de sesión por inactividad">
      {display}
    </span>
  )
}
