package com.hachiko.portal.service.validation;

import com.hachiko.portal.domain.enums.Genero;
import com.hachiko.portal.repository.IRazaRepository;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.time.LocalDate;

/**
 * Validador centralizado de datos de mascota (perro).
 * Módulo: Mascota.
 *
 * Principio SRP: valida únicamente datos de mascotas. No persiste, no orquesta
 * flujos de negocio ni instancia otros servicios.
 *
 * Principio DIP: depende de IRazaRepository (abstracción) para verificar
 * que la raza seleccionada existe. La implementación concreta se inyecta.
 *
 * La validación de que una mascota pertenece al propietario autenticado
 * NO es responsabilidad de este validador — es responsabilidad de MascotaService
 * (control de acceso, no validación de datos de entrada).
 *
 * Diseño: stateless — no mantiene estado entre llamadas.
 */
@Component
public class MascotaValidator {

    /** Peso mínimo razonable para un perro (kg). */
    static final BigDecimal PESO_MINIMO = BigDecimal.valueOf(0.1);

    /** Peso máximo razonable para un perro (kg). */
    static final BigDecimal PESO_MAXIMO = BigDecimal.valueOf(120.0);

    /** Longitud máxima permitida para el nombre de una mascota. */
    static final int NOMBRE_MAX_LENGTH = 50;

    private final IRazaRepository razaRepository;

    public MascotaValidator(IRazaRepository razaRepository) {
        this.razaRepository = razaRepository;
    }

    // -------------------------------------------------------------------------
    // Validaciones individuales
    // -------------------------------------------------------------------------

    /**
     * Valida el nombre de la mascota: obligatorio y longitud máxima.
     */
    public ValidationResult validateNombre(String nombre) {
        ValidationResult result = new ValidationResult();

        if (nombre == null || nombre.isBlank()) {
            result.addError("El nombre de la mascota es obligatorio.");
            return result;
        }
        if (nombre.length() > NOMBRE_MAX_LENGTH) {
            result.addError("El nombre no puede superar " + NOMBRE_MAX_LENGTH + " caracteres.");
        }
        return result;
    }

    /**
     * Valida la fecha de nacimiento: obligatoria, no puede ser futura,
     * y no puede ser anterior a 30 años (límite de vida razonable para un perro).
     */
    public ValidationResult validateFechaNacimiento(LocalDate fechaNacimiento) {
        ValidationResult result = new ValidationResult();

        if (fechaNacimiento == null) {
            result.addError("La fecha de nacimiento es obligatoria.");
            return result;
        }
        if (fechaNacimiento.isAfter(LocalDate.now())) {
            result.addError("La fecha de nacimiento no puede ser una fecha futura.");
        }
        if (fechaNacimiento.isBefore(LocalDate.now().minusYears(30))) {
            result.addError("La fecha de nacimiento no puede ser anterior a 30 años.");
        }
        return result;
    }

    /**
     * Valida el peso de la mascota: obligatorio y dentro del rango razonable.
     * Rango: [{@value #PESO_MINIMO} kg, {@value #PESO_MAXIMO} kg].
     */
    public ValidationResult validatePeso(BigDecimal peso) {
        ValidationResult result = new ValidationResult();

        if (peso == null) {
            result.addError("El peso es obligatorio.");
            return result;
        }
        if (peso.compareTo(PESO_MINIMO) < 0) {
            result.addError("El peso mínimo es " + PESO_MINIMO + " kg.");
        }
        if (peso.compareTo(PESO_MAXIMO) > 0) {
            result.addError("El peso máximo es " + PESO_MAXIMO + " kg.");
        }
        return result;
    }

    /**
     * Valida que el género sea un valor permitido (M o F).
     * El tipo {@link Genero} garantiza en compilación que solo existen esos valores;
     * esta validación es para la capa de entrada (conversión desde String).
     *
     * @param generoStr cadena recibida del cliente (ej: "M", "F", "Macho", "Hembra")
     */
    public ValidationResult validateGenero(String generoStr) {
        ValidationResult result = new ValidationResult();

        if (generoStr == null || generoStr.isBlank()) {
            result.addError("El género es obligatorio.");
            return result;
        }
        String normalizado = generoStr.trim().toUpperCase();
        if (!normalizado.equals("M") && !normalizado.equals("F")) {
            result.addError("El género debe ser 'M' (Macho) o 'F' (Hembra). Valor recibido: '" + generoStr + "'.");
        }
        return result;
    }

    /**
     * Verifica que la raza seleccionada exista en el catálogo.
     * Consulta IRazaRepository — equivalente al bloque de validación interna de
     * MascotaModel.php que mezclaba acceso a datos con lógica de negocio.
     *
     * Al separarlo aquí, MascotaService puede llamar al validador antes de
     * persistir, manteniendo al repositorio libre de lógica de validación.
     */
    public ValidationResult validateRazaExiste(Integer razaId) {
        ValidationResult result = new ValidationResult();

        if (razaId == null) {
            result.addError("La raza es obligatoria.");
            return result;
        }
        if (!razaRepository.existsById(razaId)) {
            result.addError("La raza con id " + razaId + " no existe en el catálogo.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación combinada para creación/actualización de mascota
    // -------------------------------------------------------------------------

    /**
     * Valida todos los campos necesarios para registrar o actualizar una mascota.
     * Acumula todos los errores encontrados en un único resultado.
     */
    public ValidationResult validateMascota(String nombre, LocalDate fechaNacimiento,
                                            BigDecimal peso, String genero, Integer razaId) {
        ValidationResult result = new ValidationResult();

        validateNombre(nombre).getErrors().forEach(result::addError);
        validateFechaNacimiento(fechaNacimiento).getErrors().forEach(result::addError);
        validatePeso(peso).getErrors().forEach(result::addError);
        validateGenero(genero).getErrors().forEach(result::addError);
        validateRazaExiste(razaId).getErrors().forEach(result::addError);

        return result;
    }
}
