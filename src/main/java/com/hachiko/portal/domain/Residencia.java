package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Residencia (dirección) asociada a un propietario.
 * Tabla: residencia
 */
@Entity
@Table(name = "residencia")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Residencia {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "residencia_id")
    private Integer residenciaId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "ciudad_id")
    private Ciudad ciudad;

    @Column(name = "direccion", length = 100)
    private String direccion;
}
