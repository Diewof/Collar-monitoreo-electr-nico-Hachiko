package com.hachiko.portal.service.impl;

import com.hachiko.portal.service.IEmailService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.boot.autoconfigure.condition.ConditionalOnProperty;
import org.springframework.stereotype.Service;

/**
 * Implementación stub de IEmailService para la fase de transición.
 *
 * En lugar de enviar correos reales, registra cada llamada en el log.
 * Esto permite que RegisterService y PasswordResetService funcionen
 * correctamente en desarrollo/pruebas sin configurar un servidor SMTP.
 *
 * Principio DIP: los servicios de dominio inyectan IEmailService —
 * nunca esta clase concreta. Para activar envío real, basta con crear
 * EmailServiceSMTP que implemente IEmailService y marcar esta clase
 * como @Profile("dev") o eliminar @Primary.
 *
 * Principio SRP: esta clase solo se ocupa de registrar (loggear) el
 * intento de envío. No valida contenido ni decide cuándo enviar.
 */
@Service
@ConditionalOnProperty(name = "email.provider", havingValue = "stub", matchIfMissing = true)
public class EmailServiceStub implements IEmailService {

    private static final Logger log = LoggerFactory.getLogger(EmailServiceStub.class);

    @Override
    public void send(String destinatario, String asunto, String cuerpo) {
        log.info("[EMAIL-STUB] -----------------------------------------------");
        log.info("[EMAIL-STUB] Para    : {}", destinatario);
        log.info("[EMAIL-STUB] Asunto  : {}", asunto);
        log.info("[EMAIL-STUB] Cuerpo  : {}", cuerpo);
        log.info("[EMAIL-STUB] -----------------------------------------------");
    }
}
