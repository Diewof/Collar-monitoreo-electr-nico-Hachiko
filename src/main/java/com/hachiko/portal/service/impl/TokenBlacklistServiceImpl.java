package com.hachiko.portal.service.impl;

import com.hachiko.portal.service.ITokenBlacklistService;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;

import java.util.concurrent.ConcurrentHashMap;

/**
 * Implementación en memoria de la blacklist de tokens JWT.
 *
 * Usa ConcurrentHashMap para thread-safety sin sincronización explícita.
 * La tarea @Scheduled limpia automáticamente los tokens ya expirados
 * para evitar crecimiento ilimitado del mapa.
 *
 * Limitación conocida: la blacklist se pierde al reiniciar el servidor.
 * En Semana 9 se reemplazará por TokenBlacklistRedisImpl sin modificar
 * ningún componente que dependa de ITokenBlacklistService (DIP).
 *
 * Requiere @EnableScheduling en PortalApplication para que cleanExpiredTokens() ejecute.
 */
@Service
public class TokenBlacklistServiceImpl implements ITokenBlacklistService {

    // Clave: token JWT — Valor: marca de tiempo de expiración (ms Unix)
    private final ConcurrentHashMap<String, Long> blacklist = new ConcurrentHashMap<>();

    @Override
    public void blacklist(String token, long expiresAt) {
        blacklist.put(token, expiresAt);
    }

    @Override
    public boolean isBlacklisted(String token) {
        Long expiresAt = blacklist.get(token);
        if (expiresAt == null) return false;

        // Si el token ya expiró naturalmente, ya no representa amenaza — eliminarlo.
        if (System.currentTimeMillis() > expiresAt) {
            blacklist.remove(token);
            return false;
        }
        return true;
    }

    /**
     * Limpieza periódica: elimina tokens expirados cada hora.
     * Evita que la blacklist crezca indefinidamente en sesiones largas.
     */
    @Scheduled(fixedRate = 3_600_000)
    public void cleanExpiredTokens() {
        long now = System.currentTimeMillis();
        blacklist.entrySet().removeIf(entry -> entry.getValue() < now);
    }
}
