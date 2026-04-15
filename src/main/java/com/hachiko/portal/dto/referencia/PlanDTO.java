package com.hachiko.portal.dto.referencia;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;

/** Catálogo de planes de suscripción — datos de solo lectura. */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class PlanDTO {

    private Integer planId;
    private String nombrePlan;
    private String descripcion;
    private BigDecimal costo;
}
