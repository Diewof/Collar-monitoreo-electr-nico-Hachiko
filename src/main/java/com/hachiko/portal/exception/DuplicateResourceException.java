package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando se intenta crear un recurso que ya existe (violación de unicidad).
 * Ejemplo: registrar un usuario con un email ya utilizado.
 *
 * HTTP 409 Conflict.
 */
public class DuplicateResourceException extends PortalException {

    public DuplicateResourceException(String message) {
        super(message, HttpStatus.valueOf(409));
    }
}
