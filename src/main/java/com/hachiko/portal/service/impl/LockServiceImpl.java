package com.hachiko.portal.service.impl;

import com.hachiko.portal.domain.LoginAttempt;
import com.hachiko.portal.repository.ILoginAttemptRepository;
import com.hachiko.portal.service.ILockService;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;

/**
 * Implementación del servicio de bloqueo de cuentas.
 * Módulo: Autenticación — Seguridad.
 *
 * Reglas migradas de authmodel.php:
 *   MAX_ATTEMPTS = 3 intentos fallidos
 *   LOCK_WINDOW_MINUTES = 10 minutos
 *
 * Un email o IP se considera bloqueado cuando acumula >= 3 intentos
 * dentro de la ventana de 15 minutos. El bloqueo expira automáticamente
 * al cumplirse la ventana — no se almacena un flag de bloqueo explícito.
 */
@Service
public class LockServiceImpl implements ILockService {

    static final int MAX_ATTEMPTS = 3;
    static final int LOCK_WINDOW_MINUTES = 10;

    private final ILoginAttemptRepository loginAttemptRepository;

    public LockServiceImpl(ILoginAttemptRepository loginAttemptRepository) {
        this.loginAttemptRepository = loginAttemptRepository;
    }

    @Override
    public boolean isLocked(String email, String ipAddress) {
        LocalDateTime since = LocalDateTime.now().minusMinutes(LOCK_WINDOW_MINUTES);
        long attempts = loginAttemptRepository.countRecentAttempts(email, ipAddress, since);
        return attempts >= MAX_ATTEMPTS;
    }

    @Override
    @Transactional
    public void recordFailedAttempt(String email, String ipAddress) {
        LoginAttempt attempt = LoginAttempt.builder()
                .email(email)
                .ipAddress(ipAddress)
                .attemptTime(LocalDateTime.now())
                .build();
        loginAttemptRepository.save(attempt);
    }

    @Override
    @Transactional
    public void clearAttempts(String email, String ipAddress) {
        loginAttemptRepository.deleteByEmailOrIpAddress(email, ipAddress);
    }

    @Override
    public int getRemainingAttempts(String email, String ipAddress) {
        LocalDateTime since = LocalDateTime.now().minusMinutes(LOCK_WINDOW_MINUTES);
        long used = loginAttemptRepository.countRecentAttempts(email, ipAddress, since);
        return (int) Math.max(MAX_ATTEMPTS - used, 0);
    }

    @Override
    public long getRemainingLockMinutes(String email, String ipAddress) {
        LocalDateTime since = LocalDateTime.now().minusMinutes(LOCK_WINDOW_MINUTES);
        return loginAttemptRepository
                .findLastAttemptTime(email, ipAddress, since)
                .map(lastAttempt -> {
                    long minutesPassed = ChronoUnit.MINUTES.between(lastAttempt, LocalDateTime.now());
                    long remaining = LOCK_WINDOW_MINUTES - minutesPassed;
                    return remaining > 0 ? remaining : 0L;
                })
                .orElse(0L);
    }
}
