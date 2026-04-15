package com.hachiko.portal.dto.propietario;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Datos para crear el perfil de propietario por primera vez.
 * Usado en POST /api/propietario.
 *
 * La validación de formato (nombres, teléfono, dirección) se realiza
 * en PropietarioValidator, que es invocado por PropietarioService.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class CreatePropietarioRequest {

    @NotNull(message = "El id de usuario es obligatorio.")
    private Integer usuarioId;

    @NotBlank(message = "El primer nombre es obligatorio.")
    private String primerNombre;

    private String segundoNombre;

    @NotBlank(message = "El apellido es obligatorio.")
    private String apellido;

    private String segundoApellido;

    @NotBlank(message = "El teléfono es obligatorio.")
    private String telefono;

    /** Email de contacto del propietario (puede diferir del email de login). */
    private String emailContacto;

    @NotBlank(message = "La dirección es obligatoria.")
    private String direccion;

    @NotNull(message = "La ciudad es obligatoria.")
    private Integer ciudadId;

    @NotNull(message = "El plan es obligatorio.")
    private Integer planId;
}
