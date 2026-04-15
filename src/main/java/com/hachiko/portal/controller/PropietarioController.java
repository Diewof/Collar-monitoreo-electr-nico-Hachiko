package com.hachiko.portal.controller;

import com.hachiko.portal.dto.propietario.CreatePropietarioRequest;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.propietario.UpdatePropietarioRequest;
import com.hachiko.portal.service.IPropietarioService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Controlador REST para el módulo de perfil de propietario.
 *
 * Rutas (todas requieren token JWT válido):
 *   GET  /api/propietario/me             → perfil del usuario autenticado
 *   POST /api/propietario                → crear perfil por primera vez
 *   PUT  /api/propietario/{propietarioId} → actualizar perfil existente
 *
 * El userId se extrae del SecurityContext (cargado por JwtAuthenticationFilter),
 * por lo que el frontend no necesita enviarlo explícitamente en el body.
 */
@RestController
@RequestMapping("/api/propietario")
public class PropietarioController {

    private final IPropietarioService propietarioService;

    public PropietarioController(IPropietarioService propietarioService) {
        this.propietarioService = propietarioService;
    }

    /**
     * Retorna el perfil completo del propietario del usuario autenticado.
     * Incluye residencia con jerarquía ciudad → departamento → país.
     *
     * GET /api/propietario/me
     */
    @GetMapping("/me")
    public ResponseEntity<PropietarioDTO> getMyProfile(
            @AuthenticationPrincipal Integer userId) {
        PropietarioDTO profile = propietarioService.getByUserId(userId);
        return ResponseEntity.ok(profile);
    }

    /**
     * Crea el perfil de propietario por primera vez.
     * El userId del token se inyecta en el request para garantizar que
     * el usuario solo pueda crear su propio perfil.
     *
     * POST /api/propietario
     */
    @PostMapping
    public ResponseEntity<PropietarioDTO> createProfile(
            @Valid @RequestBody CreatePropietarioRequest request,
            @AuthenticationPrincipal Integer userId) {
        request.setUsuarioId(userId);
        PropietarioDTO created = propietarioService.create(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(created);
    }

    /**
     * Actualiza el perfil de propietario existente.
     *
     * PUT /api/propietario/{propietarioId}
     */
    @PutMapping("/{propietarioId}")
    public ResponseEntity<PropietarioDTO> updateProfile(
            @PathVariable Integer propietarioId,
            @Valid @RequestBody UpdatePropietarioRequest request) {
        request.setPropietarioId(propietarioId);
        PropietarioDTO updated = propietarioService.update(request);
        return ResponseEntity.ok(updated);
    }
}
