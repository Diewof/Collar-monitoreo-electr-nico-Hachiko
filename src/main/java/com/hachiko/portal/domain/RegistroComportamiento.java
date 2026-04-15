package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;
import java.time.LocalTime;

/**
 * Registro de un patrón de comportamiento inferido a partir de lecturas de sensores.
 * Tabla: registro_comportamiento
 * Módulo 5 — Collar y Sensores.
 */
@Entity
@Table(name = "registro_comportamiento")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class RegistroComportamiento {

    @Id
    @Column(name = "registro_id")
    private Long registroId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "collar_id")
    private Collar collar;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "emocion_id")
    private Emocion emocion;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "tipo_patron_id")
    private TipoPatron tipoPatron;

    @Column(name = "certeza", precision = 3, scale = 0)
    private BigDecimal certeza;

    @Column(name = "hora_inicio")
    private LocalTime horaInicio;

    @Column(name = "duracion")
    private Integer duracion;
}
