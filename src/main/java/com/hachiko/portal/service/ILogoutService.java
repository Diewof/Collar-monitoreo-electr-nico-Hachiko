package com.hachiko.portal.service;

/**
 * Contrato para el cierre de sesión del lado del servidor.
 * Módulo: Autenticación.
 *
 * El logout en un sistema JWT stateless requiere dos acciones:
 *  1. Revocar el token JWT en la blacklist para que no pueda reutilizarse.
 *  2. Limpiar los intentos de login del usuario.
 *
 * Principio ISP: interfaz mínima, no mezcla responsabilidades de login o registro.
 */
public interface ILogoutService {

    /**
     * Cierra la sesión del usuario: revoca el JWT y limpia intentos de login.
     *
     * @param userId    ID del usuario autenticado (extraído del token por el controlador)
     * @param ipAddress IP del cliente (para limpiar intentos por IP)
     * @param token     JWT completo (sin prefijo "Bearer ") para revocar en la blacklist
     */
    void logout(Integer userId, String ipAddress, String token);
}
