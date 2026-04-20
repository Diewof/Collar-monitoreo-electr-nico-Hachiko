package com.hachiko.portal.service.impl;

import com.hachiko.portal.service.IEmailService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.autoconfigure.condition.ConditionalOnProperty;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.stereotype.Service;
import org.springframework.web.client.HttpClientErrorException;
import org.springframework.web.client.RestTemplate;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * Implementación real de IEmailService usando la API REST de Resend.
 * Activación: email.provider=resend en application-local.yaml.
 * DIP: los servicios de dominio inyectan IEmailService — nunca esta clase.
 */
@Service
@ConditionalOnProperty(name = "email.provider", havingValue = "resend")
public class EmailServiceResend implements IEmailService {

    private static final Logger log = LoggerFactory.getLogger(EmailServiceResend.class);
    private static final String RESEND_URL = "https://api.resend.com/emails";

    private final RestTemplate restTemplate;

    @Value("${email.resend.api-key}")
    private String apiKey;

    @Value("${email.resend.from:Hachiko <onboarding@resend.dev>}")
    private String from;

    public EmailServiceResend(RestTemplate restTemplate) {
        this.restTemplate = restTemplate;
    }

    @Override
    public void send(String destinatario, String asunto, String cuerpo) {
        HttpHeaders headers = new HttpHeaders();
        headers.setBearerAuth(apiKey);
        headers.setContentType(MediaType.APPLICATION_JSON);

        Map<String, Object> body = new LinkedHashMap<>();
        body.put("from", from);
        body.put("to", List.of(destinatario));
        body.put("subject", asunto);
        body.put("html", cuerpo);

        HttpEntity<Map<String, Object>> request = new HttpEntity<>(body, headers);

        try {
            String response = restTemplate.postForObject(RESEND_URL, request, String.class);
            log.info("[EMAIL-RESEND] Enviado a '{}' — id: {}", destinatario, response);
        } catch (HttpClientErrorException e) {
            log.error("[EMAIL-RESEND] Error HTTP {} al enviar a '{}': {}",
                    e.getStatusCode(), destinatario, e.getResponseBodyAsString());
            throw new RuntimeException("Error al enviar email: " + e.getResponseBodyAsString(), e);
        } catch (Exception e) {
            log.error("[EMAIL-RESEND] Error inesperado al enviar a '{}': {}", destinatario, e.getMessage());
            throw new RuntimeException("Error al enviar email: " + e.getMessage(), e);
        }
    }
}
