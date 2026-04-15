package com.hachiko.portal.service.impl;

import com.hachiko.portal.domain.Propietario;
import com.hachiko.portal.domain.Residencia;
import com.hachiko.portal.domain.Ciudad;
import com.hachiko.portal.domain.Plan;
import com.hachiko.portal.domain.Usuario;
import com.hachiko.portal.dto.propietario.CreatePropietarioRequest;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.propietario.ResidenciaDTO;
import com.hachiko.portal.dto.propietario.UpdatePropietarioRequest;
import com.hachiko.portal.exception.DuplicateResourceException;
import com.hachiko.portal.exception.ResourceNotFoundException;
import com.hachiko.portal.exception.ValidationException;
import com.hachiko.portal.repository.ICiudadRepository;
import com.hachiko.portal.repository.IPlanRepository;
import com.hachiko.portal.repository.IPropietarioRepository;
import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.IPropietarioService;
import com.hachiko.portal.service.validation.PropietarioValidator;
import com.hachiko.portal.service.validation.ValidationResult;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/**
 * Implementación del servicio de gestión de perfil de propietario.
 * Módulo: Propietario.
 *
 * Migra savePropietario(), updatePropietario() y getPropietarioByUserId()
 * de propietario_model.php. El CascadeType.ALL en Propietario.residencia
 * garantiza que Residencia se persiste/actualiza en la misma transacción.
 */
@Service
public class PropietarioServiceImpl implements IPropietarioService {

    private final IPropietarioRepository propietarioRepository;
    private final IUsuarioRepository usuarioRepository;
    private final ICiudadRepository ciudadRepository;
    private final IPlanRepository planRepository;
    private final PropietarioValidator propietarioValidator;

    public PropietarioServiceImpl(IPropietarioRepository propietarioRepository,
                                  IUsuarioRepository usuarioRepository,
                                  ICiudadRepository ciudadRepository,
                                  IPlanRepository planRepository,
                                  PropietarioValidator propietarioValidator) {
        this.propietarioRepository = propietarioRepository;
        this.usuarioRepository = usuarioRepository;
        this.ciudadRepository = ciudadRepository;
        this.planRepository = planRepository;
        this.propietarioValidator = propietarioValidator;
    }

