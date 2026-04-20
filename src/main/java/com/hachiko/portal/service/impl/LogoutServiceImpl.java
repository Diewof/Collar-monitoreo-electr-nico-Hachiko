package com.hachiko.portal.service.impl;

import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.security.IJwtTokenProvider;
import com.hachiko.portal.service.ILockService;
import com.hachiko.portal.service.ILogoutService;
import com.hachiko.portal.service.ITokenBlacklistService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;

/**
 * Implementación del servicio de cierre de sesión.
 * Módulo: Autenticación.
 *
 * Acciones al hacer logout:
 *  1. Revocar el JWT en la blacklist para que no pueda reutilizarse hasta su expiración.
 *  2. Limpiar los intentos de login del usuario (comportamiento original).
 *
 * Principio DIP: depende de IJwtTokenProvider (interfaz), no de JwtTokenProvider (clase concreta).
 */
@Service
public class LogoutServiceImpl implements ILogoutService {

    private static final Logger log = LoggerFactory.getLogger(LogoutServiceImpl.class);

    private final IUsuarioRepository usuarioRepository;
    private final ILockService lockService;
    private final ITokenBlacklistService tokenBlacklistService;
    private final IJwtTokenProvider jwtTokenProvider;

    public LogoutServiceImpl(IUsuarioRepository usuarioRepository,
                             ILockService lockService,
                             ITokenBlacklistService tokenBlacklistService,
                             IJwtTokenProvider jwtTokenProvider) {
        this.usuarioRepository = usuarioRepository;
        this.lockService = lockService;
        this.tokenBlacklistService = tokenBlacklistService;
        this.jwtTokenProvider = jwtTokenProvider;
    }

    @Override
    public void logout(Integer userId, String ipAddress, String token) {
        // 1. Revocar el token JWT inmediatamente.
        if (token != null && !token.isBlank()) {
            long expiresAt = jwtTokenProvider.getExpirationFromToken(token);
            tokenBlacklistService.blacklist(token, expiresAt);
            log.info("[LOGOUT] Token revocado para userId={}", userId);
        }

        // 2. Limpiar intentos de login.
        usuarioRepository.findById(userId).ifPresentOrElse(
                usuario -> {
                    lockService.clearAttempts(usuario.getEmail(), ipAddress);
                    log.info("[LOGOUT] Usuario {} cerró sesión desde IP {}", usuario.getEmail(), ipAddress);
                },
                () -> log.warn("[LOGOUT] Intento de logout con userId inexistente: {}", userId)
        );
    }
}
