package com.hachiko.portal.service.impl;

import com.hachiko.portal.dto.auth.LoginRequest;
import com.hachiko.portal.dto.auth.LoginResponse;
import com.hachiko.portal.domain.Usuario;
import com.hachiko.portal.exception.AccountLockedException;
import com.hachiko.portal.exception.AuthenticationException;
import com.hachiko.portal.repository.IPropietarioRepository;
import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.ILockService;
import com.hachiko.portal.service.ILoginService;
import com.hachiko.portal.service.IPasswordService;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;

/**
 * Implementación del servicio de autenticación.
 * Módulo: Autenticación.
 *
 * Orquesta el flujo de login migrado de authmodel.php:
 * verificación de credenciales, bloqueo por intentos, actualización de lastLogin
 * y detección de perfil incompleto.
 */
@Service
public class LoginServiceImpl implements ILoginService {

    private final IUsuarioRepository usuarioRepository;
    private final ILockService lockService;
    private final IPasswordService passwordService;
    private final IPropietarioRepository propietarioRepository;

    public LoginServiceImpl(IUsuarioRepository usuarioRepository,
                            ILockService lockService,
                            IPasswordService passwordService,
                            IPropietarioRepository propietarioRepository) {
        this.usuarioRepository = usuarioRepository;
        this.lockService = lockService;
        this.passwordService = passwordService;
        this.propietarioRepository = propietarioRepository;
    }

    @Override
    @Transactional
    public LoginResponse login(LoginRequest request, String ipAddress) {
        String email = request.getEmail();
        String rawPassword = request.getPassword();

        // Paso 1: Buscar usuario por email.
        // Mensaje genérico — no revelar si el email existe o no.
        Usuario usuario = usuarioRepository.findByEmail(email)
                .orElseThrow(AuthenticationException::new);

        // Paso 2: Verificar bloqueo antes de comprobar la contraseña.
        if (lockService.isLocked(email, ipAddress)) {
            long minutos = lockService.getRemainingLockMinutes(email, ipAddress);
            throw new AccountLockedException(minutos > 0 ? minutos : 1L);
        }

        // Paso 3: Verificar contraseña BCrypt.
        if (!passwordService.matches(rawPassword, usuario.getPassword())) {
            lockService.recordFailedAttempt(email, ipAddress);
            int remaining = lockService.getRemainingAttempts(email, ipAddress);
            if (remaining == 0) {
                long mins = lockService.getRemainingLockMinutes(email, ipAddress);
                throw new AccountLockedException(mins > 0 ? mins : 1L);
            }
            String msg = remaining == 1
                    ? "Contraseña incorrecta. Tiene 1 intento restante antes de bloquear peticiones de login."
                    : "Contraseña incorrecta. Tiene " + remaining + " intentos restantes antes de bloquear peticiones de login.";
            throw new AuthenticationException(msg);
        }

        // Paso 4: Login exitoso — actualizar lastLogin.
        usuarioRepository.updateLastLogin(usuario.getId(), LocalDateTime.now());

        // Paso 5: Limpiar intentos fallidos.
        lockService.clearAttempts(email, ipAddress);

        // Paso 6: Verificar si el usuario tiene perfil de propietario.
        boolean tienePerfil = propietarioRepository.existsByUsuario_Id(usuario.getId());

        // Paso 7: Retornar respuesta con flag de perfil incompleto.
        return LoginResponse.builder()
                .userId(usuario.getId())
                .email(usuario.getEmail())
                .role(usuario.getRole().name())
                .requiresProfileCompletion(!tienePerfil)
                .build();
    }
}
