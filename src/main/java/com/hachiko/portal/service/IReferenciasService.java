package com.hachiko.portal.service;

import com.hachiko.portal.dto.referencia.CiudadDTO;
import com.hachiko.portal.dto.referencia.DepartamentoDTO;
import com.hachiko.portal.dto.referencia.PaisDTO;
import com.hachiko.portal.dto.referencia.PlanDTO;
import com.hachiko.portal.dto.referencia.RazaDTO;

import java.util.List;

/**
 * Contrato para la consulta de datos de referencia (catálogos de solo lectura).
 * Módulo: Ubicación, Mascota, Propietario.
 *
 * Migra los métodos getPaises(), getDepartamentos(), getCiudades(),
 * getPlanes() y getRazas() de PropietarioModel.php y MascotaModel.php.
 *
 * Principio SRP: provee únicamente datos de catálogo; no persiste ni valida.
 * Candidato a caché: todos estos datos cambian con frecuencia cercana a cero.
 */
public interface IReferenciasService {

    /**
     * Retorna todos los países ordenados alfabéticamente.
     * Usado para poblar el selector de país en el formulario de perfil de propietario.
     *
     * @return lista de PaisDTO
     */
    List<PaisDTO> getPaises();

    /**
     * Retorna los departamentos de un país, ordenados alfabéticamente.
     * Invocado en cascada cuando el usuario selecciona un país.
     *
     * @param paisId ID del país
     * @return lista de DepartamentoDTO
     */
    List<DepartamentoDTO> getDepartamentosByPais(Integer paisId);

    /**
     * Retorna las ciudades de un departamento, ordenadas alfabéticamente.
     * Invocado en cascada cuando el usuario selecciona un departamento.
     *
     * @param departamentoId ID del departamento
     * @return lista de CiudadDTO
     */
    List<CiudadDTO> getCiudadesByDepartamento(Integer departamentoId);

    /**
     * Retorna todas las razas de perro ordenadas alfabéticamente.
     * Usado para poblar el selector de raza en el formulario de mascota.
     *
     * @return lista de RazaDTO
     */
    List<RazaDTO> getRazas();

    /**
     * Retorna todos los planes de suscripción ordenados por costo ascendente.
     * Usado para poblar el selector de plan en el formulario de propietario.
     *
     * @return lista de PlanDTO
     */
    List<PlanDTO> getPlanes();
}
