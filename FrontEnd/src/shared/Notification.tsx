import { createContext, useContext, useState, useCallback, type ReactNode } from 'react'

// ── Tipos ─────────────────────────────────────────────────────────────────────
type NotifType = 'success' | 'error' | 'info'

interface NotifMessage {
  id: number
  type: NotifType
  text: string
}

interface NotifContextValue {
  showSuccess: (text: string) => void
  showError: (text: string) => void
  showInfo: (text: string) => void
}

const NotifContext = createContext<NotifContextValue | null>(null)

// ── Provider ──────────────────────────────────────────────────────────────────
export function NotificationProvider({ children }: { children: ReactNode }) {
  const [messages, setMessages] = useState<NotifMessage[]>([])
  let counter = 0

  const remove = useCallback((id: number) => {
    setMessages((prev) => prev.filter((m) => m.id !== id))
  }, [])

  const add = useCallback((type: NotifType, text: string) => {
    const id = ++counter
    setMessages((prev) => [...prev, { id, type, text }])
    setTimeout(() => {
      setMessages((prev) => prev.filter((m) => m.id !== id))
    }, 6000)
  }, [])

  return (
    <NotifContext.Provider
      value={{
        showSuccess: (t) => add('success', t),
        showError: (t) => add('error', t),
        showInfo: (t) => add('info', t),
      }}
    >
      {children}
      <div className="notification-container">
        {messages.map((m) => (
          <div key={m.id} className={`notification notification--${m.type}`}>
            <span className="notification__text">{m.text}</span>
            <button
              className="notification__close"
              onClick={() => remove(m.id)}
              aria-label="Cerrar notificación"
            >
              ×
            </button>
          </div>
        ))}
      </div>
    </NotifContext.Provider>
  )
}

export function useNotification(): NotifContextValue {
  const ctx = useContext(NotifContext)
  if (!ctx) throw new Error('useNotification debe usarse dentro de <NotificationProvider>')
  return ctx
}

/**
 * Componente de conveniencia: ya integrado en App.tsx usando NotificationProvider.
 * Se exporta también como default para importar donde se necesite sin Provider anidado.
 */
export default function Notification() {
  return null // El provider real está en App.tsx — este export es por compatibilidad
}
