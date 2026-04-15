package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Usuario;
import com.hachiko.portal.domain.enums.UserRole;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import org.springframework.data.domain.Pageable;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

/**
 * Contrato de acceso a datos para la entidad Usuario.
 * Módulo: Autenticación y Gestión de Usuarios.
 *
 * Principio DIP: los servicios dependen de esta interfaz, nunca de
 * la implementación concreta que genera Spring Data en runtime.
 */
public interface IUsuarioRepository extends JpaRepository<Usuario, Integer> {

    /**
     * Busca un usuario por su dirección de correo electrónico.
     * Usado por LoginService para recuperar el usuario y verificar contraseña.
     */
    Optional<Usuario> findByEmail(String email);

    /**
     * Verifica si un email ya está registrado en el sistema.
     * Usado por UserValidator antes de crear un nuevo usuario.
     */
    boolean existsByEmail(String email);

    /**
     * Actualiza el rol de un usuario.
     * Operación administrativa; usada por UserService.
     */
    @Modifying
    @Query("UPDATE Usuario u SET u.role = :role WHERE u.id = :id")
    void updateRole(@Param("id") Integer id, @Param("role") UserRole role);

    /**
     * Actualiza la marca de tiempo del último login exitoso.
     * Invocado por LoginService tras autenticación correcta.
     */
    @Modifying
    @Query("UPDATE Usuario u SET u.lastLogin = :lastLogin WHERE u.id = :id")
    void updateLastLogin(@Param("id") Integer id, @Param("lastLogin") LocalDateTime lastLogin);

    /**
     * Actualiza la contraseña (hash) de un usuario.
     * Invocado por PasswordResetService tras validar el token.
     */
    @Modifying
    @Query("UPDATE Usuario u SET u.password = :password WHERE u.email = :email")
    void updatePassword(@Param("email") String email, @Param("password") String password);

    // -------------------------------------------------------------------------
    // Métodos para AdminDashboardService
    // -------------------------------------------------------------------------

    /**
     * Cuenta usuarios cuyo último login ocurrió dentro de un rango de tiempo.
     * AdminDashboardService pasa startOfDay y startOfTomorrow para loginHoy.
     */
    @Query("SELECT COUNT(u) FROM Usuario u WHERE u.lastLogin >= :from AND u.lastLogin < :to")
    long countByLastLoginBetween(@Param("from") LocalDateTime from,
                                 @Param("to") LocalDateTime to);

    /**
     * Retorna usuarios registrados en un rango de tiempo, ordenados por fecha desc.
     * Usado para construir el feed de actividad reciente (tipo REGISTRO).
     */
    @Query("SELECT u FROM Usuario u WHERE u.createdAt >= :from AND u.createdAt < :to ORDER BY u.createdAt DESC")
    List<Usuario> findByCreatedAtBetweenOrderByCreatedAtDesc(@Param("from") LocalDateTime from,
                                                              @Param("to") LocalDateTime to);

    /**
     * Retorna los usuarios con login más reciente desde un momento dado.
     * Usado para el feed de actividad reciente (tipo LOGIN).
     * Pasar PageRequest.of(0, 5) para limitar resultados.
     */
    @Query("SELECT u FROM Usuario u WHERE u.lastLogin >= :since ORDER BY u.lastLogin DESC")
    List<Usuario> findRecentLogins(@Param("since") LocalDateTime since, Pageable pageable);
}
