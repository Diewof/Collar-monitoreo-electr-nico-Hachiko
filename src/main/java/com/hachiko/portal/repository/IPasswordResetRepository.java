package com.hachiko.portal.repository;

import com.hachiko.portal.domain.PasswordReset;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.LocalDateTime;
import java.util.Optional;

/**
 * Contrato de acceso a datos para tokens de recuperación de contraseña.
 * Módulo: Autenticación — Recuperación de contraseña.
 *
 * Cada email puede tener como máximo un token activo a la vez
 * (restricción UNIQUE en la columna email de la tabla password_resets).
 */
public interface IPasswordResetRepository extends JpaRepository<PasswordReset, Integer> {

    /**
     * Busca un token de recuperación por su valor.
     * PasswordResetService verifica validez y vigencia del token recibido.
     */
    Optional<PasswordReset> findByToken(String token);

    /**
     * Busca el token activo de recuperación para un email.
     * Permite detectar si ya existe un token vigente y decidir si reutilizarlo o regenerarlo.
     */
    Optional<PasswordReset> findByEmail(String email);

    /**
     * Verifica si existe un token no expirado para el email dado.
     * Usado por PasswordResetService antes de generar un nuevo token.
     */
    @Query("SELECT COUNT(p) > 0 FROM PasswordReset p " +
           "WHERE p.email = :email AND p.expiresAt > :now")
    boolean existsValidTokenForEmail(@Param("email") String email, @Param("now") LocalDateTime now);

    /**
     * Elimina el token asociado a un email.
     * Invocado por PasswordResetService tras aplicar exitosamente el cambio de contraseña.
     */
    @Modifying
    @Query("DELETE FROM PasswordReset p WHERE p.email = :email")
    void deleteByEmail(@Param("email") String email);

    /**
     * Elimina tokens expirados.
     * Invocado por una tarea de mantenimiento periódica.
     */
    @Modifying
    @Query("DELETE FROM PasswordReset p WHERE p.expiresAt < :now")
    void deleteExpired(@Param("now") LocalDateTime now);
}
