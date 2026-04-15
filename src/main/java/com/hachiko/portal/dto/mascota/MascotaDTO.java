package com.hachiko.portal.dto.mascota;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;
import java.time.LocalDate;

/**
 * Representación de una mascota (perro) para respuestas de la API.
 * Incluye el nombre de la raza para evitar una segunda llamada al cliente.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class MascotaDTO {

    private Integer perroId;
    private String nombre;
    private LocalDate fechaNacimiento;
    private BigDecimal peso;

    /** "M" o "F" */
    private String genero;
    private Boolean esterilizado;

    private Integer razaId;
    private String nombreRaza;

    private Integer propietarioId;
}
