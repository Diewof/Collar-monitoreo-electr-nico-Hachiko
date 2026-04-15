package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;

/**
 * Plan de suscripción disponible para los propietarios.
 * Tabla: plan
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "plan")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Plan {

    @Id
    @Column(name = "plan_id")
    private Integer planId;

    @Column(name = "nombre_plan", length = 30)
    private String nombrePlan;

    @Column(name = "descripcion", length = 255)
    private String descripcion;

    @Column(name = "costo", precision = 6, scale = 2)
    private BigDecimal costo;
}
