package com.hachiko.portal.controller;

import com.hachiko.portal.dto.referencia.CiudadDTO;
import com.hachiko.portal.dto.referencia.DepartamentoDTO;
import com.hachiko.portal.dto.referencia.PaisDTO;
import com.hachiko.portal.dto.referencia.PlanDTO;
import com.hachiko.portal.dto.referencia.RazaDTO;
import com.hachiko.portal.service.IReferenciasService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

/**
 * Controlador REST para datos de referencia (catálogos).
 *
 * Todas las rutas son públicas (configuradas en SecurityConfig) ya que son
 * necesarias para rellenar formularios incluso antes de autenticarse.
 *
 * Rutas:
 *   GET /api/referencia/paises
 *   GET /api/referencia/departamentos?paisId={id}
 *   GET /api/referencia/ciudades?departamentoId={id}
 *   GET /api/referencia/razas
 *   GET /api/referencia/planes
 */
@RestController
@RequestMapping("/api/referencia")
public class ReferenciaController {

    private final IReferenciasService referenciasService;

    public ReferenciaController(IReferenciasService referenciasService) {
        this.referenciasService = referenciasService;
    }

    /**
     * Retorna todos los países disponibles, ordenados alfabéticamente.
     *
     * GET /api/referencia/paises
     */
    @GetMapping("/paises")
    public ResponseEntity<List<PaisDTO>> getPaises() {
        return ResponseEntity.ok(referenciasService.getPaises());
    }

    /**
     * Retorna los departamentos de un país, ordenados alfabéticamente.
     *
     * GET /api/referencia/departamentos?paisId={id}
     */
    @GetMapping("/departamentos")
    public ResponseEntity<List<DepartamentoDTO>> getDepartamentos(
            @RequestParam Integer paisId) {
        return ResponseEntity.ok(referenciasService.getDepartamentosByPais(paisId));
    }

    /**
     * Retorna las ciudades de un departamento, ordenadas alfabéticamente.
     *
     * GET /api/referencia/ciudades?departamentoId={id}
     */
    @GetMapping("/ciudades")
    public ResponseEntity<List<CiudadDTO>> getCiudades(
            @RequestParam Integer departamentoId) {
        return ResponseEntity.ok(referenciasService.getCiudadesByDepartamento(departamentoId));
    }

    /**
     * Retorna todas las razas de perro disponibles, ordenadas alfabéticamente.
     *
     * GET /api/referencia/razas
     */
    @GetMapping("/razas")
    public ResponseEntity<List<RazaDTO>> getRazas() {
        return ResponseEntity.ok(referenciasService.getRazas());
    }

    /**
     * Retorna todos los planes de suscripción disponibles, ordenados por costo ascendente.
     *
     * GET /api/referencia/planes
     */
    @GetMapping("/planes")
    public ResponseEntity<List<PlanDTO>> getPlanes() {
        return ResponseEntity.ok(referenciasService.getPlanes());
    }
}
