package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Categoría de notificación del sistema.
 * Tabla: tipo_notificacion
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "tipo_notificacion")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class TipoNotificacion {

    @Id
    @Column(name = "tipo_notificacion_id")
    private Integer tipoNotificacionId;

    @Column(name = "nombre_tipo", length = 30)
    private String nombreTipo;
}
