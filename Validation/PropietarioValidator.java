package com.hachiko.portal.service.validation;

import com.hachiko.portal.repository.ICiudadRepository;
import com.hachiko.portal.repository.IPlanRepository;
import org.springframework.stereotype.Component;

/**
 * Validador centralizado de datos del perfil de propietario.
 * Módulo: Propietario (Módulo 3).
 *
 * Principio SRP: esta clase es la ÚNICA fuente de verdad para las reglas
 * de validación de nombre, teléfono, dirección, ciudad y plan.
 * Si la regex de nombres cambia, solo se modifica este archivo.
 *
 * Principio DIP: depende de ICiudadRepository e IPlanRepository
 * (abstracciones), nunca de implementaciones concretas.
 *
 * Reglas extraídas de propietario_controller.php:
 *   - Nombres: /^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$/
 *   - Teléfono: /^[0-9]{7,15}$/
 *   - Dirección: /^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$/
 */
@Component
public class PropietarioValidator {

    static final int NOMBRE_MIN_LENGTH = 2;
    static final int NOMBRE_MAX_LENGTH = 45;
    static final int TELEFONO_MIN_LENGTH = 7;
    static final int TELEFONO_MAX_LENGTH = 15;
    static final int DIRECCION_MIN_LENGTH = 5;
    static final int DIRECCION_MAX_LENGTH = 100;

    /** Letras (incluyendo acentos y ñ) y espacios; mínimo 2, máximo 45 caracteres. */
    private static final String NOMBRE_REGEX =
            "^[A-Za-z\\u00C0-\\u024F\\s]{" + NOMBRE_MIN_LENGTH + "," + NOMBRE_MAX_LENGTH + "}$";

    /** Solo dígitos, 7 a 15 caracteres. */
    private static final String TELEFONO_REGEX =
            "^[0-9]{" + TELEFONO_MIN_LENGTH + "," + TELEFONO_MAX_LENGTH + "}$";

    /** Alfanumérico más caracteres comunes de dirección colombiana (.,#-). */
    private static final String DIRECCION_REGEX =
            "^[A-Za-z0-9\\u00C0-\\u024F\\s.,#\\-]{" + DIRECCION_MIN_LENGTH + "," + DIRECCION_MAX_LENGTH + "}$";

    private final ICiudadRepository ciudadRepository;
    private final IPlanRepository planRepository;

    public PropietarioValidator(ICiudadRepository ciudadRepository,
                                IPlanRepository planRepository) {
        this.ciudadRepository = ciudadRepository;
        this.planRepository = planRepository;
    }

    // -------------------------------------------------------------------------
    // Validación de nombres (requeridos)
    // -------------------------------------------------------------------------

    /**
     * Valida un nombre requerido (primer nombre o apellido).
     * No puede estar vacío; debe cumplir la regex de letras y espacios.
     */
    public ValidationResult validateNombre(String nombre, String etiqueta) {
        ValidationResult result = new ValidationResult();
        if (nombre == null || nombre.isBlank()) {
            result.addError(etiqueta + " es obligatorio.");
            return result;
        }
        if (!nombre.matches(NOMBRE_REGEX)) {
            result.addError(etiqueta + " solo puede contener letras y espacios, entre "
                    + NOMBRE_MIN_LENGTH + " y " + NOMBRE_MAX_LENGTH + " caracteres.");
        }
        return result;
    }

