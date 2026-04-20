import { Component, type ReactNode } from 'react'
import { Link } from 'react-router-dom'

// ── AccessDeniedPage (403) ────────────────────────────────────────────────────
/**
 * Pantalla de acceso denegado (403).
 * AdminRoute ya maneja la redirección automática; este componente es para
 * mostrar un mensaje explícito si se necesita en algún flujo específico.
 */
export function AccessDeniedPage() {
  return (
    <div className="error-page">
      <h1>Acceso denegado</h1>
      <p>No tienes permisos para ver esta página.</p>
      <Link to="/dashboard">Volver al inicio</Link>
    </div>
  )
}

// ── ErrorBoundary ─────────────────────────────────────────────────────────────
interface BoundaryProps {
  children: ReactNode
}

interface BoundaryState {
  hasError: boolean
}

/**
 * Error Boundary de React que captura errores no controlados en el árbol de
 * componentes y muestra una UI de fallback en lugar de una pantalla en blanco.
 */
export default class ApiErrorBoundary extends Component<BoundaryProps, BoundaryState> {
  constructor(props: BoundaryProps) {
    super(props)
    this.state = { hasError: false }
  }

  static getDerivedStateFromError(): BoundaryState {
    return { hasError: true }
  }

  componentDidCatch(error: Error) {
    console.error('[ErrorBoundary] Error no controlado:', error)
  }

  handleReload = () => {
    this.setState({ hasError: false })
    window.location.reload()
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-page">
          <h1>Algo salió mal</h1>
          <p>Ocurrió un error inesperado. Puedes recargar la página o volver al inicio.</p>
          <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', marginTop: '20px' }}>
            <button className="auth-btn" onClick={this.handleReload}>
              Recargar página
            </button>
            <a className="auth-btn" href="/" style={{ textDecoration: 'none' }}>
              Ir al inicio
            </a>
          </div>
        </div>
      )
    }
    return this.props.children
  }
}
