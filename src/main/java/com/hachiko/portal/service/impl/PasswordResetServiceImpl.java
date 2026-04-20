package com.hachiko.portal.service.impl;

import com.hachiko.portal.domain.PasswordReset;
import com.hachiko.portal.dto.auth.NewPasswordRequest;
import com.hachiko.portal.dto.auth.PasswordResetRequest;
import com.hachiko.portal.exception.InvalidTokenException;
import com.hachiko.portal.exception.ValidationException;
import com.hachiko.portal.repository.IPasswordResetRepository;
import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.IEmailService;
import com.hachiko.portal.service.IPasswordResetService;
import com.hachiko.portal.service.IPasswordService;
import com.hachiko.portal.service.validation.UserValidator;
import com.hachiko.portal.service.validation.ValidationResult;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.UUID;

/**
 * Implementación del servicio de recuperación de contraseña.
 * Módulo: Autenticación.
 *
 * Migra requestPasswordReset() y resetPassword() de authmodel.php.
 * Token UUID con TTL de 1 hora; eliminado tras el uso exitoso.
 */
@Service
public class PasswordResetServiceImpl implements IPasswordResetService {

    private static final Logger log = LoggerFactory.getLogger(PasswordResetServiceImpl.class);
    private static final int TOKEN_EXPIRY_HOURS = 1;

    private final IUsuarioRepository usuarioRepository;
    private final IPasswordResetRepository passwordResetRepository;
    private final IPasswordService passwordService;
    private final UserValidator userValidator;
    private final IEmailService emailService;

    public PasswordResetServiceImpl(IUsuarioRepository usuarioRepository,
                                    IPasswordResetRepository passwordResetRepository,
                                    IPasswordService passwordService,
                                    UserValidator userValidator,
                                    IEmailService emailService) {
        this.usuarioRepository = usuarioRepository;
        this.passwordResetRepository = passwordResetRepository;
        this.passwordService = passwordService;
        this.userValidator = userValidator;
        this.emailService = emailService;
    }

    @Override
    @Transactional
    public void requestPasswordReset(PasswordResetRequest request) {
        String email = request.getEmail();

        // Paso 1: Si el email no existe, retornar silenciosamente.
        // Seguridad: no revelar qué correos están registrados.
        if (!usuarioRepository.existsByEmail(email)) {
            return;
        }

        // Paso 2: Eliminar token anterior si existe.
        passwordResetRepository.deleteByEmail(email);

        // Paso 3: Generar token UUID.
        String token = UUID.randomUUID().toString();
        LocalDateTime now = LocalDateTime.now();

        // Paso 4: Persistir token con expiración de 1 hora.
        PasswordReset reset = PasswordReset.builder()
                .email(email)
                .token(token)
                .expiresAt(now.plusHours(TOKEN_EXPIRY_HOURS))
                .createdAt(now)
                .build();
        passwordResetRepository.save(reset);

        // Paso 5: Enviar email con el token (fallo no cancela la operación).
        try {
            emailService.send(
                    email,
                    "Recuperar contraseña — Hachiko",
                    "Para restablecer tu contraseña usa el siguiente token:\n\n" + token +
                    "\n\nEste enlace expira en " + TOKEN_EXPIRY_HOURS + " hora(s)."
            );
        } catch (Exception e) {
            log.warn("[PASSWORD-RESET] Email no enviado a '{}': {}", email, e.getMessage());
        }
    }

    @Override
    @Transactional
    public void resetPassword(NewPasswordRequest request) {
        String token = request.getToken();
        String newPassword = request.getNewPassword();
        String confirmPassword = request.getConfirmPassword();

        // Paso 1: Buscar token.
        PasswordReset reset = passwordResetRepository.findByToken(token)
                .orElseThrow(InvalidTokenException::new);

        // Paso 2: Verificar que no haya expirado.
        if (reset.getExpiresAt().isBefore(LocalDateTime.now())) {
            throw new InvalidTokenException();
        }

        // Paso 3: Verificar que las contraseñas coinciden.
        if (!newPassword.equals(confirmPassword)) {
            throw new ValidationException("Las contraseñas no coinciden.");
        }

        // Paso 4: Validar reglas de la nueva contraseña.
        ValidationResult validation = userValidator.validatePassword(newPassword);
        if (!validation.isValid()) {
            throw new ValidationException(validation.getErrors());
        }

        // Paso 5: Actualizar contraseña con hash BCrypt.
        String hashedPassword = passwordService.encode(newPassword);
        usuarioRepository.updatePassword(reset.getEmail(), hashedPassword);

        // Paso 6: Eliminar token usado.
        passwordResetRepository.deleteByEmail(reset.getEmail());
    }
}
