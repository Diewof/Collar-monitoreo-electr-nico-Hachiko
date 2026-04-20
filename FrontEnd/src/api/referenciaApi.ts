import client from './client'
import type { PaisDTO, DepartamentoDTO, CiudadDTO, RazaDTO, PlanDTO } from '../types/api'

export const referenciaApi = {
  getPaises: () =>
    client.get<PaisDTO[]>('/referencia/paises').then((r) => r.data),

  getDepartamentos: (paisId: number) =>
    client.get<DepartamentoDTO[]>('/referencia/departamentos', { params: { paisId } }).then((r) => r.data),

  getCiudades: (departamentoId: number) =>
    client.get<CiudadDTO[]>('/referencia/ciudades', { params: { departamentoId } }).then((r) => r.data),

  getRazas: () =>
    client.get<RazaDTO[]>('/referencia/razas').then((r) => r.data),

  getPlanes: () =>
    client.get<PlanDTO[]>('/referencia/planes').then((r) => r.data),
}
