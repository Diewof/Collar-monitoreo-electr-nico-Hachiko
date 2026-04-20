package com.hachiko.portal.handler;

import com.hachiko.portal.exception.PortalException;
import com.hachiko.portal.exception.ValidationException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;

import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

/**
 * Manejador global de excepciones para la API REST del portal Hachiko.
 *
 * Centraliza la traducción de excepciones de dominio a respuestas HTTP,
 * evitando bloques try-catch en los controladores (SRP).
 *
 * Jerarquía de captura (orden importa — de más específica a más genérica):
 *  1. ValidationException   → 422 con lista de errores
 *  2. PortalException       → usa HttpStatus del objeto (cubre todas las subclases)
 *  3. Exception (fallback)  → 500 sin detalle interno
 *
 * Seguridad: nunca se expone el stacktrace al cliente.
 * El stacktrace se registra en el logger del servidor para debugging.
 */
@RestControllerAdvice
public class GlobalExceptionHandler {

    private static final Logger log = LoggerFactory.getLogger(GlobalExceptionHandler.class);

    /**
     * Maneja ValidationException: devuelve el primer mensaje + lista completa de errores.
     * HTTP 422 Unprocessable Entity.
     */
    @ExceptionHandler(ValidationException.class)
    public ResponseEntity<Map<String, Object>> handleValidation(ValidationException ex) {
        log.debug("Validation error: {}", ex.getErrors());
        return ResponseEntity
                .status(ex.getStatus())
                .body(errorBody(ex.getMessage(), ex.getErrors()));
    }

    /**
     * Maneja errores de validación de @Valid en el controlador (@NotBlank, @Email, @Size, etc.).
     * HTTP 422 — extrae todos los mensajes de campo y los retorna como lista.
     */
    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<Map<String, Object>> handleMethodArgumentNotValid(MethodArgumentNotValidException ex) {
        List<String> errors = ex.getBindingResult().getFieldErrors().stream()
                .map(fe -> fe.getDefaultMessage())
                .collect(Collectors.toList());
        String first = errors.isEmpty() ? "Error de validación." : errors.get(0);
        log.debug("Bean validation errors: {}", errors);
        return ResponseEntity
                .status(HttpStatus.UNPROCESSABLE_ENTITY)
                .body(errorBody(first, errors));
    }

    /**
     * Maneja cualquier subclase de PortalException usando su HttpStatus propio.
     * Cubre: AuthenticationException (401), AccountLockedException (429),
     *        DuplicateResourceException (409), ResourceNotFoundException (404),
     *        AccessDeniedException (403), InvalidTokenException (401).
     */
    @ExceptionHandler(PortalException.class)
    public ResponseEntity<Map<String, Object>> handlePortalException(PortalException ex) {
        log.debug("Portal exception [{}]: {}", ex.getStatus(), ex.getMessage());
        return ResponseEntity
                .status(ex.getStatus())
                .body(errorBody(ex.getMessage(), Collections.emptyList()));
    }

    /**
     * Fallback para cualquier excepción no controlada.
     * HTTP 500 Internal Server Error — sin detalle interno al cliente.
     */
    @ExceptionHandler(Exception.class)
    public ResponseEntity<Map<String, Object>> handleGeneric(Exception ex) {
        log.error("Unhandled exception", ex);
        return ResponseEntity
                .status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(errorBody("Error interno del servidor.", Collections.emptyList()));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private Map<String, Object> errorBody(String message, List<String> errors) {
        Map<String, Object> body = new HashMap<>();
        body.put("error", message);
        if (!errors.isEmpty()) {
            body.put("errors", errors);
        }
        return body;
    }
}
