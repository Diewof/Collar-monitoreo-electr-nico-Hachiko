package com.hachiko.portal.domain;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Tipo de emoción detectada en el comportamiento de la mascota.
 * Tabla: emocion
 * Dato de referencia: ID asignado manualmente.
 */
@Entity
@Table(name = "emocion")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Emocion {

    @Id
    @Column(name = "emocion_id")
    private Integer emocionId;

    @Column(name = "nombre_emocion", length = 50)
    private String nombreEmocion;
}
