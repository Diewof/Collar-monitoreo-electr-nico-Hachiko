package com.hachiko.portal.dto.mascota;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.math.BigDecimal;
import java.time.LocalDate;

/**
 * Datos para actualizar una mascota existente.
 * Usado en PUT /api/mascotas/{perroId}.
 *
 * Incluye perroId para que MascotaService verifique la propiedad
 * (propietario_id) antes de permitir la modificación.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class UpdateMascotaRequest {

    @NotNull(message = "El id de la mascota es obligatorio.")
    private Integer perroId;

    @NotNull(message = "El propietario es obligatorio.")
    private Integer propietarioId;

    @NotBlank(message = "El nombre de la mascota es obligatorio.")
    private String nombre;

    @NotNull(message = "La fecha de nacimiento es obligatoria.")
    private LocalDate fechaNacimiento;

    @NotNull(message = "El peso es obligatorio.")
    private BigDecimal peso;

    @NotBlank(message = "El género es obligatorio.")
    private String genero;

    private Boolean esterilizado;

    @NotNull(message = "La raza es obligatoria.")
    private Integer razaId;
}
