package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Plan;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Plan.
 * Módulo: Propietario — Planes de suscripción (dato de referencia, solo lectura).
 *
 * Candidato a caché: el catálogo de planes cambia únicamente por decisión de negocio,
 * no por operación de usuarios regulares.
 */
public interface IPlanRepository extends JpaRepository<Plan, Integer> {

    /**
     * Retorna todos los planes disponibles ordenados por costo ascendente.
     * Usado por el formulario de perfil de propietario para poblar el selector de plan.
     *
     * Equivalente al getPlanes() de PropietarioModel.php.
     */
    List<Plan> findAllByOrderByCostoAsc();
}
