package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Raza de perro (catálogo de referencia).
 * Tabla: raza
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "raza")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Raza {

    @Id
    @Column(name = "raza_id")
    private Integer razaId;

    @Column(name = "nombre_raza", length = 50)
    private String nombreRaza;

    @Column(name = "predisposicion_problemas_conducta", length = 255)
    private String predisposicionProblemasConducta;
}
