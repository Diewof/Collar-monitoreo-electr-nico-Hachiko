import client from './client'
import type {
  PropietarioDTO,
  CreatePropietarioRequest,
  UpdatePropietarioRequest,
} from '../types/api'

export const propietarioApi = {
  getMe: () =>
    client.get<PropietarioDTO>('/propietario/me').then((r) => r.data),

  create: (data: CreatePropietarioRequest) =>
    client.post<PropietarioDTO>('/propietario', data).then((r) => r.data),

  update: (propietarioId: number, data: UpdatePropietarioRequest) =>
    client.put<PropietarioDTO>(`/propietario/${propietarioId}`, data).then((r) => r.data),
}
