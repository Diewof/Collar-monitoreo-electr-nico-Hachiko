package com.hachiko.portal.service;

import com.hachiko.portal.dto.auth.NewPasswordRequest;
import com.hachiko.portal.dto.auth.PasswordResetRequest;

/**
 * Contrato para el flujo completo de recuperación de contraseña.
 * Módulo: Autenticación.
 *
 * Dos operaciones separadas: solicitar el token (envío por email)
 * y aplicar la nueva contraseña usando el token recibido.
 *
 * Principio SRP: no autentica ni registra usuarios. Solo gestiona el ciclo
 * de vida del token de recuperación y el cambio de contraseña.
 */
public interface IPasswordResetService {

    /**
     * Genera un token de recuperación y lo envía al email del usuario.
     *
     * Flujo (1:1 con authmodel.php requestPasswordReset()):
     *  1. Verificar que el email exista en usuarios.
     *     Si NO existe: retornar sin acción visible (seguridad — no revelar emails registrados).
     *  2. Eliminar token anterior si existe (deleteByEmail)
     *  3. Generar UUID como token
     *  4. Crear PasswordReset con expiresAt = now + 1 hora, createdAt = now
     *  5. passwordResetRepository.save()
     *  6. emailService.send() con el token en el cuerpo del mensaje
     *
     * @param request DTO con el email del usuario
     */
    void requestPasswordReset(PasswordResetRequest request);

    /**
     * Aplica el cambio de contraseña usando el token recibido por email.
     *
     * Flujo (1:1 con authmodel.php resetPassword()):
     *  1. findByToken(token) → InvalidTokenException si no existe
     *  2. reset.expiresAt.isBefore(now) → InvalidTokenException si expirado
     *  3. newPassword != confirmPassword → ValidationException
     *  4. UserValidator.validatePassword(newPassword) → ValidationException si falla
     *  5. usuarioRepository.updatePassword(email, encode(newPassword))
     *  6. passwordResetRepository.deleteByEmail(email)
     *
     * @param request DTO con token, newPassword y confirmPassword
     * @throws com.hachiko.portal.exception.InvalidTokenException si token inválido o expirado
     * @throws com.hachiko.portal.exception.ValidationException si contraseñas no cumplen reglas
     */
    void resetPassword(NewPasswordRequest request);
}
