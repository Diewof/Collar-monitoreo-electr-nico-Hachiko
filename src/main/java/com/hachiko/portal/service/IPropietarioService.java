package com.hachiko.portal.service;

import com.hachiko.portal.dto.propietario.CreatePropietarioRequest;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.propietario.UpdatePropietarioRequest;

/**
 * Contrato para la gestión del perfil de propietario.
 * Módulo: Propietario.
 *
 * Las operaciones de escritura crean/actualizan Propietario y Residencia
 * en una sola transacción. El CascadeType.ALL en Propietario.residencia
 * garantiza que la Residencia se persiste junto al Propietario.
 *
 * Principio SRP: gestiona únicamente el perfil de propietario.
 * La lógica de mascotas pertenece a IMascotaService.
 */
public interface IPropietarioService {

    /**
     * Retorna el perfil completo de propietario para un usuario.
     * Incluye residencia con jerarquía de ubicación completa (ciudad → dpto → país).
     *
     * @param userId ID del usuario autenticado
     * @return PropietarioDTO con ResidenciaDTO anidada
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si no existe perfil
     */
    PropietarioDTO getByUserId(Integer userId);

    /**
     * Crea el perfil de propietario por primera vez para un usuario.
     * La Residencia se crea en cascada a través de Propietario.residencia.
     *
     * @param request datos del perfil y residencia incluyendo userId
     * @return PropietarioDTO del perfil recién creado
     * @throws com.hachiko.portal.exception.ValidationException si validación falla
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si usuario/ciudad/plan no existen
     * @throws com.hachiko.portal.exception.DuplicateResourceException si el usuario ya tiene perfil
     */
    PropietarioDTO create(CreatePropietarioRequest request);

    /**
     * Actualiza el perfil de propietario existente.
     * La Residencia se actualiza en cascada.
     *
     * @param request datos actualizados incluyendo propietarioId
     * @return PropietarioDTO actualizado
     * @throws com.hachiko.portal.exception.ValidationException si validación falla
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si propietario/ciudad/plan no existen
     */
    PropietarioDTO update(UpdatePropietarioRequest request);
}
