package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Sugerencia de manejo etológico asociada a una emoción y un medio.
 * Tabla: sugerencia_etologica
 */
@Entity
@Table(name = "sugerencia_etologica")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class SugerenciaEtologica {

    @Id
    @Column(name = "sug_id")
    private Integer sugId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "emocion_id")
    private Emocion emocion;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "medio_id")
    private Medio medio;

    @Column(name = "contenido", columnDefinition = "TEXT")
    private String contenido;
}
