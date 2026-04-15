package com.hachiko.portal.repository;

import com.hachiko.portal.domain.Notificacion;
import com.hachiko.portal.domain.enums.EstadoNotificacion;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.List;

/**
 * Contrato de acceso a datos para notificaciones enviadas a propietarios.
 * Módulo: Notificaciones.
 */
public interface INotificacionRepository extends JpaRepository<Notificacion, Long> {

    /**
     * Retorna todas las notificaciones de un propietario, ordenadas por fecha descendente.
     * Usado por el panel del usuario para mostrar el historial de notificaciones.
     */
    List<Notificacion> findByPropietario_PropietarioIdOrderByFechaGeneracionDesc(Integer propietarioId);

    /**
     * Retorna las notificaciones de un propietario filtradas por estado.
     * Permite consultar solo las PENDIENTES o ENVIADAS sin traer todo el historial.
     */
    List<Notificacion> findByPropietario_PropietarioIdAndEstado(
            Integer propietarioId, EstadoNotificacion estado);

    /**
     * Cuenta las notificaciones no leídas de un propietario.
     * Usado para mostrar el badge de notificaciones pendientes en la UI.
     */
    long countByPropietario_PropietarioIdAndEstadoNot(
            Integer propietarioId, EstadoNotificacion estado);

    /**
     * Marca todas las notificaciones pendientes de un propietario como LEIDA.
     * Invocado cuando el usuario abre el panel de notificaciones.
     */
    @Modifying
    @Query("UPDATE Notificacion n SET n.estado = com.hachiko.portal.domain.enums.EstadoNotificacion.LEIDA " +
           "WHERE n.propietario.propietarioId = :propietarioId " +
           "AND n.estado = com.hachiko.portal.domain.enums.EstadoNotificacion.ENVIADA")
    void marcarTodasComoLeidas(@Param("propietarioId") Integer propietarioId);
}
