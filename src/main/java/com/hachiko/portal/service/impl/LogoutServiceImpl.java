package com.hachiko.portal.service.impl;

import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.ILockService;
import com.hachiko.portal.service.ILogoutService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;

/**
 * Implementación del servicio de cierre de sesión.
 * Módulo: Autenticación.
 *
 * En la arquitectura REST stateless actual (sin JWT), el logout del servidor
 * limpia los intentos de login asociados al email e IP para que la próxima
 * sesión comience con el contador en cero.
 *
 * Cuando se implemente JWT en Etapa 5, este servicio también invalidará el token.
 */
@Service
public class LogoutServiceImpl implements ILogoutService {

    private static final Logger log = LoggerFactory.getLogger(LogoutServiceImpl.class);

    private final IUsuarioRepository usuarioRepository;
    private final ILockService lockService;

    public LogoutServiceImpl(IUsuarioRepository usuarioRepository,
                             ILockService lockService) {
        this.usuarioRepository = usuarioRepository;
        this.lockService = lockService;
    }

    @Override
    public void logout(Integer userId, String ipAddress) {
        usuarioRepository.findById(userId).ifPresentOrElse(
                usuario -> {
                    lockService.clearAttempts(usuario.getEmail(), ipAddress);
                    log.info("[LOGOUT] Usuario {} cerró sesión desde IP {}", usuario.getEmail(), ipAddress);
                },
                () -> log.warn("[LOGOUT] Intento de logout con userId inexistente: {}", userId)
        );
    }
}
