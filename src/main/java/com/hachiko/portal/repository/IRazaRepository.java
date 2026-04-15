package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Raza;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

/**
 * Contrato de acceso a datos para la entidad Raza.
 * Módulo: Mascota — Catálogo de razas (dato de referencia, solo lectura).
 *
 * Candidato a caché indefinida: las razas son un catálogo estático que no
 * cambia por operación de usuarios. Se puede cachear al inicio de la aplicación.
 *
 * MascotaValidator depende de este repositorio para verificar que la raza
 * seleccionada existe antes de persistir una mascota nueva o actualizada.
 */
public interface IRazaRepository extends JpaRepository<Raza, Integer> {

    /**
     * Retorna todas las razas ordenadas alfabéticamente por nombre.
     * Usado por el formulario de registro/edición de mascota.
     *
     * Equivalente al getRazas() de MascotaModel.php.
     */
    List<Raza> findAllByOrderByNombreRazaAsc();
}
