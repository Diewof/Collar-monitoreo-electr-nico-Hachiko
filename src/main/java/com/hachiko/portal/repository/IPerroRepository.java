package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Perro;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Perro (mascota).
 * Módulo: Mascota.
 *
 * Todos los métodos de escritura deben ser invocados desde MascotaService,
 * que es quien verifica que la mascota pertenece al propietario autenticado
 * antes de delegar aquí.
 */
public interface IPerroRepository extends JpaRepository<Perro, Integer> {

    /**
     * Retorna todas las mascotas registradas para un propietario, ordenadas por nombre.
     * Usado por MascotaService para listar las mascotas del usuario autenticado.
     *
     * Equivalente al getMascotasByPropietario($propietarioId) de MascotaModel.php.
     */
    List<Perro> findByPropietario_PropietarioIdOrderByNombreAsc(Integer propietarioId);

    /**
     * Verifica si una mascota específica pertenece a un propietario dado.
     * MascotaService llama a este método como control de acceso antes de
     * cualquier operación de escritura (update, delete).
     */
    boolean existsByPerroIdAndPropietario_PropietarioId(Integer perroId, Integer propietarioId);

    /**
     * Cuenta cuántas mascotas tiene registradas un propietario.
     * Permite que el frontend determine si debe mostrar el modal de "registra tu primera mascota".
     *
     * Equivalente al tieneMascotas($propietarioId) de MascotaModel.php,
     * pero sin combinar validación y persistencia en el mismo método.
     */
    long countByPropietario_PropietarioId(Integer propietarioId);
}
