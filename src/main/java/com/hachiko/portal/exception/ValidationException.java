package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

import java.util.List;

/**
 * Lanzada cuando una ValidationResult contiene errores.
 * El servicio construye esta excepción pasando result.getErrors()
 * para que el GlobalExceptionHandler devuelva todos los mensajes al cliente.
 *
 * HTTP 422 Unprocessable Entity: los datos llegaron pero violan reglas de negocio.
 */
public class ValidationException extends PortalException {

    private final List<String> errors;

    public ValidationException(List<String> errors) {
        super(errors.isEmpty() ? "Error de validación." : errors.get(0), HttpStatus.valueOf(422));
        this.errors = List.copyOf(errors);
    }

    public ValidationException(String singleMessage) {
        super(singleMessage, HttpStatus.valueOf(422));
        this.errors = List.of(singleMessage);
    }

    public List<String> getErrors() {
        return errors;
    }
}
