# Guía de Correcciones — Hachiko API REST
**Para ejecutar con Claude Code desde la raíz del repositorio**
**Semana 7 — SOLID y Refactorización | Abril 2026**

---

## Cómo usar esta guía

Cada sección es una unidad de trabajo independiente. Ejecutarlas en el orden listado porque la Corrección 1 es bloqueante para el frontend y la Corrección 2 es prerequisito para Semana 8 de Seguridad.

| # | Problema | Prioridad | Archivos afectados |
|---|---|---|---|
| 1 | Configuración CORS inválida | **CRÍTICA — Bloqueante** | `SecurityConfig.java` |
| 2 | Logout no invalida el JWT | **ALTA — Seguridad** | `LogoutServiceImpl.java`, `ILogoutService.java`, `AuthController.java` |
| 3 | `AuthController` depende de clase concreta JWT | **MEDIA — DIP** | `AuthController.java`, nuevo `IJwtTokenProvider.java` |
| 4 | `AdminDashboardService` viola SRP | **MEDIA — SOLID** | `AdminDashboardServiceImpl.java`, `IAdminDashboardService.java`, nuevas clases |

Los paths en esta guía son relativos a `src/main/java/com/hachiko/portal/`.

---

---

## Corrección 1 — Configuración CORS inválida

### Problema

**Archivo:** `config/SecurityConfig.java`

La combinación `allowedOriginPatterns("*")` + `allowCredentials(true)` viola el estándar CORS. Los navegadores rechazan esta combinación y bloquean todos los requests autenticados. La API no es consumible por ningún frontend en navegador hasta que esto se corrija.

### Qué cambiar

Localizar el método `corsConfigurationSource()` dentro de `SecurityConfig.java`. El bloque actual es:

```java
@Bean
public CorsConfigurationSource corsConfigurationSource() {
    CorsConfiguration config = new CorsConfiguration();
    config.setAllowedOriginPatterns(List.of("*"));
    config.setAllowedMethods(List.of("GET", "POST", "PUT", "DELETE", "OPTIONS"));
    config.setAllowedHeaders(List.of("*"));
    config.setAllowCredentials(true);

    UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
    source.registerCorsConfiguration("/**", config);
    return source;
}
```

Reemplazarlo por:

```java
@Bean
public CorsConfigurationSource corsConfigurationSource() {
    CorsConfiguration config = new CorsConfiguration();

    // En desarrollo: origen explícito del frontend local.
    // En producción: reemplazar con el dominio real, ej. "https://app.hachiko.com"
    config.setAllowedOrigins(List.of(
        "http://localhost:3000",
        "http://localhost:5173",
        "http://localhost:8080"
    ));

    config.setAllowedMethods(List.of("GET", "POST", "PUT", "DELETE", "OPTIONS"));
    config.setAllowedHeaders(List.of("Authorization", "Content-Type", "Accept"));
    config.setExposedHeaders(List.of("Authorization"));
    config.setAllowCredentials(true);
    config.setMaxAge(3600L);

    UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
    source.registerCorsConfiguration("/**", config);
    return source;
}
```

> **Nota importante:** `setAllowedOrigins` (origen explícito) es compatible con `allowCredentials(true)`. El wildcard `"*"` no lo es. Los tres orígenes de localhost cubren los puertos más comunes de React, Vite y servidores de desarrollo genéricos.

### Criterio de verificación

Desde un frontend en `http://localhost:3000`, ejecutar un `fetch` con `credentials: "include"` a cualquier endpoint protegido. Debe responder sin error CORS. También verificar que `mvn test` sigue pasando.

---

---

## Corrección 2 — Logout no invalida el JWT

### Problema

**Archivo:** `service/impl/LogoutServiceImpl.java`

El sistema ya tiene JWT implementado (`JwtTokenProvider`, `JwtAuthenticationFilter`, `SecurityConfig` con filtro activo), pero el logout solo limpia intentos de login. Un usuario que llama a `POST /api/auth/logout` recibe 200 OK y su token JWT sigue siendo válido hasta que expire naturalmente. El logout es semánticamente vacío.

