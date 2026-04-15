package com.hachiko.portal.dto.referencia;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/** Catálogo de ciudades — datos de solo lectura. */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class CiudadDTO {

    private Integer ciudadId;
    private String nombre;
    private Integer departamentoId;
}
