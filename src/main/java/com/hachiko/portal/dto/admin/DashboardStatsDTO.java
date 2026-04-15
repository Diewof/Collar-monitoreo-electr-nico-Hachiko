package com.hachiko.portal.dto.admin;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

import java.util.List;

/**
 * Estadísticas del dashboard de administración.
 *
 * Cada campo corresponde a un método específico de AdminDashboardService
 * (evita el getDashboardData() monolítico del PHP original).
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DashboardStatsDTO {

    private long totalUsuarios;
    private long loginHoy;
    private long intentosFallidosHoy;
    private long cuentasBloqueadas;

    /** Actividad reciente: últimos logins, registros e intentos fallidos mezclados y ordenados. */
    private List<ActividadRecienteDTO> actividadReciente;
}
