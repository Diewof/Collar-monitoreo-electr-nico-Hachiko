package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando un usuario intenta acceder o modificar un recurso que no le pertenece.
 * Ejemplo: actualizar una mascota registrada bajo otro propietario.
 *
 * HTTP 403 Forbidden.
 */
public class AccessDeniedException extends PortalException {

    public AccessDeniedException() {
        super("No tiene permiso para realizar esta operación sobre el recurso solicitado.",
              HttpStatus.valueOf(403));
    }

    public AccessDeniedException(String message) {
        super(message, HttpStatus.valueOf(403));
    }
}
