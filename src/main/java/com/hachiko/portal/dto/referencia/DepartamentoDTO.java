package com.hachiko.portal.dto.referencia;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/** Catálogo de departamentos — datos de solo lectura. */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DepartamentoDTO {

    private Integer departamentoId;
    private String nombre;
    private Integer paisId;
}
