# ADR-04: JWT y Seguridad de Autenticación

Estado: Aceptado

Fecha: 2026-04-20

---

## Contexto

El portal Hachiko requiere un mecanismo de autenticación seguro para una API REST stateless construida con Spring Boot. Se necesita proteger rutas privadas, distinguir roles (USER / ADMIN), prevenir ataques de fuerza bruta y garantizar que los tokens revocados no puedan reutilizarse. El sistema es un prototipo académico funcional con seguridad real implementada.

---

## Decisión

Se implementó una arquitectura de autenticación basada en **JWT firmados con HMAC-SHA256**, complementada con **BCrypt** para el hash de contraseñas, un **filtro de autenticación** como middleware HTTP, un servicio de **blacklist de tokens** y un sistema de **bloqueo de cuenta** por intentos fallidos.

---

## Componentes Implementados

### 1. JWT — Generación y Validación (`JwtTokenProvider` / `IJwtTokenProvider`)

**Qué hace:**
- Genera tokens firmados HS256 con los claims: `userId`, `email`, `role`, `issuedAt`, `expiration`.
- Valida firma, expiración y estructura del token.
- Expone la duración del token para que el cliente gestione el ciclo de vida.

**¿Cumple su función?** Sí, completamente.

- Secreto externalizado via variable de entorno `JWT_SECRET`.
- Duración configurable via `JWT_EXPIRATION_MS` (por defecto 24 horas).
- Usa la librería JJWT con API moderna (`parseSignedClaims`).
- Diseño basado en interfaz (`IJwtTokenProvider`) que permite intercambiar la implementación sin afectar al resto del sistema.

---

### 2. BCrypt — Hash de Contraseñas (`AppConfig` + `PasswordEncoder`)

**Qué hace:**
- Encripta contraseñas con BCrypt antes de persistirlas en base de datos.
- Permite verificar contraseñas en texto plano contra el hash almacenado sin desencriptar.

**¿Cumple su función?** Sí, completamente.

- `PasswordEncoder` configurado en `AppConfig` (separado de `SecurityConfig` para evitar dependencia circular).
- Usado en `RegisterServiceImpl` al crear usuarios y en `LoginServiceImpl` para verificar credenciales.
- BCrypt aplica factor de coste automático, lo que hace impráctica la fuerza bruta sobre hashes.

---

### 3. Auth Middleware — Filtro JWT (`JwtAuthenticationFilter`)

**Qué hace:**
- Intercepta **cada request HTTP** antes de llegar al controlador.
- Extrae el token del header `Authorization: Bearer <token>`.
- Valida firma y expiración via `IJwtTokenProvider`.
- Consulta la blacklist para rechazar tokens revocados.
- Si el token es válido: extrae `userId` y `role`, construye el contexto de seguridad de Spring (`SecurityContextHolder`).

**¿Cumple su función?** Sí, completamente.

- Extiende `OncePerRequestFilter` — se ejecuta exactamente una vez por request.
- Prefija el rol con `ROLE_` para compatibilidad con `hasRole("ADMIN")` de Spring Security.
- Si el token está ausente o es inválido, simplemente no autentica (el filtro chain continúa y Spring Security denegará el acceso a rutas protegidas).
- Dependencias via interfaces: no acopla al filtro con implementaciones concretas.

---

### 4. Filter Chain — Configuración de Seguridad HTTP (`SecurityConfig`)

**Qué hace:**
- Define qué rutas son públicas, cuáles requieren autenticación y cuáles requieren rol ADMIN.
- Configura CORS con lista de orígenes permitidos por variable de entorno.
- Deshabilita CSRF (correcto para API REST stateless).
- Establece sesiones como `STATELESS` (sin HttpSession).
- Registra `JwtAuthenticationFilter` antes del filtro estándar de Spring.

**Rutas configuradas:**

| Ruta | Acceso |
|------|--------|
| `/api/auth/login`, `/api/auth/register`, `/api/auth/forgot-password`, `/api/auth/reset-password` | Público |
| `/api/referencia/**` | Público |
| `/api/admin/**` | Solo `ROLE_ADMIN` |
| Todo lo demás | Autenticado |

**¿Cumple su función?** Sí, completamente.

---

### 5. Token Blacklist (`TokenBlacklistServiceImpl` / `ITokenBlacklistService`)

**Qué hace:**
- Mantiene un registro de tokens JWT revocados (post-logout).
- Impide que un token válido pero revocado pueda usarse para autenticarse.
- Limpia automáticamente tokens cuya expiración natural ya pasó.

