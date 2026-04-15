package com.hachiko.portal.service.impl;

import com.hachiko.portal.domain.Usuario;
import com.hachiko.portal.domain.enums.UserRole;
import com.hachiko.portal.dto.admin.ActividadRecienteDTO;
import com.hachiko.portal.dto.admin.DashboardStatsDTO;
import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;
import com.hachiko.portal.exception.ResourceNotFoundException;
import com.hachiko.portal.exception.ValidationException;
import com.hachiko.portal.repository.ILoginAttemptRepository;
import com.hachiko.portal.repository.IPerroRepository;
import com.hachiko.portal.repository.IPropietarioRepository;
import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.IAdminDashboardService;
import com.hachiko.portal.service.IPropietarioService;
import com.hachiko.portal.service.validation.UserValidator;
import com.hachiko.portal.service.validation.ValidationResult;
import org.springframework.data.domain.PageRequest;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;

/**
 * Implementación del servicio del panel de administración.
 * Módulo: Administración.
 *
 * Migra getDashboardStats(), getRecentActivity(), listUsers(), getUserData(),
 * updateUserRole() y deleteUser() de admin_model.php.
 * Cada responsabilidad está en un método separado (SRP).
 */
@Service
public class AdminDashboardServiceImpl implements IAdminDashboardService {

    private static final int LOCK_WINDOW_MINUTES = 15;
    private static final long BLOCKED_THRESHOLD = 3L;
    private static final int ACTIVITY_LIMIT = 10;
    private static final int ACTIVITY_PER_TYPE = 5;

    private final IUsuarioRepository usuarioRepository;
    private final ILoginAttemptRepository loginAttemptRepository;
    private final IPropietarioRepository propietarioRepository;
    private final IPerroRepository perroRepository;
    private final IPropietarioService propietarioService;
    private final UserValidator userValidator;

    public AdminDashboardServiceImpl(IUsuarioRepository usuarioRepository,
                                     ILoginAttemptRepository loginAttemptRepository,
                                     IPropietarioRepository propietarioRepository,
                                     IPerroRepository perroRepository,
                                     IPropietarioService propietarioService,
                                     UserValidator userValidator) {
        this.usuarioRepository = usuarioRepository;
        this.loginAttemptRepository = loginAttemptRepository;
        this.propietarioRepository = propietarioRepository;
        this.perroRepository = perroRepository;
        this.propietarioService = propietarioService;
        this.userValidator = userValidator;
    }

