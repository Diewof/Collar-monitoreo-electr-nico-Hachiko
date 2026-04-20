package com.hachiko.portal.service;

/**
 * Contrato para la invalidación de tokens JWT al hacer logout.
 *
 * Principio SRP: responsabilidad única — gestionar tokens revocados.
 * Principio DIP: LogoutServiceImpl y JwtAuthenticationFilter dependen de esta
 * interfaz. La implementación actual usa memoria (ConcurrentHashMap).
 * En Semana 9 se reemplazará por una implementación Redis sin modificar
 * ningún componente que dependa de esta interfaz.
 */
public interface ITokenBlacklistService {

    /**
     * Agrega un token a la lista de tokens revocados.
     * El token permanece en la blacklist hasta su expiración natural.
     *
     * @param token     JWT completo (sin prefijo "Bearer ")
     * @param expiresAt marca de tiempo Unix en milisegundos de expiración del token
     */
    void blacklist(String token, long expiresAt);

    /**
     * Verifica si un token ha sido revocado.
     *
     * @param token JWT completo a verificar
     * @return true si el token fue revocado (logout previo)
     */
    boolean isBlacklisted(String token);
}
