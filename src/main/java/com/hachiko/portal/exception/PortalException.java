package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Clase base de todas las excepciones de negocio del portal.
 * Extiende RuntimeException para no obligar a los servicios a declararla
 * (unchecked), manteniendo las firmas de los métodos limpias.
 *
 * Cada subclase porta el HttpStatus apropiado para que el GlobalExceptionHandler
 * (Etapa 5) pueda responder correctamente sin instanceof.
 */
public abstract class PortalException extends RuntimeException {

    private final HttpStatus status;

    protected PortalException(String message, HttpStatus status) {
        super(message);
        this.status = status;
    }

    public HttpStatus getStatus() {
        return status;
    }
}
