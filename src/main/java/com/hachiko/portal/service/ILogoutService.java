package com.hachiko.portal.service;

/**
 * Contrato para el cierre de sesión del lado del servidor.
 * Módulo: Autenticación.
 *
 * En la arquitectura actual (stateless REST sin JWT), el logout del servidor
 * se limita a limpiar los intentos de login asociados para que la próxima
 * sesión comience con contador en cero.
 *
 * Nota de diseño: cuando se implemente JWT en Etapa 5, este servicio también
 * invalidará el token en la lista de revocación.
 *
 * Principio ISP: interfaz mínima, no mezcla responsabilidades de login o registro.
 */
public interface ILogoutService {

    /**
     * Ejecuta el cierre de sesión del lado del servidor para un usuario.
     *
     * Operación actual: limpiar intentos de login asociados al email del usuario
     * e ip para que la próxima sesión comience con contador en cero.
     *
     * @param userId    ID del usuario que cierra sesión
     * @param ipAddress IP del cliente (para limpiar intentos por IP)
     */
    void logout(Integer userId, String ipAddress);
}
