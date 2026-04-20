package com.hachiko.portal.controller;

import com.hachiko.portal.dto.admin.DashboardStatsDTO;
import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.auth.RegisterRequest;
import com.hachiko.portal.dto.usuario.UsuarioDTO;
import com.hachiko.portal.service.IAdminDashboardService;
import com.hachiko.portal.service.IAdminUserService;
import com.hachiko.portal.service.IRegisterService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;
import java.util.Map;

/**
 * Controlador REST para el panel de administración.
 *
 * Todas las rutas bajo /api/admin/** requieren rol ADMIN
 * (configurado en SecurityConfig con hasRole("ADMIN")).
 *
 * Rutas:
 *   GET    /api/admin/stats                    → estadísticas del dashboard
 *   GET    /api/admin/usuarios                 → lista de todos los usuarios
 *   GET    /api/admin/usuarios/{userId}        → detalle de un usuario
 *   POST   /api/admin/usuarios                 → crear usuario desde panel admin
 *   PUT    /api/admin/usuarios/{userId}/role   → cambiar rol de usuario
 *   DELETE /api/admin/usuarios/{userId}        → eliminar usuario
 */
@RestController
@RequestMapping("/api/admin")
public class AdminController {

    private final IAdminDashboardService adminDashboardService;
    private final IAdminUserService adminUserService;
    private final IRegisterService registerService;

    public AdminController(IAdminDashboardService adminDashboardService,
                           IAdminUserService adminUserService,
                           IRegisterService registerService) {
        this.adminDashboardService = adminDashboardService;
        this.adminUserService = adminUserService;
        this.registerService = registerService;
    }

    /**
     * Retorna estadísticas del dashboard y feed de actividad reciente.
     *
     * GET /api/admin/stats
     */
    @GetMapping("/stats")
    public ResponseEntity<DashboardStatsDTO> getDashboardStats() {
        return ResponseEntity.ok(adminDashboardService.getDashboardStats());
    }

    /**
     * Retorna la lista de todos los usuarios registrados en el sistema.
     *
     * GET /api/admin/usuarios
     */
    @GetMapping("/usuarios")
    public ResponseEntity<List<UsuarioDTO>> getAllUsers() {
        return ResponseEntity.ok(adminUserService.getAllUsers());
    }

    /**
     * Retorna el detalle completo de un usuario: cuenta, perfil propietario y mascotas.
     *
     * GET /api/admin/usuarios/{userId}
     */
    @GetMapping("/usuarios/{userId}")
    public ResponseEntity<UserDetailDTO> getUserDetail(@PathVariable Integer userId) {
        return ResponseEntity.ok(adminUserService.getUserDetail(userId));
    }

    /**
     * Crea un nuevo usuario desde el panel de administración.
     * Reutiliza el mismo IRegisterService que el registro público.
     *
     * POST /api/admin/usuarios
     */
    @PostMapping("/usuarios")
    public ResponseEntity<UsuarioDTO> createUser(@Valid @RequestBody RegisterRequest request) {
        UsuarioDTO created = registerService.register(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(created);
    }

    /**
     * Actualiza el rol de un usuario existente.
     * El body debe contener {"role": "ADMIN"} o {"role": "USER"}.
     *
     * PUT /api/admin/usuarios/{userId}/role
     */
    @PutMapping("/usuarios/{userId}/role")
    public ResponseEntity<Map<String, String>> updateUserRole(
            @PathVariable Integer userId,
            @RequestBody Map<String, String> body) {
        String role = body.get("role");
        adminUserService.updateUserRole(userId, role);
        return ResponseEntity.ok(Map.of("message", "Rol actualizado correctamente."));
    }

    /**
     * Elimina permanentemente un usuario y sus datos relacionados (cascada en BD).
     *
     * DELETE /api/admin/usuarios/{userId}
     */
    @DeleteMapping("/usuarios/{userId}")
    public ResponseEntity<Void> deleteUser(@PathVariable Integer userId) {
        adminUserService.deleteUser(userId);
        return ResponseEntity.noContent().build();
    }
}