    /**
     * Valida un nombre opcional (segundo nombre o segundo apellido).
     * Si se proporciona, debe cumplir la regex; si está vacío o nulo, es válido.
     */
    public ValidationResult validateNombreOpcional(String nombre, String etiqueta) {
        ValidationResult result = new ValidationResult();
        if (nombre == null || nombre.isBlank()) {
            return result; // campo opcional — ausencia es válida
        }
        if (!nombre.matches(NOMBRE_REGEX)) {
            result.addError(etiqueta + " solo puede contener letras y espacios, entre "
                    + NOMBRE_MIN_LENGTH + " y " + NOMBRE_MAX_LENGTH + " caracteres.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación de teléfono
    // -------------------------------------------------------------------------

    /**
     * Valida que el teléfono sea numérico y tenga entre 7 y 15 dígitos.
     */
    public ValidationResult validateTelefono(String telefono) {
        ValidationResult result = new ValidationResult();
        if (telefono == null || telefono.isBlank()) {
            result.addError("El teléfono es obligatorio.");
            return result;
        }
        if (!telefono.matches(TELEFONO_REGEX)) {
            result.addError("El teléfono debe contener solo dígitos, entre "
                    + TELEFONO_MIN_LENGTH + " y " + TELEFONO_MAX_LENGTH + " caracteres.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación de dirección
    // -------------------------------------------------------------------------

    /**
     * Valida que la dirección tenga caracteres permitidos y longitud correcta.
     */
    public ValidationResult validateDireccion(String direccion) {
        ValidationResult result = new ValidationResult();
        if (direccion == null || direccion.isBlank()) {
            result.addError("La dirección es obligatoria.");
            return result;
        }
        if (!direccion.matches(DIRECCION_REGEX)) {
            result.addError("La dirección solo puede contener letras, números y los caracteres . , # - "
                    + "(entre " + DIRECCION_MIN_LENGTH + " y " + DIRECCION_MAX_LENGTH + " caracteres).");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación de referencias (ciudad y plan)
    // -------------------------------------------------------------------------

    /**
     * Verifica que la ciudad exista en el catálogo.
     * El control de jerarquía (ciudad → departamento → país) es responsabilidad
     * del servicio, no del validador.
     */
    public ValidationResult validateCiudadExiste(Integer ciudadId) {
        ValidationResult result = new ValidationResult();
        if (ciudadId == null) {
            result.addError("La ciudad es obligatoria.");
            return result;
        }
        if (!ciudadRepository.existsById(ciudadId)) {
            result.addError("La ciudad con id " + ciudadId + " no existe en el catálogo.");
        }
        return result;
    }

    /**
     * Verifica que el plan exista en el catálogo.
     */
    public ValidationResult validatePlanExiste(Integer planId) {
        ValidationResult result = new ValidationResult();
        if (planId == null) {
            result.addError("El plan es obligatorio.");
            return result;
        }
        if (!planRepository.existsById(planId)) {
            result.addError("El plan con id " + planId + " no existe en el catálogo.");
        }
        return result;
    }

    // -------------------------------------------------------------------------
    // Validación combinada para crear/actualizar propietario
    // -------------------------------------------------------------------------

    /**
     * Valida todos los campos del perfil del propietario en una sola llamada.
     * Acumula todos los errores encontrados.
     *
     * @param primerNombre    requerido
     * @param segundoNombre   opcional
     * @param apellido        requerido
     * @param segundoApellido opcional
     * @param telefono        requerido
     * @param direccion       requerida
     * @param ciudadId        requerida, debe existir en BD
     * @param planId          requerido, debe existir en BD
     */
    public ValidationResult validatePropietario(String primerNombre,
                                                String segundoNombre,
                                                String apellido,
                                                String segundoApellido,
                                                String telefono,
                                                String direccion,
                                                Integer ciudadId,
                                                Integer planId) {
        ValidationResult result = new ValidationResult();

        validateNombre(primerNombre, "El primer nombre").getErrors().forEach(result::addError);
        validateNombreOpcional(segundoNombre, "El segundo nombre").getErrors().forEach(result::addError);
        validateNombre(apellido, "El apellido").getErrors().forEach(result::addError);
        validateNombreOpcional(segundoApellido, "El segundo apellido").getErrors().forEach(result::addError);
        validateTelefono(telefono).getErrors().forEach(result::addError);
        validateDireccion(direccion).getErrors().forEach(result::addError);
        validateCiudadExiste(ciudadId).getErrors().forEach(result::addError);
        validatePlanExiste(planId).getErrors().forEach(result::addError);

        return result;
    }
}
