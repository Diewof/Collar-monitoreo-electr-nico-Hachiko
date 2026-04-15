package com.hachiko.portal.dto.auth;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Respuesta del endpoint POST /api/auth/login.
 *
 * {@code requiresProfileCompletion} indica que el usuario no tiene
 * propietario registrado; el frontend debe redirigir al formulario
 * de perfil antes de acceder al dashboard.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class LoginResponse {

    private Integer userId;
    private String email;
    private String role;

    /** true si el usuario no tiene perfil de propietario creado aún. */
    private boolean requiresProfileCompletion;

    /** JWT generado por el controlador tras autenticación exitosa. */
    private String token;
}
