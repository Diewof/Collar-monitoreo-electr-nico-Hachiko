package com.hachiko.portal.dto.referencia;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/** Catálogo de razas — datos de solo lectura. */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class RazaDTO {

    private Integer razaId;
    private String nombreRaza;
}
