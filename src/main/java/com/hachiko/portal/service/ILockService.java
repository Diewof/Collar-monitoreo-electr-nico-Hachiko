package com.hachiko.portal.service;

/**
 * Contrato para la gestión del bloqueo de cuentas por intentos fallidos.
 * Módulo: Autenticación — Seguridad.
 *
 * Regla de negocio migrada de authmodel.php:
 *   MAX_ATTEMPTS = 3, WINDOW = 10 minutos.
 *   Una cuenta se bloquea cuando email O ip acumula >= 3 intentos en 10 min.
 *   El bloqueo expira automáticamente al cumplirse la ventana de tiempo.
 *   Un login exitoso limpia todos los intentos del email e ip.
 *
 * Principio DIP: LoginService depende de esta interfaz, no de la implementación.
 */
public interface ILockService {

    /**
     * Determina si el par email+ip está actualmente bloqueado.
     * Debe evaluarse ANTES de verificar la contraseña en el flujo de login.
     *
     * @param email     correo del intento
     * @param ipAddress IP del cliente
     * @return true si hay >= 3 intentos en los últimos 15 minutos
     */
    boolean isLocked(String email, String ipAddress);

    /**
     * Registra un intento fallido de login para el par email+ip.
     * Invocado por LoginService tras un fallo de autenticación.
     *
     * @param email     correo del intento
     * @param ipAddress IP del cliente
     */
    void recordFailedAttempt(String email, String ipAddress);

    /**
     * Elimina todos los intentos fallidos asociados al email e ip.
     * Invocado por LoginService tras un login exitoso.
     *
     * @param email     correo del usuario que acaba de autenticarse
     * @param ipAddress IP del cliente
     */
    void clearAttempts(String email, String ipAddress);

    /**
     * Calcula los minutos restantes de bloqueo para el par email+ip.
     * Retorna 0 si no está bloqueado.
     * Usado por AccountLockedException para informar al usuario.
     *
     * @param email     correo del intento
     * @param ipAddress IP del cliente
     * @return minutos hasta que expire el bloqueo (0 si no bloqueado)
     */
    long getRemainingLockMinutes(String email, String ipAddress);

    /**
     * Calcula los intentos restantes antes de que la cuenta sea bloqueada.
     * Retorna 0 si ya se han agotado todos los intentos.
     *
     * @param email     correo del intento
     * @param ipAddress IP del cliente
     * @return intentos disponibles antes del bloqueo (mínimo 0)
     */
    int getRemainingAttempts(String email, String ipAddress);
}
