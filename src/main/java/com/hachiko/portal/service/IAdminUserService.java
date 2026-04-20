package com.hachiko.portal.service;

import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;

import java.util.List;

/**
 * Contrato para la gestión de usuarios desde el panel de administración.
 * Módulo: Administración.
 *
 * Principio SRP: responsabilidad única — operaciones CRUD sobre usuarios.
 * Las estadísticas del dashboard pertenecen a IAdminDashboardService.
 * Principio DIP: AdminController depende de esta interfaz, no de la implementación.
 */
public interface IAdminUserService {

    /**
     * Retorna la lista de todos los usuarios registrados en el sistema.
     *
     * @return lista de UsuarioDTO, vacía si no hay usuarios
     */
    List<UsuarioDTO> getAllUsers();

    /**
     * Retorna el detalle completo de un usuario: cuenta, perfil de propietario y mascotas.
     *
     * @param userId ID del usuario
     * @return UserDetailDTO con usuario, propietario (nullable) y cantidadMascotas
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    UserDetailDTO getUserDetail(Integer userId);

    /**
     * Actualiza el rol de un usuario. Operación exclusiva de administradores.
     *
     * @param userId ID del usuario a actualizar
     * @param role   nuevo rol como String ("ADMIN" o "USER")
     * @throws com.hachiko.portal.exception.ValidationException         si el rol no es válido
     * @throws com.hachiko.portal.exception.ResourceNotFoundException   si el usuario no existe
     */
    void updateUserRole(Integer userId, String role);

    /**
     * Elimina permanentemente un usuario del sistema.
     * Las entidades relacionadas se eliminan por cascada en BD.
     *
     * @param userId ID del usuario a eliminar
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    void deleteUser(Integer userId);
}
