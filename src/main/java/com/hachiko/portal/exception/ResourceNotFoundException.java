package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando un recurso solicitado no existe en la base de datos.
 * Ejemplo: usuario no encontrado por email, perro no encontrado por id.
 *
 * HTTP 404 Not Found.
 */
public class ResourceNotFoundException extends PortalException {

    public ResourceNotFoundException(String resourceName, Object identifier) {
        super(resourceName + " con identificador '" + identifier + "' no encontrado.",
              HttpStatus.valueOf(404));
    }

    public ResourceNotFoundException(String message) {
        super(message, HttpStatus.valueOf(404));
    }
}
