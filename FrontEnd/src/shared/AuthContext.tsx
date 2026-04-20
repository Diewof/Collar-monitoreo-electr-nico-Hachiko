import { createContext, useContext, useReducer, useEffect, type ReactNode } from 'react'
import { TOKEN_KEY, USER_KEY } from '../api/client'
import type { LoginResponse } from '../types/api'

// ── State ─────────────────────────────────────────────────────────────────────
interface AuthUser {
  userId: number
  email: string
  role: 'ADMIN' | 'USER'
}

interface AuthState {
  token: string | null
  user: AuthUser | null
}

type AuthAction =
  | { type: 'LOGIN'; payload: LoginResponse }
  | { type: 'LOGOUT' }

function authReducer(state: AuthState, action: AuthAction): AuthState {
  switch (action.type) {
    case 'LOGIN': {
      const { token, userId, email, role } = action.payload
      const user: AuthUser = { userId, email, role }
      localStorage.setItem(TOKEN_KEY, token)
      localStorage.setItem(USER_KEY, JSON.stringify(user))
      return { token, user }
    }
    case 'LOGOUT':
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
      return { token: null, user: null }
    default:
      return state
  }
}

function loadInitialState(): AuthState {
  const token = localStorage.getItem(TOKEN_KEY)
  const raw = localStorage.getItem(USER_KEY)
  if (token && raw) {
    try {
      const user: AuthUser = JSON.parse(raw)
      return { token, user }
    } catch {
      // datos corruptos
    }
  }
  return { token: null, user: null }
}

// ── Context ───────────────────────────────────────────────────────────────────
interface AuthContextValue extends AuthState {
  login: (response: LoginResponse) => void
  logout: () => void
  isAuthenticated: boolean
  isAdmin: boolean
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [state, dispatch] = useReducer(authReducer, undefined, loadInitialState)

  // Si el token se elimina de otra pestaña, cerrar sesión también aquí
  useEffect(() => {
    const handleStorage = (e: StorageEvent) => {
      if (e.key === TOKEN_KEY && !e.newValue) {
        dispatch({ type: 'LOGOUT' })
      }
    }
    window.addEventListener('storage', handleStorage)
    return () => window.removeEventListener('storage', handleStorage)
  }, [])

  // Escucha el evento global que dispara el interceptor axios cuando recibe 401
  useEffect(() => {
    const handleUnauthorized = () => dispatch({ type: 'LOGOUT' })
    window.addEventListener('hachiko:unauthorized', handleUnauthorized)
    return () => window.removeEventListener('hachiko:unauthorized', handleUnauthorized)
  }, [])

  const login = (response: LoginResponse) => dispatch({ type: 'LOGIN', payload: response })
  const logout = () => dispatch({ type: 'LOGOUT' })

  return (
    <AuthContext.Provider
      value={{
        ...state,
        login,
        logout,
        isAuthenticated: state.token !== null,
        isAdmin: state.user?.role === 'ADMIN',
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth debe usarse dentro de <AuthProvider>')
  return ctx
}
