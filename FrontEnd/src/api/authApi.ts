import client from './client'
import type {
  LoginRequest,
  LoginResponse,
  RegisterRequest,
  UsuarioDTO,
  PasswordResetRequest,
  NewPasswordRequest,
} from '../types/api'

export const authApi = {
  login: (data: LoginRequest) =>
    client.post<LoginResponse>('/auth/login', data).then((r) => r.data),

  register: (data: RegisterRequest) =>
    client.post<UsuarioDTO>('/auth/register', data).then((r) => r.data),

  logout: () =>
    client.post<{ message: string }>('/auth/logout').then((r) => r.data),

  forgotPassword: (data: PasswordResetRequest) =>
    client.post<{ message: string }>('/auth/forgot-password', data).then((r) => r.data),

  resetPassword: (data: NewPasswordRequest) =>
    client.post<{ message: string }>('/auth/reset-password', data).then((r) => r.data),
}
