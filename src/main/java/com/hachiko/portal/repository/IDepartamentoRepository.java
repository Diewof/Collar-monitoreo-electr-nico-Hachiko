package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Departamento;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Departamento.
 * Módulo: Ubicación (dato de referencia — catálogo de solo lectura).
 *
 * Candidato a caché: los departamentos cambian raramente.
 * La jerarquía es: Pais → Departamento → Ciudad.
 */
public interface IDepartamentoRepository extends JpaRepository<Departamento, Integer> {

    /**
     * Retorna los departamentos de un país, ordenados alfabéticamente.
     * Invocado en cascada cuando el usuario selecciona un país en el formulario de perfil.
     *
     * Equivalente al getDepartamentos($pais_id) de PropietarioModel.php.
     */
    List<Departamento> findByPais_PaisIdOrderByNombreAsc(Integer paisId);
}
