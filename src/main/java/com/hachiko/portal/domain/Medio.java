package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Medio multimedia asociado a sugerencias etológicas.
 * Tabla: medio
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "medio")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Medio {

    @Id
    @Column(name = "medio_id")
    private Integer medioId;

    @Column(name = "tipo_medio", length = 100)
    private String tipoMedio;

    @Column(name = "ruta", length = 45)
    private String ruta;
}