**¿Cumple su función?** Sí, para el alcance del proyecto.

- Implementación: `ConcurrentHashMap<String, Long>` (token → timestamp de expiración).
- `isBlacklisted(token)`: retorna `true` si el token está en el mapa Y no ha expirado aún.
- Tarea programada con `@Scheduled(fixedRate = 3_600_000)` limpia tokens vencidos cada hora.
- Thread-safe para entornos concurrentes.

---

### 6. Account Lockout — Bloqueo por Fuerza Bruta (`LockServiceImpl` / `ILockService`)

**Qué hace:**
- Registra cada intento de login fallido con email, IP y timestamp.
- Bloquea el acceso cuando se alcanzan **3 intentos fallidos en 10 minutos**.
- El bloqueo expira automáticamente (sin estado explícito en BD).
- Informa al usuario los intentos restantes y el tiempo de desbloqueo.

**¿Cumple su función?** Sí, completamente.

- Doble targeting: bloquea por **email** O por **IP** (protege contra ataques distribuidos).
- Persistencia en tabla `login_attempts` (email, ipAddress, attemptTime).
- Sin flag de bloqueo explícito: consulta dinámica por ventana de tiempo, lo que evita bugs de "bloqueo permanente".
- `@Transactional` en escrituras para consistencia.

---

### 7. Login Service (`LoginServiceImpl`)

**Qué hace:**
Orquesta el flujo completo de autenticación en orden seguro:

1. Busca usuario por email (mensaje genérico si no existe — no revela si el email está registrado).
2. Verifica bloqueo de cuenta **antes** de validar la contraseña (evita timing attacks).
3. Verifica contraseña con BCrypt.
4. En fallo: registra intento, calcula intentos restantes, lanza excepción con feedback.
5. En éxito: actualiza `lastLogin`, limpia intentos fallidos, detecta perfil incompleto.
6. Retorna `LoginResponse` con `userId`, `email`, `role`, `requiresProfileCompletion`.

**¿Cumple su función?** Sí, completamente.

---

### 8. Logout Service (`LogoutServiceImpl`)

**Qué hace:**
- Agrega el token al blacklist con su tiempo de expiración original.
- Limpia los intentos de login fallidos del usuario (resetea el contador de brute-force).
- Registra el evento de logout en logs.

**¿Cumple su función?** Sí, completamente.

---

### 9. Auth Controller (`AuthController`)

**Endpoints expuestos:**

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/auth/login` | Autentica y retorna JWT |
| POST | `/api/auth/register` | Crea nueva cuenta |
| POST | `/api/auth/logout` | Revoca el token activo |
| POST | `/api/auth/forgot-password` | Solicita email de reset |
| POST | `/api/auth/reset-password` | Aplica nueva contraseña |

**¿Cumple su función?** Sí, completamente.

- Extrae IP del cliente con soporte para reverse proxy (`X-Forwarded-For`).
- El token JWT se genera en el controlador (mantiene la capa de servicio agnóstica a JWT).
- Usa `@AuthenticationPrincipal Integer userId` para obtener el usuario autenticado desde el contexto de Spring Security.

---

## Consecuencias

### Positivas
- Autenticación completamente stateless: escala horizontalmente sin sesiones compartidas.
- BCrypt hace impráctica la fuerza bruta sobre contraseñas almacenadas.
- La blacklist garantiza que el logout sea efectivo inmediatamente.
- El lockout por email+IP protege contra ataques de fuerza bruta distribuidos.
- El diseño basado en interfaces permite sustituir cualquier componente sin modificar el resto.
- CORS configurable por entorno evita hardcodear orígenes.
- El flujo de login protege información sensible (no revela si un email existe).

### Negativas
- La blacklist en memoria es suficiente para un prototipo académico, pero no persiste entre reinicios del servidor.
- Sin refresh tokens: al expirar el access token (24h), el usuario debe volver a hacer login.
- La protección de fuerza bruta es a nivel aplicación; no existe rate limiting a nivel HTTP/infraestructura.

---

## Alternativas Consideradas

- **Spring Session + Redis** para gestión de sesiones: descartado por romper la naturaleza stateless de la API REST.
- **OAuth2 / OpenID Connect** con proveedor externo (Auth0, Keycloak): descartado por complejidad excesiva para el alcance académico del proyecto.
- **Opaque tokens** en lugar de JWT: descartado porque JWT permite validación sin consultar la BD en cada request.
- **Argon2** en lugar de BCrypt: considerado como alternativa superior, descartado por ser suficiente BCrypt para el contexto del proyecto.
