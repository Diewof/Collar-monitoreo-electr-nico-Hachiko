import axios, { AxiosError } from 'axios'
import type { ApiErrorResponse } from '../types/api'

export const TOKEN_KEY = 'hachiko_token'
export const USER_KEY = 'hachiko_user'

const client = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json' },
})

// ── Request interceptor: adjunta Bearer token ─────────────────────────────────
client.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})

// ── Response interceptor: manejo centralizado de errores ─────────────────────
client.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiErrorResponse>) => {
    const status = error.response?.status

    if (status === 401) {
      // Token expirado o inválido → notificar al AuthContext para que limpie estado y storage
      window.dispatchEvent(new CustomEvent('hachiko:unauthorized'))
      return Promise.reject(error)
    }

    if (status === 403) {
      // Acceso denegado → redirigir al dashboard (no al admin)
      window.location.href = '/dashboard'
      return Promise.reject(error)
    }

    // 422 y otros: propagar el error para que cada módulo lo maneje
    return Promise.reject(error)
  }
)

export default client
