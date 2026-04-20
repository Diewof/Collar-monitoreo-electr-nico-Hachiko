package com.hachiko.portal.security;

import io.jsonwebtoken.JwtException;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.security.Keys;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

import javax.crypto.SecretKey;
import java.nio.charset.StandardCharsets;
import java.util.Date;

/**
 * Proveedor de tokens JWT para el portal Hachiko.
 *
 * Responsabilidades (SRP):
 *  - Generar tokens firmados con HS256 al completar el login.
 *  - Validar tokens recibidos en cada request.
 *  - Extraer claims (userId, role) para el filtro de autenticación.
 *
 * El secret y la expiración se leen de application.yaml vía variables
 * de entorno (JWT_SECRET, JWT_EXPIRATION_MS) para no hardcodear credenciales.
 */
@Component
public class JwtTokenProvider implements IJwtTokenProvider {

    private final SecretKey secretKey;
    private final long expirationMs;

    public JwtTokenProvider(
            @Value("${jwt.secret}") String secret,
            @Value("${jwt.expiration-ms}") long expirationMs) {
        this.secretKey = Keys.hmacShaKeyFor(secret.getBytes(StandardCharsets.UTF_8));
        this.expirationMs = expirationMs;
    }

    /**
     * Genera un JWT firmado con HS256.
     *
     * @param userId ID del usuario autenticado
     * @param email  Email del usuario
     * @param role   Rol del usuario (ADMIN / USER)
     * @return token JWT como String
     */
    @Override
    public String generateToken(Integer userId, String email, String role) {
        Date now = new Date();
        Date expiry = new Date(now.getTime() + expirationMs);

        return Jwts.builder()
                .subject(email)
                .claim("userId", userId)
                .claim("role", role)
                .issuedAt(now)
                .expiration(expiry)
                .signWith(secretKey)
                .compact();
    }

    /**
     * Valida que el token sea correcto y no haya expirado.
     *
     * @param token JWT a validar
     * @return true si el token es válido y vigente
     */
    @Override
    public boolean validateToken(String token) {
        try {
            Jwts.parser()
                    .verifyWith(secretKey)
                    .build()
                    .parseSignedClaims(token);
            return true;
        } catch (JwtException | IllegalArgumentException e) {
            return false;
        }
    }

    /**
     * Extrae el userId del claim del token.
     *
     * @param token JWT válido
     * @return userId almacenado en el claim
     */
    @Override
    public Integer getUserIdFromToken(String token) {
        return Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload()
                .get("userId", Integer.class);
    }

    /**
     * Extrae el rol del claim del token.
     *
     * @param token JWT válido
     * @return rol almacenado en el claim (ej. "ADMIN" o "USER")
     */
    @Override
    public String getRoleFromToken(String token) {
        return Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload()
                .get("role", String.class);
    }

    /**
     * Extrae la fecha de expiración del token en milisegundos Unix.
     * Usado por LogoutService para registrar cuándo expira el token revocado.
     */
    @Override
    public long getExpirationFromToken(String token) {
        return Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload()
                .getExpiration()
                .getTime();
    }

    /**
     * Retorna el tiempo de vida configurado del token en milisegundos.
     * Usado por AuthController para incluir expiresIn en el LoginResponse.
     */
    @Override
    public long getExpirationMs() {
        return expirationMs;
    }
}
