package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Propietario;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.Optional;

/**
 * Contrato de acceso a datos para la entidad Propietario.
 * Módulo: Propietario.
 *
 * La transacción que coordina Residencia + Propietario vive en PropietarioService,
 * no en este repositorio. Aquí solo se define el contrato de persistencia.
 */
public interface IPropietarioRepository extends JpaRepository<Propietario, Integer> {

    /**
     * Busca el perfil de propietario vinculado a un usuario.
     * Relación 1:1 entre Usuario y Propietario.
     *
     * Usado por PropietarioService para recuperar el perfil tras el login
     * y por LoginService para determinar si el usuario completó su perfil.
     */
    Optional<Propietario> findByUsuario_Id(Integer userId);

    /**
     * Verifica si existe un perfil de propietario para el usuario dado.
     * Permite que LoginService retorne el flag `requires_profile_completion`
     * sin cargar el objeto completo.
     */
    boolean existsByUsuario_Id(Integer userId);
}
