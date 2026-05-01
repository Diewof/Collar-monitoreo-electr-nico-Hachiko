package com.hachiko.portal.repository;

import com.hachiko.portal.domain.LoginAttempt;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.LocalDateTime;
import java.util.List;

/**
 * Contrato de acceso a datos para intentos fallidos de login.
 * Módulo: Autenticación — Bloqueo de cuentas.
 *
 * La regla de negocio ("bloquear tras 3 intentos en 15 min") vive en LockService,
 * no aquí. Este repositorio solo provee los datos necesarios para evaluarla.
 */
public interface ILoginAttemptRepository extends JpaRepository<LoginAttempt, Integer> {

    /**
     * Cuenta los intentos fallidos recientes asociados a un email o IP.
     * LockService usa este conteo para decidir si la cuenta debe bloquearse.
     *
     * @param email    correo del intento
     * @param ipAddress IP del cliente
     * @param since    límite temporal (ej: ahora − 15 minutos)
     */
    @Query("SELECT COUNT(a) FROM LoginAttempt a " +
           "WHERE (a.email = :email OR a.ipAddress = :ipAddress) " +
           "AND a.attemptTime > :since")
    long countRecentAttempts(@Param("email") String email,
                             @Param("ipAddress") String ipAddress,
                             @Param("since") LocalDateTime since);

    /**
     * Obtiene la marca de tiempo del intento más reciente para un email o IP.
     * Usado para calcular el tiempo restante de bloqueo.
     */
    @Query("SELECT MAX(a.attemptTime) FROM LoginAttempt a " +
           "WHERE (a.email = :email OR a.ipAddress = :ipAddress) " +
           "AND a.attemptTime > :since")
    java.util.Optional<LocalDateTime> findLastAttemptTime(@Param("email") String email,
                                                          @Param("ipAddress") String ipAddress,
                                                          @Param("since") LocalDateTime since);

    /**
     * Elimina todos los intentos asociados a un email o IP.
     * Invocado por LockService tras un login exitoso (limpieza de historial).
     */
    @Modifying
    @Query("DELETE FROM LoginAttempt a WHERE a.email = :email OR a.ipAddress = :ipAddress")
    void deleteByEmailOrIpAddress(@Param("email") String email, @Param("ipAddress") String ipAddress);

    /**
     * Elimina intentos anteriores a un punto en el tiempo.
     * Invocado por una tarea de mantenimiento para evitar crecimiento indefinido de la tabla.
     */
    @Modifying
    @Query("DELETE FROM LoginAttempt a WHERE a.attemptTime < :cutoff")
    void deleteOlderThan(@Param("cutoff") LocalDateTime cutoff);

    // -------------------------------------------------------------------------
    // Métodos para AdminDashboardService
    // -------------------------------------------------------------------------

    /**
     * Cuenta todos los intentos fallidos en un rango de tiempo.
     * AdminDashboardService pasa startOfDay y startOfTomorrow para intentosFallidosHoy.
     */
    @Query("SELECT COUNT(a) FROM LoginAttempt a WHERE a.attemptTime >= :from AND a.attemptTime < :to")
    long countByAttemptTimeBetween(@Param("from") LocalDateTime from,
                                   @Param("to") LocalDateTime to);

    /**
     * Retorna los emails distintos con >= maxAttempts intentos desde 'since'.
     * AdminDashboardService llama .size() sobre la lista para obtener cuentasBloqueadas.
     */
    @Query("SELECT DISTINCT a.email FROM LoginAttempt a " +
           "WHERE a.attemptTime > :since " +
           "GROUP BY a.email HAVING COUNT(a) >= :maxAttempts")
    List<String> findBlockedEmails(@Param("since") LocalDateTime since,
                                   @Param("maxAttempts") long maxAttempts);

    /**
     * Retorna los intentos fallidos más recientes desde un momento dado, ordenados desc.
     * Usado para el feed de actividad reciente (tipo INTENTO_FALLIDO).
     * Pasar PageRequest.of(0, 5) para limitar resultados.
     */
    @Query("SELECT a FROM LoginAttempt a WHERE a.attemptTime >= :since ORDER BY a.attemptTime DESC")
    List<LoginAttempt> findRecentAttempts(@Param("since") LocalDateTime since, Pageable pageable);
}
