package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.time.LocalDate;

/**
 * Collar físico vinculado a una mascota.
 * Tabla: collar
 * Módulo 5 — Collar y Sensores (schema base; lógica a implementar en etapas posteriores).
 */
@Entity
@Table(name = "collar")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Collar {

    @Id
    @Column(name = "collar_id")
    private Integer collarId;

    @OneToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "perro_id")
    private Perro perro;

    @Column(name = "version_firmware", length = 20)
    private String versionFirmware;

    @Column(name = "fecha_fabricacion")
    private LocalDate fechaFabricacion;

    @Column(name = "fecha_instalacion")
    private LocalDate fechaInstalacion;

    @Column(name = "bateria")
    private Integer bateria;
}
