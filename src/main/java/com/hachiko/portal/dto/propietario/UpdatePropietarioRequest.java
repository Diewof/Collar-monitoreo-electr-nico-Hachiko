package com.hachiko.portal.dto.propietario;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Datos para actualizar el perfil de propietario existente.
 * Usado en PUT /api/propietario/{propietarioId}.
 *
 * Incluye propietarioId para que PropietarioService pueda verificar que
 * el recurso pertenece al usuario autenticado antes de modificarlo.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class UpdatePropietarioRequest {

    @NotNull(message = "El id de propietario es obligatorio.")
    private Integer propietarioId;

    @NotBlank(message = "El primer nombre es obligatorio.")
    private String primerNombre;

    private String segundoNombre;

    @NotBlank(message = "El apellido es obligatorio.")
    private String apellido;

    private String segundoApellido;

    @NotBlank(message = "El teléfono es obligatorio.")
    private String telefono;

    private String emailContacto;

    @NotBlank(message = "La dirección es obligatoria.")
    private String direccion;

    @NotNull(message = "La ciudad es obligatoria.")
    private Integer ciudadId;

    @NotNull(message = "El plan es obligatorio.")
    private Integer planId;
}
