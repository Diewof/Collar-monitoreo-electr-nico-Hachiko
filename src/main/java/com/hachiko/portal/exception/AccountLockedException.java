package com.hachiko.portal.exception;

import org.springframework.http.HttpStatus;

/**
 * Lanzada cuando una cuenta está bloqueada por exceso de intentos fallidos.
 * El mensaje incluye el tiempo restante de bloqueo para informar al usuario.
 *
 * Regla de negocio: bloqueo tras 3 intentos fallidos en una ventana de 15 minutos.
 *
 * HTTP 429 Too Many Requests.
 */
public class AccountLockedException extends PortalException {

    public AccountLockedException(long minutosRestantes) {
        super("Cuenta bloqueada por múltiples intentos fallidos. " +
              "Intente nuevamente en " + minutosRestantes + " minuto(s).",
              HttpStatus.valueOf(429));
    }

    public AccountLockedException() {
        super("Cuenta bloqueada por múltiples intentos fallidos.", HttpStatus.valueOf(429));
    }
}
