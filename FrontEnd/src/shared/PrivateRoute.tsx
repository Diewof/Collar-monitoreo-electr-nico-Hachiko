import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from './AuthContext'

/**
 * Protege rutas que requieren un token JWT válido.
 * Sin token → redirige a /login (reemplaza la verificación de $_SESSION['is_logged_in']).
 */
export default function PrivateRoute() {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace />
}
