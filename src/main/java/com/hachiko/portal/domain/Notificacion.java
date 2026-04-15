package com.hachiko.portal.domain;

import com.hachiko.portal.domain.enums.EstadoNotificacion;
import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.time.LocalDateTime;

/**
 * Notificación enviada a un propietario.
 * Tabla: notificacion
 */
@Entity
@Table(name = "notificacion")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Notificacion {

    @Id
    @Column(name = "notificacion_id")
    private Long notificacionId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "propietario_id")
    private Propietario propietario;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "tipo_notificacion_id")
    private TipoNotificacion tipoNotificacion;

    @Column(name = "mensaje", length = 500)
    private String mensaje;

    @Enumerated(EnumType.STRING)
    @Column(name = "estado", length = 10)
    private EstadoNotificacion estado;

    @Column(name = "fecha_generacion")
    private LocalDateTime fechaGeneracion;
}