    @Override
    @Transactional(readOnly = true)
    public DashboardStatsDTO getDashboardStats() {
        LocalDateTime startOfToday = LocalDate.now().atStartOfDay();
        LocalDateTime startOfTomorrow = startOfToday.plusDays(1);
        LocalDateTime since24h = LocalDateTime.now().minusHours(24);
        LocalDateTime sinceWindow = LocalDateTime.now().minusMinutes(LOCK_WINDOW_MINUTES);

        // Estadísticas de conteo.
        long totalUsuarios = usuarioRepository.count();
        long loginHoy = usuarioRepository.countByLastLoginBetween(startOfToday, startOfTomorrow);
        long intentosFallidosHoy = loginAttemptRepository.countByAttemptTimeBetween(startOfToday, startOfTomorrow);
        long cuentasBloqueadas = loginAttemptRepository
                .findBlockedEmails(sinceWindow, BLOCKED_THRESHOLD).size();

        // Construir feed de actividad reciente mezclando 3 fuentes.
        List<ActividadRecienteDTO> actividad = new ArrayList<>();

        // Tipo LOGIN: usuarios con login reciente.
        usuarioRepository.findRecentLogins(since24h, PageRequest.of(0, ACTIVITY_PER_TYPE))
                .forEach(u -> actividad.add(ActividadRecienteDTO.builder()
                        .tipo("LOGIN")
                        .email(u.getEmail())
                        .momento(u.getLastLogin())
                        .descripcion("Inicio de sesión exitoso")
                        .build()));

        // Tipo REGISTRO: usuarios registrados recientemente.
        usuarioRepository.findByCreatedAtBetweenOrderByCreatedAtDesc(since24h, startOfTomorrow)
                .stream()
                .limit(ACTIVITY_PER_TYPE)
                .forEach(u -> actividad.add(ActividadRecienteDTO.builder()
                        .tipo("REGISTRO")
                        .email(u.getEmail())
                        .momento(u.getCreatedAt())
                        .descripcion("Nuevo usuario registrado")
                        .build()));

        // Tipo INTENTO_FALLIDO: intentos de login fallidos recientes.
        loginAttemptRepository.findRecentAttempts(since24h, PageRequest.of(0, ACTIVITY_PER_TYPE))
                .forEach(a -> actividad.add(ActividadRecienteDTO.builder()
                        .tipo("INTENTO_FALLIDO")
                        .email(a.getEmail())
                        .momento(a.getAttemptTime())
                        .descripcion("Intento de login fallido desde " + a.getIpAddress())
                        .build()));

        // Mezclar, ordenar por momento DESC y tomar los 10 más recientes.
        List<ActividadRecienteDTO> top10 = actividad.stream()
                .filter(a -> a.getMomento() != null)
                .sorted(Comparator.comparing(ActividadRecienteDTO::getMomento).reversed())
                .limit(ACTIVITY_LIMIT)
                .toList();

        return DashboardStatsDTO.builder()
                .totalUsuarios(totalUsuarios)
                .loginHoy(loginHoy)
                .intentosFallidosHoy(intentosFallidosHoy)
                .cuentasBloqueadas(cuentasBloqueadas)
                .actividadReciente(top10)
                .build();
    }

    @Override
    @Transactional(readOnly = true)
    public List<UsuarioDTO> getAllUsers() {
        return usuarioRepository.findAll()
                .stream()
                .map(this::toUsuarioDTO)
                .toList();
    }

    @Override
    @Transactional(readOnly = true)
    public UserDetailDTO getUserDetail(Integer userId) {
        Usuario usuario = usuarioRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", userId));

        // Obtener perfil de propietario (puede ser null si no lo completó).
        PropietarioDTO propietarioDTO = null;
        long cantidadMascotas = 0;

        if (propietarioRepository.existsByUsuario_Id(userId)) {
            try {
                propietarioDTO = propietarioService.getByUserId(userId);
                if (propietarioDTO != null && propietarioDTO.getPropietarioId() != null) {
                    cantidadMascotas = perroRepository.countByPropietario_PropietarioId(
                            propietarioDTO.getPropietarioId());
                }
            } catch (ResourceNotFoundException ignored) {
                // No tiene perfil — propietarioDTO se queda null
            }
        }

        return UserDetailDTO.builder()
                .usuario(toUsuarioDTO(usuario))
                .propietario(propietarioDTO)
                .cantidadMascotas(cantidadMascotas)
                .build();
    }

    @Override
    @Transactional
    public void updateUserRole(Integer userId, String role) {
        ValidationResult validation = userValidator.validateRole(role);
        if (!validation.isValid()) {
            throw new ValidationException(validation.getErrors());
        }

        if (!usuarioRepository.existsById(userId)) {
            throw new ResourceNotFoundException("Usuario", userId);
        }

        usuarioRepository.updateRole(userId, UserRole.valueOf(role.toUpperCase()));
    }

    @Override
    @Transactional
    public void deleteUser(Integer userId) {
        if (!usuarioRepository.existsById(userId)) {
            throw new ResourceNotFoundException("Usuario", userId);
        }
        usuarioRepository.deleteById(userId);
    }

    // -------------------------------------------------------------------------
    // Mapeo entidad → DTO
    // -------------------------------------------------------------------------

    private UsuarioDTO toUsuarioDTO(Usuario u) {
        return UsuarioDTO.builder()
                .id(u.getId())
                .email(u.getEmail())
                .role(u.getRole() != null ? u.getRole().name() : null)
                .createdAt(u.getCreatedAt())
                .lastLogin(u.getLastLogin())
                .build();
    }
}
