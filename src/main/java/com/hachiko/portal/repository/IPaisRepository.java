package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Pais;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Pais.
 * Módulo: Ubicación (dato de referencia — catálogo de solo lectura).
 *
 * Candidato a caché: los países cambian con frecuencia cercana a cero.
 * La anotación @Cacheable se aplica en la implementación del servicio que lo llama.
 */
public interface IPaisRepository extends JpaRepository<Pais, Integer> {

    /**
     * Retorna todos los países ordenados alfabéticamente.
     * Usado por el formulario de perfil de propietario para poblar el selector de país.
     */
    List<Pais> findAllByOrderByNombreAsc();
}