La solución correcta para un sistema stateless es una **blacklist de tokens en memoria** (usando `ConcurrentHashMap` con limpieza periódica). Esto es suficiente para Semana 7. La migración a Redis se hará en Semana 9 cuando se containerice el sistema.

### Paso 1 — Crear el servicio de blacklist

Crear el archivo `service/ITokenBlacklistService.java`:

```java
package com.hachiko.portal.service;

/**
 * Contrato para la invalidación de tokens JWT al hacer logout.
 *
 * Principio SRP: responsabilidad única — gestionar tokens revocados.
 * La implementación actual usa memoria (ConcurrentHashMap con limpieza periódica).
 * En Semana 9 se reemplazará por una implementación Redis sin modificar
 * ningún servicio que dependa de esta interfaz (DIP).
 */
public interface ITokenBlacklistService {

    /**
     * Agrega un token a la lista de tokens revocados.
     * El token permanece en la blacklist hasta su expiración natural.
     *
     * @param token     JWT completo (sin prefijo "Bearer ")
     * @param expiresAt marca de tiempo Unix en milisegundos de expiración del token
     */
    void blacklist(String token, long expiresAt);

    /**
     * Verifica si un token ha sido revocado.
     *
     * @param token JWT completo a verificar
     * @return true si el token fue revocado (logout previo)
     */
    boolean isBlacklisted(String token);
}
```

### Paso 2 — Crear la implementación en memoria

Crear el archivo `service/impl/TokenBlacklistServiceImpl.java`:

```java
package com.hachiko.portal.service.impl;

import com.hachiko.portal.service.ITokenBlacklistService;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;

import java.util.concurrent.ConcurrentHashMap;

/**
 * Implementación en memoria de la blacklist de tokens JWT.
 *
 * Usa ConcurrentHashMap para thread-safety sin sincronización explícita.
 * La tarea @Scheduled limpia automáticamente los tokens ya expirados
 * para evitar crecimiento ilimitado del mapa.
 *
 * Limitación conocida: la blacklist se pierde al reiniciar el servidor.
 * En Semana 9 se reemplazará por TokenBlacklistServiceRedis sin modificar
 * ningún código que dependa de ITokenBlacklistService.
 */
@Service
public class TokenBlacklistServiceImpl implements ITokenBlacklistService {

    // Clave: token JWT — Valor: marca de tiempo de expiración (ms Unix)
    private final ConcurrentHashMap<String, Long> blacklist = new ConcurrentHashMap<>();

    @Override
    public void blacklist(String token, long expiresAt) {
        blacklist.put(token, expiresAt);
    }

    @Override
    public boolean isBlacklisted(String token) {
        Long expiresAt = blacklist.get(token);
        if (expiresAt == null) return false;

        // Si el token ya expiró naturalmente, ya no es amenaza — eliminarlo.
        if (System.currentTimeMillis() > expiresAt) {
            blacklist.remove(token);
            return false;
        }
        return true;
    }

    /**
     * Limpieza periódica: elimina tokens expirados cada hora.
     * Evita que la blacklist crezca indefinidamente en sesiones largas.
     */
    @Scheduled(fixedRate = 3_600_000)
    public void cleanExpiredTokens() {
        long now = System.currentTimeMillis();
        blacklist.entrySet().removeIf(entry -> entry.getValue() < now);
    }
}
```

### Paso 3 — Habilitar scheduling en la aplicación

Verificar si la clase principal (`HachikoPortalApplication.java` o similar) ya tiene `@EnableScheduling`. Si no la tiene, agregarla:

```java
@SpringBootApplication
@EnableScheduling   // ← agregar esta anotación
public class HachikoPortalApplication {
    public static void main(String[] args) {
        SpringApplication.run(HachikoPortalApplication.class, args);
    }
}
```

El import necesario es `org.springframework.scheduling.annotation.EnableScheduling`.

### Paso 4 — Exponer la expiración del token en JwtTokenProvider

Abrir `security/JwtTokenProvider.java` y agregar este método al final de la clase:

```java
/**
 * Extrae la fecha de expiración del token en milisegundos Unix.
 * Usado por LogoutService para registrar cuándo expira el token revocado.
 *
 * @param token JWT válido
 * @return expiración en milisegundos desde epoch
 */
public long getExpirationFromToken(String token) {
    return Jwts.parser()
            .verifyWith(secretKey)
            .build()
            .parseSignedClaims(token)
            .getPayload()
            .getExpiration()
            .getTime();
}
```

