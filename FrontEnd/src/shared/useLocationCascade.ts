import { useState, useEffect } from 'react'
import { referenciaApi } from '../api/referenciaApi'
import type { PaisDTO, DepartamentoDTO, CiudadDTO, PlanDTO } from '../types/api'

/**
 * Hook que gestiona los tres dropdowns en cascada: país → departamento → ciudad.
 * Reemplaza el fetch cascadeado de propietario_modal.php y edit_user_modal.php.
 */
export function useLocationCascade(initialPaisId?: number, initialDeptoId?: number) {
  const [paises, setPaises] = useState<PaisDTO[]>([])
  const [departamentos, setDepartamentos] = useState<DepartamentoDTO[]>([])
  const [ciudades, setCiudades] = useState<CiudadDTO[]>([])
  const [planes, setPlanes] = useState<PlanDTO[]>([])

  const [paisId, setPaisId] = useState<number | ''>(initialPaisId ?? '')
  const [departamentoId, setDepartamentoId] = useState<number | ''>(initialDeptoId ?? '')
  const [ciudadId, setCiudadId] = useState<number | ''>('')

  // Cargar países y planes al montar
  useEffect(() => {
    referenciaApi.getPaises().then(setPaises)
    referenciaApi.getPlanes().then(setPlanes)
  }, [])

  // Cargar departamentos cuando cambia el país
  useEffect(() => {
    if (paisId) {
      referenciaApi.getDepartamentos(Number(paisId)).then(setDepartamentos)
      setDepartamentoId('')
      setCiudades([])
      setCiudadId('')
    } else {
      setDepartamentos([])
      setCiudades([])
    }
  }, [paisId])

  // Cargar ciudades cuando cambia el departamento
  useEffect(() => {
    if (departamentoId) {
      referenciaApi.getCiudades(Number(departamentoId)).then(setCiudades)
      setCiudadId('')
    } else {
      setCiudades([])
    }
  }, [departamentoId])

  return {
    paises, departamentos, ciudades, planes,
    paisId, setPaisId,
    departamentoId, setDepartamentoId,
    ciudadId, setCiudadId,
  }
}
