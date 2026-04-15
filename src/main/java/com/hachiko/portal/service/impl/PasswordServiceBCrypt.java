package com.hachiko.portal.service.impl;

import com.hachiko.portal.service.IPasswordService;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;

/**
 * Implementación BCrypt de IPasswordService.
 *
 * Delega completamente al PasswordEncoder configurado en AppConfig.
 * El factor de coste (strength=10) se define allí, no aquí — esta
 * clase no conoce el algoritmo específico, solo que existe un encoder.
 *
 * Principio DIP: recibe PasswordEncoder por constructor injection;
 * si se cambia a Argon2 en AppConfig, esta clase no cambia.
 *
 * Principio SRP: solo hash y verificación. No valida reglas de negocio
 * (longitud mínima, etc.) — eso pertenece a UserValidator.
 */
@Service
public class PasswordServiceBCrypt implements IPasswordService {

    private final PasswordEncoder passwordEncoder;

    public PasswordServiceBCrypt(PasswordEncoder passwordEncoder) {
        this.passwordEncoder = passwordEncoder;
    }

    @Override
    public String encode(String rawPassword) {
        return passwordEncoder.encode(rawPassword);
    }

    @Override
    public boolean matches(String rawPassword, String encodedPassword) {
        if (rawPassword == null || encodedPassword == null) {
            return false;
        }
        return passwordEncoder.matches(rawPassword, encodedPassword);
    }
}
