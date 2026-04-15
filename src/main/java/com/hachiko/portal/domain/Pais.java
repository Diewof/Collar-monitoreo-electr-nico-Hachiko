package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * País en la jerarquía de ubicación.
 * Tabla: pais
 * Dato de referencia: ID asignado manualmente (no auto-increment).
 */
@Entity
@Table(name = "pais")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Pais {

    @Id
    @Column(name = "pais_id")
    private Integer paisId;

    @Column(name = "nombre", length = 50)
    private String nombre;
}
