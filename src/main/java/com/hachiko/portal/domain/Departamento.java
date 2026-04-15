package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Departamento/Estado en la jerarquía de ubicación.
 * Tabla: departamento
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "departamento")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Departamento {

    @Id
    @Column(name = "departamento_id")
    private Integer departamentoId;

    @Column(name = "nombre", length = 50)
    private String nombre;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "pais_id")
    private Pais pais;
}
