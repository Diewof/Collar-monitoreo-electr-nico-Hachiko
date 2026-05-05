package com.hachiko.portal.controller;

import com.hachiko.portal.dto.auth.LoginRequest;
import com.hachiko.portal.dto.auth.LoginResponse;
import com.hachiko.portal.dto.auth.NewPasswordRequest;
import com.hachiko.portal.dto.auth.PasswordResetRequest;
import com.hachiko.portal.dto.auth.RegisterRequest;
import com.hachiko.portal.dto.usuario.UsuarioDTO;
import com.hachiko.portal.security.IJwtTokenProvider;
import com.hachiko.portal.service.ILoginService;
import com.hachiko.portal.service.ILogoutService;
import com.hachiko.portal.service.IPasswordResetService;
import com.hachiko.portal.service.IRegisterService;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.Map;

/**
 * Controlador REST para el módulo de autenticación.
 *
 * Rutas públicas (configuradas en SecurityConfig):
 *   POST /api/auth/login
 *   POST /api/auth/register
 *   POST /api/auth/forgot-password
 *   POST /api/auth/reset-password
 *
 * Ruta protegida (requiere token JWT válido):
 *   POST /api/auth/logout
 *
 * Principio SRP: solo orquesta peticiones HTTP — ninguna lógica de negocio aquí.
 * El token JWT se genera aquí (no en el servicio) para mantener la capa de
 * servicio independiente de la infraestructura de seguridad HTTP.
 */
@RestController
@RequestMapping("/api/auth")
public class AuthController {

    private final ILoginService loginService;
    private final IRegisterService registerService;
    private final ILogoutService logoutService;
    private final IPasswordResetService passwordResetService;
    private final IJwtTokenProvider jwtTokenProvider;

    public AuthController(ILoginService loginService,
                          IRegisterService registerService,
                          ILogoutService logoutService,
                          IPasswordResetService passwordResetService,
                          IJwtTokenProvider jwtTokenProvider) {
        this.loginService = loginService;
        this.registerService = registerService;
        this.logoutService = logoutService;
        this.passwordResetService = passwordResetService;
        this.jwtTokenProvider = jwtTokenProvider;
    }

    /**
     * Autentica un usuario y retorna un JWT.
     *
     * El servicio verifica credenciales, bloqueo de cuenta y perfil incompleto.
     * El controlador agrega el token JWT generado a la respuesta.
     *
     * POST /api/auth/login
     */
    @PostMapping("/login")
    public ResponseEntity<LoginResponse> login(@Valid @RequestBody LoginRequest request,
                                               HttpServletRequest httpRequest) {
        String ipAddress = extractIp(httpRequest);
        LoginResponse response = loginService.login(request, ipAddress);

        String token = jwtTokenProvider.generateToken(
                response.getUserId(),
                response.getEmail(),
                response.getRole());
        response.setToken(token);
        response.setExpiresIn(jwtTokenProvider.getExpirationMs() / 1000);

        return ResponseEntity.ok(response);
    }

    /**
     * Registra un nuevo usuario en el sistema.
     *
     * POST /api/auth/register
     */
    @PostMapping("/register")
    public ResponseEntity<UsuarioDTO> register(@Valid @RequestBody RegisterRequest request) {
        UsuarioDTO created = registerService.register(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(created);
    }

    /**
     * Cierra la sesión del usuario autenticado: revoca el JWT y limpia intentos de login.
     *
     * POST /api/auth/logout
     */
    @PostMapping("/logout")
    public ResponseEntity<Map<String, String>> logout(
            @AuthenticationPrincipal Integer userId,
            HttpServletRequest httpRequest) {
        String token = extractTokenFromRequest(httpRequest);
        logoutService.logout(userId, extractIp(httpRequest), token);
        return ResponseEntity.ok(Map.of("message", "Has cerrado sesión correctamente."));
    }

    /**
     * Solicita un token de recuperación de contraseña enviado por email.
     * Responde siempre 200 OK (no revela si el email existe o no).
     *
     * POST /api/auth/forgot-password
     */
    @PostMapping("/forgot-password")
    public ResponseEntity<Map<String, String>> forgotPassword(
            @Valid @RequestBody PasswordResetRequest request) {
        passwordResetService.requestPasswordReset(request);
        return ResponseEntity.ok(Map.of("message",
                "Si el email existe, recibirás las instrucciones de recuperación."));
    }

    /**
     * Aplica el cambio de contraseña usando el token recibido por email.
     *
     * POST /api/auth/reset-password
     */
    @PostMapping("/reset-password")
    public ResponseEntity<Map<String, String>> resetPassword(
            @Valid @RequestBody NewPasswordRequest request) {
        passwordResetService.resetPassword(request);
        return ResponseEntity.ok(Map.of("message", "Contraseña actualizada exitosamente."));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Extrae la IP real del cliente considerando proxies inversos.
     */
    private String extractIp(HttpServletRequest request) {
        String forwarded = request.getHeader("X-Forwarded-For");
        if (forwarded != null && !forwarded.isBlank()) {
            return forwarded.split(",")[0].trim();
        }
        return request.getRemoteAddr();
    }

    /**
     * Extrae el token JWT del header Authorization sin el prefijo "Bearer ".
     */
    private String extractTokenFromRequest(HttpServletRequest request) {
        String header = request.getHeader("Authorization");
        if (header != null && header.startsWith("Bearer ")) {
            return header.substring(7);
        }
        return null;
    }
}