### Paso 5 — Actualizar la interfaz ILogoutService

Abrir `service/ILogoutService.java` y agregar el parámetro `token`:

```java
package com.hachiko.portal.service;

/**
 * Contrato para el cierre de sesión.
 * Módulo: Autenticación.
 *
 * El logout en un sistema JWT stateless requiere dos acciones:
 *  1. Limpiar los intentos de login del usuario (comportamiento original).
 *  2. Revocar el token JWT para que no pueda reutilizarse hasta su expiración.
 */
public interface ILogoutService {

    /**
     * Cierra la sesión del usuario: limpia intentos de login y revoca el token.
     *
     * @param userId    ID del usuario autenticado (extraído del token por el controlador)
     * @param ipAddress IP del cliente
     * @param token     JWT completo (sin prefijo "Bearer ") para revocar
     */
    void logout(Integer userId, String ipAddress, String token);
}
```

### Paso 6 — Actualizar LogoutServiceImpl

Reemplazar el contenido de `service/impl/LogoutServiceImpl.java` por:

```java
package com.hachiko.portal.service.impl;

import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.security.JwtTokenProvider;
import com.hachiko.portal.service.ILockService;
import com.hachiko.portal.service.ILogoutService;
import com.hachiko.portal.service.ITokenBlacklistService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;

/**
 * Implementación del servicio de cierre de sesión.
 * Módulo: Autenticación.
 *
 * Acciones al hacer logout:
 *  1. Limpiar intentos de login (comportamiento original).
 *  2. Revocar el JWT en la blacklist para que no pueda reutilizarse.
 */
@Service
public class LogoutServiceImpl implements ILogoutService {

    private static final Logger log = LoggerFactory.getLogger(LogoutServiceImpl.class);

    private final IUsuarioRepository usuarioRepository;
    private final ILockService lockService;
    private final ITokenBlacklistService tokenBlacklistService;
    private final JwtTokenProvider jwtTokenProvider;

    public LogoutServiceImpl(IUsuarioRepository usuarioRepository,
                             ILockService lockService,
                             ITokenBlacklistService tokenBlacklistService,
                             JwtTokenProvider jwtTokenProvider) {
        this.usuarioRepository = usuarioRepository;
        this.lockService = lockService;
        this.tokenBlacklistService = tokenBlacklistService;
        this.jwtTokenProvider = jwtTokenProvider;
    }

    @Override
    public void logout(Integer userId, String ipAddress, String token) {
        // Revocar el token JWT inmediatamente.
        if (token != null && !token.isBlank()) {
            long expiresAt = jwtTokenProvider.getExpirationFromToken(token);
            tokenBlacklistService.blacklist(token, expiresAt);
            log.info("[LOGOUT] Token revocado para userId={}", userId);
        }

        // Limpiar intentos de login.
        usuarioRepository.findById(userId).ifPresentOrElse(
                usuario -> {
                    lockService.clearAttempts(usuario.getEmail(), ipAddress);
                    log.info("[LOGOUT] Usuario {} cerró sesión desde IP {}", usuario.getEmail(), ipAddress);
                },
                () -> log.warn("[LOGOUT] Intento de logout con userId inexistente: {}", userId)
        );
    }
}
```

### Paso 7 — Actualizar JwtAuthenticationFilter para verificar la blacklist

Abrir `security/JwtAuthenticationFilter.java`. El filtro necesita verificar la blacklist antes de autenticar. Agregar `ITokenBlacklistService` como dependencia e incorporar la verificación:

