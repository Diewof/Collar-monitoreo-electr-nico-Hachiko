package com.hachiko.portal.service;

import com.hachiko.portal.dto.mascota.CreateMascotaRequest;
import com.hachiko.portal.dto.mascota.MascotaDTO;
import com.hachiko.portal.dto.mascota.UpdateMascotaRequest;

import java.util.List;

/**
 * Contrato para la gestión de mascotas (perros).
 * Módulo: Mascota.
 *
 * Todos los métodos de escritura verifican que la mascota pertenece al
 * propietario indicado antes de operar (control de acceso por propiedad).
 *
 * Principio SRP: gestiona únicamente operaciones sobre mascotas.
 * La verificación de pertenencia es responsabilidad de este servicio,
 * no del validador (que solo valida datos de entrada).
 */
public interface IMascotaService {

    /**
     * Lista todas las mascotas de un propietario, ordenadas por nombre.
     *
     * @param propietarioId ID del propietario
     * @return lista de MascotaDTO, vacía si no tiene mascotas registradas
     */
    List<MascotaDTO> listByPropietario(Integer propietarioId);

    /**
     * Retorna una mascota por su ID, verificando que pertenece al propietario.
     *
     * @param perroId       ID de la mascota
     * @param propietarioId ID del propietario (para verificación de propiedad)
     * @return MascotaDTO
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si la mascota no existe
     * @throws com.hachiko.portal.exception.AccessDeniedException si no pertenece al propietario
     */
    MascotaDTO getById(Integer perroId, Integer propietarioId);

    /**
     * Registra una nueva mascota para un propietario.
     *
     * @param request datos de la mascota incluyendo propietarioId y razaId
     * @return MascotaDTO de la mascota creada
     * @throws com.hachiko.portal.exception.ValidationException si validación falla
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si propietario o raza no existen
     */
    MascotaDTO create(CreateMascotaRequest request);

    /**
     * Actualiza una mascota existente, verificando propiedad.
     *
     * @param request datos actualizados incluyendo perroId y propietarioId
     * @return MascotaDTO actualizado
     * @throws com.hachiko.portal.exception.ValidationException si validación falla
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si la mascota no existe
     * @throws com.hachiko.portal.exception.AccessDeniedException si no pertenece al propietario
     */
    MascotaDTO update(UpdateMascotaRequest request);

    /**
     * Elimina una mascota, verificando que pertenece al propietario.
     *
     * @param perroId       ID de la mascota a eliminar
     * @param propietarioId ID del propietario (verificación de propiedad)
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si la mascota no existe
     * @throws com.hachiko.portal.exception.AccessDeniedException si no pertenece al propietario
     */
    void delete(Integer perroId, Integer propietarioId);
}
