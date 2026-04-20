import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from './AuthContext'

/**
 * Protege rutas exclusivas para ADMIN.
 * Sin token → /login. Con token pero rol USER → /dashboard.
 * Reemplaza la verificación PHP: $_SESSION['user_role'] !== 'admin'.
 */
export default function AdminRoute() {
  const { isAuthenticated, isAdmin } = useAuth()
  if (!isAuthenticated) return <Navigate to="/login" replace />
  if (!isAdmin) return <Navigate to="/dashboard" replace />
  return <Outlet />
}
