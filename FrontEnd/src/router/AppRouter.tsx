import { Routes, Route } from 'react-router-dom'
import NotFoundPage from '../shared/NotFoundPage'
import PrivateRoute from '../shared/PrivateRoute'
import AdminRoute from '../shared/AdminRoute'

// Páginas públicas
import LandingPage from '../landing/LandingPage'
import LoginPage from '../auth/LoginPage'
import RegisterPage from '../auth/RegisterPage'
import ForgotPasswordPage from '../auth/ForgotPasswordPage'
import ResetPasswordPage from '../auth/ResetPasswordPage'

// Páginas protegidas (USER)
import CompletarPerfilPage from '../propietario/CompletarPerfilPage'
import DashboardPage from '../dashboard/DashboardPage'
import EditarPerfilPage from '../propietario/EditarPerfilPage'
import MascotasPage from '../mascotas/MascotasPage'

// Páginas protegidas (ADMIN)
import AdminDashboardPage from '../admin/AdminDashboardPage'

export default function AppRouter() {
  return (
    <Routes>
      {/* ── Públicas ─────────────────────────────────────────────────────── */}
      <Route path="/" element={<LandingPage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/registro" element={<RegisterPage />} />
      <Route path="/recuperar-password" element={<ForgotPasswordPage />} />
      <Route path="/reset-password" element={<ResetPasswordPage />} />

      {/* ── Autenticadas (cualquier rol) ──────────────────────────────────── */}
      <Route element={<PrivateRoute />}>
        <Route path="/completar-perfil" element={<CompletarPerfilPage />} />
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/perfil" element={<EditarPerfilPage />} />
        <Route path="/mascotas" element={<MascotasPage />} />
      </Route>

      {/* ── Solo ADMIN ───────────────────────────────────────────────────── */}
      <Route element={<AdminRoute />}>
        <Route path="/admin" element={<AdminDashboardPage />} />
      </Route>

      {/* ── Fallback ─────────────────────────────────────────────────────── */}
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  )
}