```java
package com.hachiko.portal.security;

import com.hachiko.portal.service.ITokenBlacklistService;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;
import java.util.List;

/**
 * Filtro JWT — se ejecuta una sola vez por request.
 *
 * Valida el token y verifica que no haya sido revocado (blacklist).
 * Si el token es inválido o está en la blacklist, no establece contexto
 * y Spring Security bloqueará el acceso con 401.
 */
@Component
public class JwtAuthenticationFilter extends OncePerRequestFilter {

    private final JwtTokenProvider jwtTokenProvider;
    private final ITokenBlacklistService tokenBlacklistService;

    public JwtAuthenticationFilter(JwtTokenProvider jwtTokenProvider,
                                   ITokenBlacklistService tokenBlacklistService) {
        this.jwtTokenProvider = jwtTokenProvider;
        this.tokenBlacklistService = tokenBlacklistService;
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                    HttpServletResponse response,
                                    FilterChain filterChain)
            throws ServletException, IOException {

        String token = extractToken(request);

        if (token != null
                && jwtTokenProvider.validateToken(token)
                && !tokenBlacklistService.isBlacklisted(token)) {   // ← verificación de blacklist

            Integer userId = jwtTokenProvider.getUserIdFromToken(token);
            String role = jwtTokenProvider.getRoleFromToken(token);

            List<SimpleGrantedAuthority> authorities =
                    List.of(new SimpleGrantedAuthority("ROLE_" + role));

            UsernamePasswordAuthenticationToken authentication =
                    new UsernamePasswordAuthenticationToken(userId, null, authorities);

            SecurityContextHolder.getContext().setAuthentication(authentication);
        }

        filterChain.doFilter(request, response);
    }

    private String extractToken(HttpServletRequest request) {
        String header = request.getHeader("Authorization");
        if (header != null && header.startsWith("Bearer ")) {
            return header.substring(7);
        }
        return null;
    }
}
```

### Paso 8 — Actualizar AuthController para pasar el token al logout

Abrir `controller/AuthController.java`. Localizar el método `logout` y actualizarlo para extraer el token del header y pasarlo al servicio:

```java
/**
 * Cierra la sesión del usuario autenticado.
 * Revoca el JWT y limpia los intentos de login.
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
```

Agregar también el helper `extractTokenFromRequest` en la sección de helpers del controlador (junto al `extractIp` existente):

```java
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
```

### Criterio de verificación

1. Hacer login → obtener token.
2. Llamar a `POST /api/auth/logout` con ese token en el header.
3. Intentar usar el mismo token en cualquier endpoint protegido → debe responder `401 Unauthorized`.
4. Esperar a que el token expire naturalmente y confirmar que el sistema sigue funcionando (la limpieza periódica no rompe nada).

---

---

## Corrección 3 — AuthController depende de clase concreta JWT (violación DIP)

### Problema

**Archivo:** `controller/AuthController.java`

`AuthController` inyecta directamente `JwtTokenProvider` (clase concreta del paquete `security/`), violando el Principio de Inversión de Dependencias. Si el proveedor JWT cambia (de JJWT a Nimbus, por ejemplo), `AuthController` debe modificarse. Los controladores de alto nivel no deben depender de detalles de infraestructura de bajo nivel.

> **Nota:** Dado que en la Corrección 2 ya se agrega `getExpirationFromToken()` a `JwtTokenProvider`, esta interfaz debe incluir ese método también.

### Paso 1 — Crear la interfaz IJwtTokenProvider

Crear el archivo `security/IJwtTokenProvider.java`:

```java
package com.hachiko.portal.security;

/**
 * Contrato para la generación y validación de tokens JWT.
 *
 * Principio DIP: AuthController y JwtAuthenticationFilter dependen de esta
 * interfaz, no de JwtTokenProvider. Si se cambia el proveedor JWT
 * (de JJWT a Nimbus, por ejemplo), solo cambia la implementación —
 * ningún controlador ni filtro se modifica.
 */
public interface IJwtTokenProvider {

    /**
     * Genera un JWT firmado.
     *
     * @param userId ID del usuario autenticado
     * @param email  email del usuario
     * @param role   rol del usuario (ADMIN / USER)
     * @return token JWT como String
     */
    String generateToken(Integer userId, String email, String role);

    /**
     * Valida que el token sea correcto y no haya expirado.
     *
     * @param token JWT a validar
     * @return true si el token es válido y vigente
     */
    boolean validateToken(String token);

    /**
     * Extrae el userId del claim del token.
     *
     * @param token JWT válido
     * @return userId almacenado en el claim
     */
    Integer getUserIdFromToken(String token);

    /**
     * Extrae el rol del claim del token.
     *
     * @param token JWT válido
     * @return rol almacenado en el claim (ej. "ADMIN", "USER")
     */
    String getRoleFromToken(String token);

    /**
     * Extrae la fecha de expiración del token en milisegundos Unix.
     * Usado por LogoutService para registrar cuándo expira el token revocado.
     *
     * @param token JWT válido
     * @return expiración en milisegundos desde epoch
     */
    long getExpirationFromToken(String token);
}
```

