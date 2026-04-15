package com.hachiko.portal.dto.usuario;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.Size;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Datos para actualizar un usuario desde el panel de administración.
 * Todos los campos son opcionales: solo se actualiza lo que se proporciona.
 *
 * La lógica "si newPassword != null, también confirmPassword es requerida"
 * vive en UserService, no aquí.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class UpdateUserRequest {

    @Email(message = "El formato del email no es válido.")
    private String email;

    @Size(min = 8, message = "La contraseña debe tener al menos 8 caracteres.")
    private String newPassword;

    private String confirmPassword;

    /** Debe ser "ADMIN" o "USER". Validado en UserValidator.validateRole(). */
    private String role;
}
