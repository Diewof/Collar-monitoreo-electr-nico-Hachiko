package com.hachiko.portal.domain.enums;

/**
 * Estado de una notificación en el sistema.
 * Fuente: ENUM('pendiente','enviada','leída') en la tabla `notificacion`.
 */
public enum EstadoNotificacion {
    PENDIENTE,
    ENVIADA,
    LEIDA
}
