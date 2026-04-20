import client from './client'
import type { MascotaDTO, CreateMascotaRequest, UpdateMascotaRequest } from '../types/api'

export const mascotaApi = {
  list: () =>
    client.get<MascotaDTO[]>('/mascotas').then((r) => r.data),

  getOne: (perroId: number) =>
    client.get<MascotaDTO>(`/mascotas/${perroId}`).then((r) => r.data),

  create: (data: CreateMascotaRequest) =>
    client.post<MascotaDTO>('/mascotas', data).then((r) => r.data),

  update: (perroId: number, data: UpdateMascotaRequest) =>
    client.put<MascotaDTO>(`/mascotas/${perroId}`, data).then((r) => r.data),

  delete: (perroId: number) =>
    client.delete(`/mascotas/${perroId}`),
}