### Paso 2 — Hacer que JwtTokenProvider implemente la interfaz

Abrir `security/JwtTokenProvider.java` y modificar la declaración de la clase:

```java
// Antes:
@Component
public class JwtTokenProvider {

// Después:
@Component
public class JwtTokenProvider implements IJwtTokenProvider {
```

No cambiar ningún método. Agregar `@Override` en cada método que implementa la interfaz (los cuatro existentes más el nuevo `getExpirationFromToken` añadido en Corrección 2).

### Paso 3 — Actualizar AuthController para usar la interfaz

Abrir `controller/AuthController.java`. Cambiar el tipo de la dependencia:

```java
// Antes:
private final JwtTokenProvider jwtTokenProvider;

public AuthController(ILoginService loginService,
                      IRegisterService registerService,
                      ILogoutService logoutService,
                      IPasswordResetService passwordResetService,
                      JwtTokenProvider jwtTokenProvider) {
    ...
    this.jwtTokenProvider = jwtTokenProvider;
}

// Después:
private final IJwtTokenProvider jwtTokenProvider;

public AuthController(ILoginService loginService,
                      IRegisterService registerService,
                      ILogoutService logoutService,
                      IPasswordResetService passwordResetService,
                      IJwtTokenProvider jwtTokenProvider) {
    ...
    this.jwtTokenProvider = jwtTokenProvider;
}
```

Actualizar el import: eliminar `import com.hachiko.portal.security.JwtTokenProvider` y asegurarse de que esté presente `import com.hachiko.portal.security.IJwtTokenProvider`.

### Paso 4 — Actualizar JwtAuthenticationFilter para usar la interfaz

Aplicar el mismo cambio en `security/JwtAuthenticationFilter.java` (este cambio ya fue incluido en el código de la Corrección 2, pero si se implementan las correcciones por separado, hacerlo aquí también):

```java
// Cambiar tipo de campo y constructor:
private final IJwtTokenProvider jwtTokenProvider;

public JwtAuthenticationFilter(IJwtTokenProvider jwtTokenProvider,
                                ITokenBlacklistService tokenBlacklistService) {
    this.jwtTokenProvider = jwtTokenProvider;
    this.tokenBlacklistService = tokenBlacklistService;
}
```

### Paso 5 — Actualizar LogoutServiceImpl para usar la interfaz

En `service/impl/LogoutServiceImpl.java`, cambiar el tipo de la dependencia `JwtTokenProvider` por `IJwtTokenProvider`:

```java
// Cambiar campo y constructor:
private final IJwtTokenProvider jwtTokenProvider;

public LogoutServiceImpl(IUsuarioRepository usuarioRepository,
                         ILockService lockService,
                         ITokenBlacklistService tokenBlacklistService,
                         IJwtTokenProvider jwtTokenProvider) { ... }
```

### Criterio de verificación

Ejecutar `mvn compile`. No debe haber ningún import de `JwtTokenProvider` (la clase concreta) fuera del propio archivo `JwtTokenProvider.java`. Verificar con:

```bash
grep -r "JwtTokenProvider" src/ --include="*.java" | grep -v "JwtTokenProvider.java"
```

El resultado debe estar vacío.

---

---

## Corrección 4 — AdminDashboardService viola SRP

### Problema

**Archivos:** `service/IAdminDashboardService.java`, `service/impl/AdminDashboardServiceImpl.java`

`AdminDashboardServiceImpl` concentra cinco responsabilidades distintas en una sola clase: estadísticas del dashboard, feed de actividad reciente, listado de usuarios, cambio de rol y eliminación de usuarios. Tiene seis dependencias inyectadas. La guía de refactorización prohíbe explícitamente clases que concentran demasiada lógica.

