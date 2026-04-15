package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Collar;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.Optional;

/**
 * Contrato de acceso a datos para la entidad Collar.
 * Módulo: Collar y Sensores.
 *
 * Un collar tiene relación 1:1 con un perro. CollarService verifica esta
 * unicidad antes de delegar el vínculo a este repositorio.
 */
public interface ICollarRepository extends JpaRepository<Collar, Integer> {

    /**
     * Busca el collar vinculado a una mascota específica.
     * CollarService lo usa para verificar si la mascota ya tiene collar
     * antes de crear un nuevo vínculo.
     */
    Optional<Collar> findByPerro_PerroId(Integer perroId);

    /**
     * Verifica si una mascota ya tiene un collar vinculado.
     * CollarService lo usa como control de unicidad antes de vincular.
     */
    boolean existsByPerro_PerroId(Integer perroId);
}
