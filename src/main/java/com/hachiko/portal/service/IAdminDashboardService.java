package com.hachiko.portal.service;

import com.hachiko.portal.dto.admin.DashboardStatsDTO;
import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;

import java.util.List;

/**
 * Contrato para el panel de administración.
 * Módulo: Administración.
 *
 * Cubre las tres responsabilidades del admin_model.php migradas a métodos separados:
 * estadísticas del dashboard, gestión de usuarios y actividad reciente.
 *
 * Principio SRP: métodos separados por responsabilidad — no un único método monolítico.
 * Principio DIP: los controladores dependen de esta interfaz, no de la implementación.
 */
public interface IAdminDashboardService {

    /**
     * Retorna las estadísticas del dashboard y el feed de actividad reciente.
     *
     * Estadísticas (migradas de admin_model.php getDashboardStats()):
     *   - totalUsuarios : count(users)
     *   - loginHoy      : count de usuarios con lastLogin en la fecha actual
     *   - intentosFallidosHoy : count de login_attempts de hoy
     *   - cuentasBloqueadas   : emails distintos con >= 3 intentos en últimos 15 min
     *
     * Actividad reciente (migrada de admin_model.php getRecentActivity()):
     *   Mezcla los últimos eventos de logins, registros e intentos fallidos.
     *   Ordenados por momento DESC, limitado a los 10 más recientes.
     *
     * @return DashboardStatsDTO con contadores y lista actividadReciente
     */
    DashboardStatsDTO getDashboardStats();

    /**
     * Retorna la lista de todos los usuarios del sistema.
     * Usado por la tabla de gestión de usuarios en el panel de admin.
     *
     * @return lista de UsuarioDTO, vacía si no hay usuarios
     */
    List<UsuarioDTO> getAllUsers();

    /**
     * Retorna el detalle completo de un usuario: datos de cuenta, perfil de propietario
     * (si existe) y cantidad de mascotas registradas.
     *
     * @param userId ID del usuario
     * @return UserDetailDTO con usuario, propietario (nullable) y cantidadMascotas
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    UserDetailDTO getUserDetail(Integer userId);

    /**
     * Actualiza el rol de un usuario.
     * Operación exclusiva de administradores.
     *
     * @param userId ID del usuario a actualizar
     * @param role   nuevo rol como String ("ADMIN" o "USER")
     * @throws com.hachiko.portal.exception.ValidationException si el rol no es válido
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    void updateUserRole(Integer userId, String role);

    /**
     * Elimina permanentemente un usuario del sistema.
     * Las entidades relacionadas (propietario, mascotas) se eliminan por cascada en BD.
     *
     * @param userId ID del usuario a eliminar
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    void deleteUser(Integer userId);
}