La solución es separar en dos servicios con responsabilidades claras:

- `IAdminDashboardService` → solo estadísticas y actividad reciente (lectura, solo dashboard).
- `IAdminUserService` → solo gestión de usuarios: listar, detalle, cambiar rol, eliminar.

`AdminDashboardServiceImpl` ya existe: se reduce. Se crea `AdminUserServiceImpl` nuevo.

### Paso 1 — Crear la interfaz IAdminUserService

Crear el archivo `service/IAdminUserService.java`:

```java
package com.hachiko.portal.service;

import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;

import java.util.List;

/**
 * Contrato para la gestión de usuarios desde el panel de administración.
 * Módulo: Administración.
 *
 * Principio SRP: responsabilidad única — operaciones CRUD sobre usuarios.
 * Las estadísticas del dashboard pertenecen a IAdminDashboardService.
 * Principio DIP: AdminController depende de esta interfaz, no de la implementación.
 */
public interface IAdminUserService {

    /**
     * Retorna la lista de todos los usuarios registrados en el sistema.
     *
     * @return lista de UsuarioDTO, vacía si no hay usuarios
     */
    List<UsuarioDTO> getAllUsers();

    /**
     * Retorna el detalle completo de un usuario: cuenta, perfil de propietario y mascotas.
     *
     * @param userId ID del usuario
     * @return UserDetailDTO con usuario, propietario (nullable) y cantidadMascotas
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    UserDetailDTO getUserDetail(Integer userId);

    /**
     * Actualiza el rol de un usuario. Operación exclusiva de administradores.
     *
     * @param userId ID del usuario a actualizar
     * @param role   nuevo rol como String ("ADMIN" o "USER")
     * @throws com.hachiko.portal.exception.ValidationException si el rol no es válido
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    void updateUserRole(Integer userId, String role);

    /**
     * Elimina permanentemente un usuario del sistema.
     * Las entidades relacionadas se eliminan por cascada en BD.
     *
     * @param userId ID del usuario a eliminar
     * @throws com.hachiko.portal.exception.ResourceNotFoundException si el usuario no existe
     */
    void deleteUser(Integer userId);
}
```

### Paso 2 — Actualizar IAdminDashboardService

Reemplazar el contenido de `service/IAdminDashboardService.java` para que contenga **solo** las responsabilidades del dashboard:

```java
package com.hachiko.portal.service;

import com.hachiko.portal.dto.admin.DashboardStatsDTO;

/**
 * Contrato para las estadísticas del panel de administración.
 * Módulo: Administración.
 *
 * Principio SRP: responsabilidad única — proveer estadísticas y actividad reciente.
 * La gestión de usuarios (listar, cambiar rol, eliminar) pertenece a IAdminUserService.
 * Principio DIP: AdminController depende de esta interfaz, no de la implementación.
 */
public interface IAdminDashboardService {

    /**
     * Retorna las estadísticas del dashboard y el feed de actividad reciente.
     *
     * Estadísticas:
     *   - totalUsuarios       : count(users)
     *   - loginHoy            : usuarios con lastLogin en la fecha actual
     *   - intentosFallidosHoy : login_attempts de hoy
     *   - cuentasBloqueadas   : emails con >= 3 intentos en últimos 15 min
     *
     * Actividad reciente: mezcla de logins, registros e intentos fallidos,
     * ordenados por momento DESC, limitado a los 10 más recientes.
     *
     * @return DashboardStatsDTO con contadores y lista actividadReciente
     */
    DashboardStatsDTO getDashboardStats();
}
```

### Paso 3 — Crear AdminUserServiceImpl

Crear el archivo `service/impl/AdminUserServiceImpl.java`. El contenido es la extracción de los métodos `getAllUsers`, `getUserDetail`, `updateUserRole` y `deleteUser` de `AdminDashboardServiceImpl`, más sus dependencias necesarias (`IUsuarioRepository`, `IPropietarioService`, `IPerroRepository`, `UserValidator`):

