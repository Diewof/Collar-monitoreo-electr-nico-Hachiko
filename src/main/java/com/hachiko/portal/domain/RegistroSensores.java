package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;
import java.time.LocalDateTime;

/**
 * Lectura de sensores registrada por un collar.
 * Tabla: registro_sensores
 * Módulo 5 — Collar y Sensores (mayor volumen de datos esperado; candidato a caché).
 */
@Entity
@Table(name = "registro_sensores")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class RegistroSensores {

    @Id
    @Column(name = "registro_id")
    private Long registroId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "collar_id")
    private Collar collar;

    @Column(name = "decibelios", precision = 4, scale = 2)
    private BigDecimal decibelios;

    @Column(name = "frecuencia", precision = 6, scale = 2)
    private BigDecimal frecuencia;

    @Column(name = "aceleracion_x", precision = 8, scale = 2)
    private BigDecimal aceleracionX;

    @Column(name = "temperatura", precision = 4, scale = 1)
    private BigDecimal temperatura;

    @Column(name = "pulsaciones_min")
    private Integer pulsacionesMin;

    @Column(name = "marca_tiempo")
    private LocalDateTime marcaTiempo;
}
