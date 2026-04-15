package com.hachiko.portal.dto.propietario;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Datos de residencia aplanados (sin anidamiento de entidades JPA).
 * Incluye nombres de ciudad, departamento y país para evitar lazy-load
 * en los controladores.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ResidenciaDTO {

    private Integer residenciaId;
    private String direccion;

    private Integer ciudadId;
    private String ciudadNombre;

    private Integer departamentoId;
    private String departamentoNombre;

    private Integer paisId;
    private String paisNombre;
}
