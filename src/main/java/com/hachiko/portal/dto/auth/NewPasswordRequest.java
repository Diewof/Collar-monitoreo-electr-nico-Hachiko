package com.hachiko.portal.dto.auth;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Datos recibidos en el endpoint POST /api/auth/reset-password.
 * Completa el restablecimiento de contraseña usando el token enviado por email.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class NewPasswordRequest {

    @NotBlank(message = "El token de recuperación es obligatorio.")
    private String token;

    @NotBlank(message = "La nueva contraseña es obligatoria.")
    @Size(min = 8, message = "La contraseña debe tener al menos 8 caracteres.")
    private String newPassword;

    @NotBlank(message = "La confirmación de contraseña es obligatoria.")
    private String confirmPassword;
}
