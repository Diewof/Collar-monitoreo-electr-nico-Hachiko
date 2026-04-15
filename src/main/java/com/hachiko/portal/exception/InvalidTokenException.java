package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando un token de recuperación de contraseña no existe o ha expirado.
 *
 * HTTP 400 Bad Request: el cliente envió un token inválido.
 */
public class InvalidTokenException extends PortalException {

    public InvalidTokenException() {
        super("El token de recuperación no es válido o ha expirado.", HttpStatus.valueOf(400));
    }
}
