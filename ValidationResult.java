package com.hachiko.portal.service.validation;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

/**
 * Resultado estructurado de una operación de validación.
 *
 * Los validadores del sistema retornan esta clase en lugar de lanzar
 * excepciones genéricas, lo que permite acumular múltiples errores en
 * una sola pasada y retornarlos todos al cliente en una sola respuesta.
 *
 * Uso:
 * <pre>
 *   ValidationResult result = userValidator.validateNewUser(email, password);
 *   if (!result.isValid()) {
 *       // manejar result.getErrors()
 *   }
 * </pre>
 */
public class ValidationResult {

    private final List<String> errors = new ArrayList<>();

    /**
     * Agrega un mensaje de error a la lista de errores.
     *
     * @param error descripción del problema de validación
     */
    public void addError(String error) {
        errors.add(error);
    }

    /**
     * Retorna {@code true} si no se encontró ningún error de validación.
     */
    public boolean isValid() {
        return errors.isEmpty();
    }

    /**
     * Retorna la lista de errores de validación encontrados.
     * La lista es inmutable para evitar modificaciones externas.
     */
    public List<String> getErrors() {
        return Collections.unmodifiableList(errors);
    }

    /**
     * Retorna el primer error de la lista, o una cadena vacía si no hay errores.
     * Útil cuando el contexto solo puede mostrar un mensaje a la vez.
     */
    public String getFirstError() {
        return errors.isEmpty() ? "" : errors.get(0);
    }
}
