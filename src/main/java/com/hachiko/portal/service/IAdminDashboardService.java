package com.hachiko.portal.service;

import com.hachiko.portal.dto.admin.DashboardStatsDTO;

/**
 * Contrato para las estadísticas del panel de administración.
 * Módulo: Administración.
 *
 * Principio SRP: responsabilidad única — proveer estadísticas y actividad reciente.
 * La gestión de usuarios (listar, cambiar rol, eliminar) pertenece a IAdminUserService.
 * Principio DIP: AdminController depende de esta interfaz, no de la implementación.
 */
public interface IAdminDashboardService {

    /**
     * Retorna las estadísticas del dashboard y el feed de actividad reciente.
     *
     * Estadísticas:
     *   - totalUsuarios       : count(users)
     *   - loginHoy            : usuarios con lastLogin en la fecha actual
     *   - intentosFallidosHoy : login_attempts de hoy
     *   - cuentasBloqueadas   : emails con >= 3 intentos en últimos 15 min
     *
     * Actividad reciente: mezcla de logins, registros e intentos fallidos,
     * ordenados por momento DESC, limitado a los 10 más recientes.
     *
     * @return DashboardStatsDTO con contadores y lista actividadReciente
     */
    DashboardStatsDTO getDashboardStats();
}
