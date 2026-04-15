package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Tipo de patrón de comportamiento detectado.
 * Tabla: tipo_patron
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "tipo_patron")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class TipoPatron {

    @Id
    @Column(name = "tipo_patron_id")
    private Integer tipoPatronId;

    @Column(name = "nombre_patron", length = 100)
    private String nombrePatron;
}
