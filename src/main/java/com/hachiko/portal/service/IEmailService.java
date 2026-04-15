package com.hachiko.portal.service;

/**
 * Contrato para el servicio de envío de correos electrónicos.
 * Módulo: Servicio Auxiliar — Comunicación.
 *
 * Principio DIP: RegisterService y PasswordResetService dependen de esta
 * interfaz, no de la implementación concreta (SMTP, SendGrid, stub de log, etc.).
 * Esto permite sustituir la implementación sin tocar ningún servicio de dominio.
 *
 * Principio SRP: este servicio no valida el contenido del correo ni decide
 * cuándo enviarlo — esa lógica pertenece al servicio de dominio que lo invoca.
 */
public interface IEmailService {

    /**
     * Envía un correo electrónico.
     *
     * @param destinatario dirección de correo del receptor (ej: "usuario@ejemplo.com")
     * @param asunto       línea de asunto del mensaje
     * @param cuerpo       cuerpo del mensaje en texto plano o HTML
     */
    void send(String destinatario, String asunto, String cuerpo);
}
