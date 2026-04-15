package com.hachiko.portal.dto.referencia;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/** Catálogo de países — datos de solo lectura. */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class PaisDTO {

    private Integer paisId;
    private String nombre;
}
