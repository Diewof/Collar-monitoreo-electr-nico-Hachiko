package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Ciudad;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Ciudad.
 * Módulo: Ubicación (dato de referencia — catálogo de solo lectura).
 *
 * Candidato a caché: las ciudades de un departamento son datos cuasi-estáticos.
 * La jerarquía es: Pais → Departamento → Ciudad.
 */
public interface ICiudadRepository extends JpaRepository<Ciudad, Integer> {

    /**
     * Retorna las ciudades de un departamento, ordenadas alfabéticamente.
     * Invocado en cascada cuando el usuario selecciona un departamento en el formulario de perfil.
     *
     * Equivalente al getCiudades($departamento_id) de PropietarioModel.php.
     */
    List<Ciudad> findByDepartamento_DepartamentoIdOrderByNombreAsc(Integer departamentoId);
}
