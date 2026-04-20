package com.hachiko.portal.security;

/**
 * Contrato para la generación y validación de tokens JWT.
 *
 * Principio DIP: AuthController, JwtAuthenticationFilter y LogoutServiceImpl
 * dependen de esta interfaz, no de JwtTokenProvider. Si se cambia el proveedor
 * JWT (de JJWT a Nimbus, por ejemplo), solo cambia la implementación —
 * ningún controlador ni filtro se modifica.
 */
public interface IJwtTokenProvider {

    /**
     * Genera un JWT firmado con HS256.
     *
     * @param userId ID del usuario autenticado
     * @param email  email del usuario (subject del token)
     * @param role   rol del usuario ("ADMIN" / "USER")
     * @return token JWT como String
     */
    String generateToken(Integer userId, String email, String role);

    /**
     * Valida que el token sea correcto (firma) y no haya expirado.
     *
     * @param token JWT a validar
     * @return true si el token es válido y vigente
     */
    boolean validateToken(String token);

    /**
     * Extrae el userId del claim del token.
     *
     * @param token JWT válido
     * @return userId almacenado en el claim
     */
    Integer getUserIdFromToken(String token);

    /**
     * Extrae el rol del claim del token.
     *
     * @param token JWT válido
     * @return rol almacenado en el claim (ej. "ADMIN", "USER")
     */
    String getRoleFromToken(String token);

    /**
     * Extrae la fecha de expiración del token en milisegundos Unix.
     * Usado por LogoutService para registrar cuándo expira el token revocado.
     *
     * @param token JWT válido
     * @return expiración en milisegundos desde epoch
     */
    long getExpirationFromToken(String token);

    /**
     * Retorna el tiempo de vida configurado del token en milisegundos.
     * Usado por AuthController para incluir expiresIn en el LoginResponse.
     *
     * @return duración del token en milisegundos (ej. 86400000 para 24 horas)
     */
    long getExpirationMs();
}
