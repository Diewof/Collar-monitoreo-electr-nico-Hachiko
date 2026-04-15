package com.hachiko.portal.dto.propietario;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Representación completa del perfil de propietario para respuestas de la API.
 * Incluye la residencia aplanada para que el frontend no necesite una
 * segunda llamada para obtener ubicación.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class PropietarioDTO {

    private Integer propietarioId;
    private Integer usuarioId;

    private String primerNombre;
    private String segundoNombre;
    private String apellido;
    private String segundoApellido;
    private String telefono;
    private String email;

    private Integer planId;
    private String planNombre;

    /** Residencia aplanada: ciudad, departamento y país incluidos. */
    private ResidenciaDTO residencia;
}
