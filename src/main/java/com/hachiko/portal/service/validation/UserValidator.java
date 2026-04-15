package com.hachiko.portal.service.validation;

import com.hachiko.portal.domain.enums.UserRole;
import com.hachiko.portal.repository.IUsuarioRepository;
import org.springframework.stereotype.Component;

/**
 * Validador centralizado de datos de usuario.
 * Módulo: Autenticación y Gestión de Usuarios.
 *
 * Principio SRP: esta clase es la ÚNICA fuente de verdad para las reglas de
 * validación de email, contraseña y rol en todo el sistema. Si la regla de
 * longitud mínima de contraseña cambia, solo se modifica este archivo.
 *
 * Principio DIP: depende de IUsuarioRepository (abstracción), no de ninguna
 * implementación concreta de repositorio.
 *
 * Diseño: stateless — no mantiene estado entre llamadas; es seguro como bean
 * singleton de Spring (@Component).
 */
@Component
public class UserValidator {

    /** Longitud mínima exigida para contraseñas nuevas o restablecidas. */
    static final int PASSWORD_MIN_LENGTH = 8;

    /** Longitud máxima permitida para un email. */
    static final int EMAIL_MAX_LENGTH = 255;

    private final IUsuarioRepository usuarioRepository;

    public UserValidator(IUsuarioRepository usuarioRepository) {
        this.usuarioRepository = usuarioRepository;
    }

    // -------------------------------------------------------------------------
    // Validaciones de email
    // -------------------------------------------------------------------------

    /**
     * Valida que un email tenga formato correcto y longitud aceptable.
     * No verifica si el email ya existe en la BD — usar {@link #validateEmailNotTaken(String)}
     * para eso.
     */
    public ValidationResult validateEmailFormat(String email) {
        ValidationResult result = new ValidationResult();

        if (email == null || email.isBlank()) {
            result.addError("El email es obligatorio.");
            return result;
        }
        if (email.length() > EMAIL_MAX_LENGTH) {
            result.addError("El email no puede superar " + EMAIL_MAX_LENGTH + " caracteres.");
        }
        if (!email.matches("^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$")) {
            result.addError("El formato del email no es válido.");
        }
        return result;
    }

    /**
     * Verifica que el email NO esté registrado en el sistema.
     * Usado por RegisterService antes de crear un nuevo usuario.
     */
    public ValidationResult validateEmailNotTaken(String email) {
        ValidationResult result = new ValidationResult();

        if (usuarioRepository.existsByEmail(email)) {
            result.addError("El email ya está registrado en el sistema.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validaciones de contraseña
    // -------------------------------------------------------------------------

    /**
     * Valida que una contraseña cumpla los requisitos mínimos de seguridad.
     * Reglas actuales: no vacía, mínimo {@value #PASSWORD_MIN_LENGTH} caracteres.
     */
    public ValidationResult validatePassword(String password) {
        ValidationResult result = new ValidationResult();

        if (password == null || password.isBlank()) {
            result.addError("La contraseña es obligatoria.");
            return result;
        }
        if (password.length() < PASSWORD_MIN_LENGTH) {
            result.addError("La contraseña debe tener al menos " + PASSWORD_MIN_LENGTH + " caracteres.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validaciones de rol
    // -------------------------------------------------------------------------

    /**
     * Valida que el valor de rol sea uno de los permitidos por el sistema.
     * Usado por AdminDashboardService al cambiar el rol de un usuario.
     *
     * @param role cadena con el nombre del rol (ej: "ADMIN", "USER")
     */
    public ValidationResult validateRole(String role) {
        ValidationResult result = new ValidationResult();

        if (role == null || role.isBlank()) {
            result.addError("El rol es obligatorio.");
            return result;
        }
        try {
            UserRole.valueOf(role.toUpperCase());
        } catch (IllegalArgumentException e) {
            result.addError("El rol '" + role + "' no es válido. Valores permitidos: ADMIN, USER.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación combinada para registro de nuevo usuario
    // -------------------------------------------------------------------------

    /**
     * Valida todos los campos necesarios para registrar un nuevo usuario.
     * Acumula todos los errores encontrados en un único resultado.
     *
     * Verifica: formato de email, disponibilidad del email, y contraseña.
     */
    public ValidationResult validateNewUser(String email, String password) {
        ValidationResult result = new ValidationResult();

        ValidationResult emailFormat = validateEmailFormat(email);
        emailFormat.getErrors().forEach(result::addError);

        if (emailFormat.isValid()) {
            ValidationResult emailTaken = validateEmailNotTaken(email);
            emailTaken.getErrors().forEach(result::addError);
        }

        ValidationResult passwordResult = validatePassword(password);
        passwordResult.getErrors().forEach(result::addError);

        return result;
    }
}
