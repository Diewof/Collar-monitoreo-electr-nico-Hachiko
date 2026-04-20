import client from './client'
import type {
  DashboardStatsDTO,
  UsuarioDTO,
  UserDetailDTO,
  RegisterRequest,
} from '../types/api'

export const adminApi = {
  getStats: () =>
    client.get<DashboardStatsDTO>('/admin/stats').then((r) => r.data),

  listUsuarios: () =>
    client.get<UsuarioDTO[]>('/admin/usuarios').then((r) => r.data),

  getUsuario: (userId: number) =>
    client.get<UserDetailDTO>(`/admin/usuarios/${userId}`).then((r) => r.data),

  createUsuario: (data: RegisterRequest) =>
    client.post<UsuarioDTO>('/admin/usuarios', data).then((r) => r.data),

  updateRole: (userId: number, role: 'ADMIN' | 'USER') =>
    client.put<{ message: string }>(`/admin/usuarios/${userId}/role`, { role }).then((r) => r.data),

  deleteUsuario: (userId: number) =>
    client.delete(`/admin/usuarios/${userId}`),
}
