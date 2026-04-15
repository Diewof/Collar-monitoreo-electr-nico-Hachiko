package com.hachiko.portal.dto.admin;

import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Getter;
import lombok.NoArgsConstructor;
import lombok.Setter;

/**
 * Vista detallada de un usuario para el panel de administración.
 * Combina datos del usuario (autenticación) con su perfil de propietario.
 *
 * Corresponde a la query de 6 tablas de getUserData() en admin_controller.php,
 * ahora resuelta a través de dos repositorios independientes en AdminDashboardService.
 */
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class UserDetailDTO {

    private UsuarioDTO usuario;

    /**
     * Perfil de propietario del usuario.
     * Puede ser null si el usuario nunca completó el registro de propietario.
     */
    private PropietarioDTO propietario;

    private long cantidadMascotas;
}
