package com.hachiko.portal.repository;

import com.hachiko.portal.domain.RegistroSensores;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

import java.time.LocalDateTime;
import java.util.List;

/**
 * Contrato de acceso a datos para lecturas de sensores.
 * Módulo: Collar y Sensores.
 *
 * Es el repositorio con mayor volumen de datos esperado en el sistema.
 * Las lecturas de las últimas 24 h de un collar son candidatas principales
 * a caché (TTL natural: la lectura más reciente desplaza a la anterior).
 *
 * SensorDataService debe poder recibir datos en lotes (batch) sin invocar
 * este repositorio una vez por lectura individual.
 */
public interface IRegistroSensoresRepository extends JpaRepository<RegistroSensores, Long> {

    /**
     * Retorna las lecturas de un collar ordenadas de más reciente a más antigua.
     * Acepta Pageable para controlar el volumen retornado (ej: últimas 100 lecturas).
     *
     * Candidato principal a caché: lecturas recientes de un collar se consultan
     * con alta frecuencia para mostrar el estado en tiempo real.
     */
    List<RegistroSensores> findByCollar_CollarIdOrderByMarcaTiempoDesc(Integer collarId, Pageable pageable);

    /**
     * Retorna las lecturas de un collar a partir de una marca de tiempo dada.
     * Usado para consultar las últimas N horas (ej: últimas 24 h).
     */
    List<RegistroSensores> findByCollar_CollarIdAndMarcaTiempoAfterOrderByMarcaTiempoDesc(
            Integer collarId, LocalDateTime desde);
}
