package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando las credenciales de login son incorrectas.
 * El mensaje es intencionalmente genérico para no revelar si el email existe.
 *
 * HTTP 401 Unauthorized.
 */
public class AuthenticationException extends PortalException {

    public AuthenticationException() {
        super("Credenciales incorrectas.", HttpStatus.valueOf(401));
    }

    public AuthenticationException(String message) {
        super(message, HttpStatus.valueOf(401));
    }
}