```java
package com.hachiko.portal.service.impl;

import com.hachiko.portal.domain.Usuario;
import com.hachiko.portal.domain.enums.UserRole;
import com.hachiko.portal.dto.admin.UserDetailDTO;
import com.hachiko.portal.dto.propietario.PropietarioDTO;
import com.hachiko.portal.dto.usuario.UsuarioDTO;
import com.hachiko.portal.exception.ResourceNotFoundException;
import com.hachiko.portal.exception.ValidationException;
import com.hachiko.portal.repository.IPerroRepository;
import com.hachiko.portal.repository.IUsuarioRepository;
import com.hachiko.portal.service.IAdminUserService;
import com.hachiko.portal.service.IPropietarioService;
import com.hachiko.portal.service.validation.UserValidator;
import com.hachiko.portal.service.validation.ValidationResult;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

/**
 * Implementación del servicio de gestión de usuarios desde el panel de administración.
 * Módulo: Administración.
 *
 * Principio SRP: responsabilidad única — operaciones sobre usuarios.
 * Las estadísticas del dashboard pertenecen a AdminDashboardServiceImpl.
 */
@Service
public class AdminUserServiceImpl implements IAdminUserService {

    private final IUsuarioRepository usuarioRepository;
    private final IPropietarioService propietarioService;
    private final IPerroRepository perroRepository;
    private final UserValidator userValidator;

    public AdminUserServiceImpl(IUsuarioRepository usuarioRepository,
                                IPropietarioService propietarioService,
                                IPerroRepository perroRepository,
                                UserValidator userValidator) {
        this.usuarioRepository = usuarioRepository;
        this.propietarioService = propietarioService;
        this.perroRepository = perroRepository;
        this.userValidator = userValidator;
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

        PropietarioDTO propietario = null;
        long cantidadMascotas = 0;

        try {
            propietario = propietarioService.getByUserId(userId);
            cantidadMascotas = perroRepository
                    .countByPropietario_PropietarioId(propietario.getPropietarioId());
        } catch (Exception ignored) {
            // El usuario puede no tener perfil de propietario — es válido.
        }

        return UserDetailDTO.builder()
                .usuario(toUsuarioDTO(usuario))
                .propietario(propietario)
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

        usuarioRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", userId));

        usuarioRepository.updateRole(userId, UserRole.valueOf(role.toUpperCase()));
    }

    @Override
    @Transactional
    public void deleteUser(Integer userId) {
        usuarioRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", userId));
        usuarioRepository.deleteById(userId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private UsuarioDTO toUsuarioDTO(Usuario u) {
        return UsuarioDTO.builder()
                .id(u.getId())
                .email(u.getEmail())
                .role(u.getRole().name())
                .createdAt(u.getCreatedAt())
                .lastLogin(u.getLastLogin())
                .build();
    }
}
```

### Paso 4 — Reducir AdminDashboardServiceImpl

Abrir `service/impl/AdminDashboardServiceImpl.java` y eliminar los métodos `getAllUsers`, `getUserDetail`, `updateUserRole` y `deleteUser`, junto con sus dependencias exclusivas. El resultado debe contener **solo** `getDashboardStats()` y sus dependencias (`IUsuarioRepository`, `ILoginAttemptRepository`, `IPropietarioRepository`, `IPerroRepository`). Eliminar también `IPropietarioService` y `UserValidator` del constructor si no los usa ya el método que queda.

La firma de la clase después del refactor:

```java
@Service
public class AdminDashboardServiceImpl implements IAdminDashboardService {

    private final IUsuarioRepository usuarioRepository;
    private final ILoginAttemptRepository loginAttemptRepository;
    private final IPropietarioRepository propietarioRepository;
    private final IPerroRepository perroRepository;

    public AdminDashboardServiceImpl(IUsuarioRepository usuarioRepository,
                                     ILoginAttemptRepository loginAttemptRepository,
                                     IPropietarioRepository propietarioRepository,
                                     IPerroRepository perroRepository) {
        this.usuarioRepository = usuarioRepository;
        this.loginAttemptRepository = loginAttemptRepository;
        this.propietarioRepository = propietarioRepository;
        this.perroRepository = perroRepository;
    }

    @Override
    @Transactional(readOnly = true)
    public DashboardStatsDTO getDashboardStats() {
        // ... mantener el cuerpo del método exactamente como está ...
    }
}
```

### Paso 5 — Actualizar AdminController

