package com.hachiko.portal.domain;

import com.hachiko.portal.domain.enums.Genero;
import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;
import java.time.LocalDate;

/**
 * Mascota (perro) registrada en el sistema, vinculada a un propietario.
 * Tabla: perro
 */
@Entity
@Table(name = "perro")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Perro {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "perro_id")
    private Integer perroId;

    @Column(name = "nombre", length = 50)
    private String nombre;

    @Column(name = "fechanacimiento")
    private LocalDate fechaNacimiento;

    @Column(name = "peso", precision = 4, scale = 2)
    private BigDecimal peso;

    @Enumerated(EnumType.STRING)
    @Column(name = "genero", length = 1)
    private Genero genero;

    @Column(name = "esterilizado")
    private Boolean esterilizado;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "propietario_id")
    private Propietario propietario;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "raza_id")
    private Raza raza;
}
