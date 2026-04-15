package com.hachiko.portal.dto.admin;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.time.LocalDateTime;

/**
 * Representa un evento en la línea de tiempo de actividad reciente del dashboard.
 *
 * Los tipos posibles corresponden a los tres orígenes del PHP original
 * (admin_model.getRecentActivity): LOGIN, REGISTRO, INTENTO_FALLIDO.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ActividadRecienteDTO {

    /** Tipo de evento: "LOGIN", "REGISTRO", "INTENTO_FALLIDO". */
    private String tipo;

    private String email;
    private LocalDateTime momento;
    private String descripcion;
}