Abrir `controller/AdminController.java`. Agregar `IAdminUserService` como segunda dependencia y redirigir los endpoints de usuarios:

```java
@RestController
@RequestMapping("/api/admin")
public class AdminController {

    private final IAdminDashboardService adminDashboardService;
    private final IAdminUserService adminUserService;        // ← nuevo
    private final IRegisterService registerService;

    public AdminController(IAdminDashboardService adminDashboardService,
                           IAdminUserService adminUserService,             // ← nuevo
                           IRegisterService registerService) {
        this.adminDashboardService = adminDashboardService;
        this.adminUserService = adminUserService;                          // ← nuevo
        this.registerService = registerService;
    }

    @GetMapping("/stats")
    public ResponseEntity<DashboardStatsDTO> getDashboardStats() {
        return ResponseEntity.ok(adminDashboardService.getDashboardStats());
    }

    @GetMapping("/usuarios")
    public ResponseEntity<List<UsuarioDTO>> getAllUsers() {
        return ResponseEntity.ok(adminUserService.getAllUsers());          // ← era adminDashboardService
    }

    @GetMapping("/usuarios/{userId}")
    public ResponseEntity<UserDetailDTO> getUserDetail(@PathVariable Integer userId) {
        return ResponseEntity.ok(adminUserService.getUserDetail(userId));  // ← era adminDashboardService
    }

    // ... POST /usuarios (registerService.register) — sin cambios ...

    @PutMapping("/usuarios/{userId}/role")
    public ResponseEntity<Map<String, String>> updateUserRole(
            @PathVariable Integer userId,
            @RequestBody Map<String, String> body) {
        adminUserService.updateUserRole(userId, body.get("role"));        // ← era adminDashboardService
        return ResponseEntity.ok(Map.of("message", "Rol actualizado correctamente."));
    }

    @DeleteMapping("/usuarios/{userId}")
    public ResponseEntity<Void> deleteUser(@PathVariable Integer userId) {
        adminUserService.deleteUser(userId);                              // ← era adminDashboardService
        return ResponseEntity.noContent().build();
    }
}
```

Agregar el import necesario: `import com.hachiko.portal.service.IAdminUserService`.

### Criterio de verificación

```bash
mvn compile
```

Debe compilar sin errores. Verificar también que `AdminDashboardServiceImpl` tenga exactamente cuatro dependencias (no seis) y que no exista ninguna referencia a `getAllUsers`, `getUserDetail`, `updateUserRole` o `deleteUser` en esa clase.

---

---

## Resumen de archivos modificados y creados

| Acción | Archivo |
|---|---|
| **Modificar** | `config/SecurityConfig.java` |
| **Modificar** | `service/ILogoutService.java` |
| **Modificar** | `service/impl/LogoutServiceImpl.java` |
| **Modificar** | `security/JwtTokenProvider.java` |
| **Modificar** | `security/JwtAuthenticationFilter.java` |
| **Modificar** | `controller/AuthController.java` |
| **Modificar** | `service/IAdminDashboardService.java` |
| **Modificar** | `service/impl/AdminDashboardServiceImpl.java` |
| **Modificar** | `controller/AdminController.java` |
| **Crear** | `service/ITokenBlacklistService.java` |
| **Crear** | `service/impl/TokenBlacklistServiceImpl.java` |
| **Crear** | `security/IJwtTokenProvider.java` |
| **Crear** | `service/IAdminUserService.java` |
| **Crear** | `service/impl/AdminUserServiceImpl.java` |

**Verificación final de todo el conjunto:**

```bash
mvn compile && mvn test
```

```bash
# Ningún controlador o filtro debe importar JwtTokenProvider directamente
grep -r "import.*JwtTokenProvider" src/ --include="*.java" | grep -v "JwtTokenProvider.java" | grep -v "IJwtTokenProvider.java"
# Resultado esperado: vacío

# AdminDashboardServiceImpl no debe tener getAllUsers ni getUserDetail
grep -n "getAllUsers\|getUserDetail\|updateUserRole\|deleteUser" src/main/java/com/hachiko/portal/service/impl/AdminDashboardServiceImpl.java
# Resultado esperado: vacío
```