    @Override
    @Transactional(readOnly = true)
    public PropietarioDTO getByUserId(Integer userId) {
        Propietario propietario = propietarioRepository.findByUsuario_Id(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Perfil de propietario", userId));
        return toDTO(propietario);
    }

    @Override
    @Transactional
    public PropietarioDTO create(CreatePropietarioRequest request) {
        Integer usuarioId = request.getUsuarioId();

        // Verificar que el usuario no tenga ya un perfil.
        if (propietarioRepository.existsByUsuario_Id(usuarioId)) {
            throw new DuplicateResourceException("El usuario ya tiene un perfil de propietario registrado.");
        }

        // Validar todos los campos del perfil.
        ValidationResult validation = propietarioValidator.validatePropietario(
                request.getPrimerNombre(),
                request.getSegundoNombre(),
                request.getApellido(),
                request.getSegundoApellido(),
                request.getTelefono(),
                request.getDireccion(),
                request.getCiudadId(),
                request.getPlanId()
        );
        if (!validation.isValid()) {
            throw new ValidationException(validation.getErrors());
        }

        // Cargar entidades relacionadas.
        Usuario usuario = usuarioRepository.findById(usuarioId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", usuarioId));
        Ciudad ciudad = ciudadRepository.findById(request.getCiudadId())
                .orElseThrow(() -> new ResourceNotFoundException("Ciudad", request.getCiudadId()));
        Plan plan = planRepository.findById(request.getPlanId())
                .orElseThrow(() -> new ResourceNotFoundException("Plan", request.getPlanId()));

        // Construir Residencia (se persistirá en cascada).
        Residencia residencia = Residencia.builder()
                .ciudad(ciudad)
                .direccion(request.getDireccion())
                .build();

        // Construir y persistir Propietario.
        Propietario propietario = Propietario.builder()
                .usuario(usuario)
                .primerNombre(request.getPrimerNombre())
                .segundoNombre(request.getSegundoNombre())
                .apellido(request.getApellido())
                .segundoApellido(request.getSegundoApellido())
                .telefono(request.getTelefono())
                .email(request.getEmailContacto())
                .residencia(residencia)
                .plan(plan)
                .build();

        Propietario saved = propietarioRepository.save(propietario);
        return toDTO(saved);
    }

    @Override
    @Transactional
    public PropietarioDTO update(UpdatePropietarioRequest request) {
        Propietario propietario = propietarioRepository.findById(request.getPropietarioId())
                .orElseThrow(() -> new ResourceNotFoundException("Propietario", request.getPropietarioId()));

        // Validar los campos del perfil.
        ValidationResult validation = propietarioValidator.validatePropietario(
                request.getPrimerNombre(),
                request.getSegundoNombre(),
                request.getApellido(),
                request.getSegundoApellido(),
                request.getTelefono(),
                request.getDireccion(),
                request.getCiudadId(),
                request.getPlanId()
        );
        if (!validation.isValid()) {
            throw new ValidationException(validation.getErrors());
        }

        // Cargar entidades relacionadas.
        Ciudad ciudad = ciudadRepository.findById(request.getCiudadId())
                .orElseThrow(() -> new ResourceNotFoundException("Ciudad", request.getCiudadId()));
        Plan plan = planRepository.findById(request.getPlanId())
                .orElseThrow(() -> new ResourceNotFoundException("Plan", request.getPlanId()));

        // Actualizar Residencia (managed — se actualizará en cascada).
        Residencia residencia = propietario.getResidencia();
        if (residencia == null) {
            residencia = new Residencia();
            propietario.setResidencia(residencia);
        }
        residencia.setCiudad(ciudad);
        residencia.setDireccion(request.getDireccion());

        // Actualizar campos del Propietario.
        propietario.setPrimerNombre(request.getPrimerNombre());
        propietario.setSegundoNombre(request.getSegundoNombre());
        propietario.setApellido(request.getApellido());
        propietario.setSegundoApellido(request.getSegundoApellido());
        propietario.setTelefono(request.getTelefono());
        propietario.setEmail(request.getEmailContacto());
        propietario.setPlan(plan);

        Propietario saved = propietarioRepository.save(propietario);
        return toDTO(saved);
    }

    // -------------------------------------------------------------------------
    // Mapeo entidad → DTO
    // IMPORTANTE: solo llamar dentro de un contexto @Transactional activo
    // porque accede a relaciones LAZY (residencia → ciudad → departamento → pais).
    // -------------------------------------------------------------------------

    private PropietarioDTO toDTO(Propietario p) {
        ResidenciaDTO residenciaDTO = null;
        if (p.getResidencia() != null) {
            Residencia r = p.getResidencia();

            Integer ciudadId = null, deptId = null, paisId = null;
            String ciudadNombre = null, deptNombre = null, paisNombre = null;

            if (r.getCiudad() != null) {
                ciudadId = r.getCiudad().getCiudadId();
                ciudadNombre = r.getCiudad().getNombre();

                if (r.getCiudad().getDepartamento() != null) {
                    deptId = r.getCiudad().getDepartamento().getDepartamentoId();
                    deptNombre = r.getCiudad().getDepartamento().getNombre();

                    if (r.getCiudad().getDepartamento().getPais() != null) {
                        paisId = r.getCiudad().getDepartamento().getPais().getPaisId();
                        paisNombre = r.getCiudad().getDepartamento().getPais().getNombre();
                    }
                }
            }

            residenciaDTO = ResidenciaDTO.builder()
                    .residenciaId(r.getResidenciaId())
                    .direccion(r.getDireccion())
                    .ciudadId(ciudadId)
                    .ciudadNombre(ciudadNombre)
                    .departamentoId(deptId)
                    .departamentoNombre(deptNombre)
                    .paisId(paisId)
                    .paisNombre(paisNombre)
                    .build();
        }

        Integer planId = null;
        String planNombre = null;
        if (p.getPlan() != null) {
            planId = p.getPlan().getPlanId();
            planNombre = p.getPlan().getNombrePlan();
        }

        return PropietarioDTO.builder()
                .propietarioId(p.getPropietarioId())
                .usuarioId(p.getUsuario() != null ? p.getUsuario().getId() : null)
                .primerNombre(p.getPrimerNombre())
                .segundoNombre(p.getSegundoNombre())
                .apellido(p.getApellido())
                .segundoApellido(p.getSegundoApellido())
                .telefono(p.getTelefono())
                .email(p.getEmail())
                .planId(planId)
                .planNombre(planNombre)
                .residencia(residenciaDTO)
                .build();
    }
}
