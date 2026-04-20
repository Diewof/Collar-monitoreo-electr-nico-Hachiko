package com.hachiko.portal.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.client.RestTemplate;

/**
 * Configuración general de la aplicación.
 *
 * Declara beans de infraestructura compartidos entre capas.
 * El PasswordEncoder se inyecta en PasswordServiceBCrypt; ningún servicio
 * de dominio instancia BCryptPasswordEncoder directamente (DIP).
 */
@Configuration
public class AppConfig {

    /**
     * Codificador de contraseñas BCrypt con factor de coste 10.
     * Es el único lugar del sistema donde se define la implementación concreta;
     * el resto del código depende de la interfaz {@code PasswordEncoder}.
     */
    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder(10);
    }

    /**
     * Cliente HTTP reutilizable para llamadas a APIs externas (ej: Mailgun).
     * Singleton — compartido por todos los servicios que lo inyecten.
     */
    @Bean
    public RestTemplate restTemplate() {
        return new RestTemplate();
    }
}
