package com.hachiko.portal.controller;

import com.hachiko.portal.dto.mascota.CreateMascotaRequest;
import com.hachiko.portal.dto.mascota.MascotaDTO;
import com.hachiko.portal.dto.mascota.UpdateMascotaRequest;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.service.IMascotaService;
import com.hachiko.portal.service.IPropietarioService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

/**
 * Controlador REST para la gestión de mascotas.
 *
 * Rutas (todas requieren token JWT válido):
 *   GET    /api/mascotas              → listar mascotas del propietario autenticado
 *   GET    /api/mascotas/{perroId}    → detalle de una mascota
 *   POST   /api/mascotas              → registrar nueva mascota
 *   PUT    /api/mascotas/{perroId}    → actualizar mascota
 *   DELETE /api/mascotas/{perroId}    → eliminar mascota
 *
 * Control de acceso: el propietarioId se resuelve desde el token JWT,
 * garantizando que cada usuario solo accede a sus propias mascotas.
 * El servicio lanza AccessDeniedException si la mascota no pertenece al propietario.
 */
@RestController
@RequestMapping("/api/mascotas")
public class MascotaController {

    private final IMascotaService mascotaService;
    private final IPropietarioService propietarioService;

    public MascotaController(IMascotaService mascotaService,
                             IPropietarioService propietarioService) {
        this.mascotaService = mascotaService;
        this.propietarioService = propietarioService;
    }

    /**
     * Lista todas las mascotas del propietario autenticado, ordenadas por nombre.
     *
     * GET /api/mascotas
     */
    @GetMapping
    public ResponseEntity<List<MascotaDTO>> listMyMascotas(
            @AuthenticationPrincipal Integer userId) {
        Integer propietarioId = getPropietarioId(userId);
        return ResponseEntity.ok(mascotaService.listByPropietario(propietarioId));
    }

    /**
     * Retorna el detalle de una mascota verificando que pertenezca al propietario.
     *
     * GET /api/mascotas/{perroId}
     */
    @GetMapping("/{perroId}")
    public ResponseEntity<MascotaDTO> getMascota(
            @PathVariable Integer perroId,
            @AuthenticationPrincipal Integer userId) {
        Integer propietarioId = getPropietarioId(userId);
        return ResponseEntity.ok(mascotaService.getById(perroId, propietarioId));
    }

    /**
     * Registra una nueva mascota para el propietario autenticado.
     * El propietarioId del token sobreescribe cualquier valor en el request.
     *
     * POST /api/mascotas
     */
    @PostMapping
    public ResponseEntity<MascotaDTO> createMascota(
            @Valid @RequestBody CreateMascotaRequest request,
            @AuthenticationPrincipal Integer userId) {
        Integer propietarioId = getPropietarioId(userId);
        request.setPropietarioId(propietarioId);
        MascotaDTO created = mascotaService.create(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(created);
    }

    /**
     * Actualiza una mascota existente verificando la propiedad.
     *
     * PUT /api/mascotas/{perroId}
     */
    @PutMapping("/{perroId}")
    public ResponseEntity<MascotaDTO> updateMascota(
            @PathVariable Integer perroId,
            @Valid @RequestBody UpdateMascotaRequest request,
            @AuthenticationPrincipal Integer userId) {
        Integer propietarioId = getPropietarioId(userId);
        request.setPerroId(perroId);
        request.setPropietarioId(propietarioId);
        return ResponseEntity.ok(mascotaService.update(request));
    }

    /**
     * Elimina una mascota verificando que pertenece al propietario autenticado.
     *
     * DELETE /api/mascotas/{perroId}
     */
    @DeleteMapping("/{perroId}")
    public ResponseEntity<Void> deleteMascota(
            @PathVariable Integer perroId,
            @AuthenticationPrincipal Integer userId) {
        Integer propietarioId = getPropietarioId(userId);
        mascotaService.delete(perroId, propietarioId);
        return ResponseEntity.noContent().build();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resuelve el propietarioId a partir del userId del token.
     * Lanza ResourceNotFoundException si el usuario aún no tiene perfil.
     */
    private Integer getPropietarioId(Integer userId) {
        PropietarioDTO propietario = propietarioService.getByUserId(userId);
        return propietario.getPropietarioId();
    }
}
