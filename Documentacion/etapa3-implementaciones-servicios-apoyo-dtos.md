# Etapa 3 — Implementaciones de Servicios de Apoyo, Validadores y DTOs

**Fecha:** Abril 2026
**Proyecto:** Hachiko Portal
**Contexto:** Migración de monolito PHP → API REST Spring Boot + PostgreSQL

---

## Objetivo de la Etapa

Completar la capa de infraestructura del sistema antes de que los servicios de negocio (Etapa 4) puedan existir. En Spring Boot, Spring Data JPA ya genera las implementaciones de repositorios a partir de las interfaces definidas en Etapa 2 — no hay clases `*RepositoryPDO` que escribir. El trabajo real de esta etapa es:

1. **Corregir defectos** detectados en la verificación de Etapas 1 y 2
2. **Implementar servicios de apoyo** (`EmailServiceStub`, `PasswordServiceBCrypt`)
3. **Completar validadores** (`PropietarioValidator`, faltante en Etapa 2)
4. **Definir la capa de DTOs** (contratos entre capas Controller ↔ Service)
5. **Agregar configuración Spring** (bean `BCryptPasswordEncoder`)

> "Los repositorios deben estar listos ANTES de los servicios. Los validadores deben existir ANTES de los servicios." — Guía de Refactorización §8.1

---

## Correcciones de Etapas Anteriores

### Defecto corregido: `Perro.java` sin `@GeneratedValue`

**Problema:** La tabla `perro` tiene `perro_id INT AUTO_INCREMENT` (confirmado en `Codigo base/sql/alter_perro.sql`), pero la entidad no tenía `@GeneratedValue`. Esto causaría una excepción al intentar insertar un nuevo perro sin asignar ID manualmente.

**Corrección aplicada:**
```java
// Antes (incorrecto):
@Id
@Column(name = "perro_id")
private Integer perroId;

// Después (correcto):
@Id
@GeneratedValue(strategy = GenerationType.IDENTITY)
@Column(name = "perro_id")
private Integer perroId;
```

**Por qué no afecta a otros catálogos:** `Raza`, `Plan`, `Pais`, `Departamento`, `Ciudad` son catálogos con IDs asignados manualmente en el SQL — no tienen `AUTO_INCREMENT`. Solo `perro`, `propietario`, `residencia`, `users`, `login_attempts` y `password_resets` tienen identidad gestionada por la BD.

---

## Estructura de paquetes creada

```
src/main/java/com/hachiko/portal/
├── config/                              ← NUEVO — Etapa 3
│   └── AppConfig.java                  (bean BCryptPasswordEncoder)
│
├── dto/                                 ← NUEVO — Etapa 3 (20 clases)
│   ├── auth/
│   │   ├── LoginRequest.java
│   │   ├── LoginResponse.java
│   │   ├── RegisterRequest.java
│   │   ├── PasswordResetRequest.java
│   │   └── NewPasswordRequest.java
│   ├── usuario/
│   │   ├── UsuarioDTO.java
│   │   └── UpdateUserRequest.java
│   ├── propietario/
│   │   ├── PropietarioDTO.java
│   │   ├── ResidenciaDTO.java
│   │   ├── CreatePropietarioRequest.java
│   │   └── UpdatePropietarioRequest.java
│   ├── mascota/
│   │   ├── MascotaDTO.java
│   │   ├── CreateMascotaRequest.java
│   │   └── UpdateMascotaRequest.java
│   ├── referencia/
│   │   ├── RazaDTO.java
│   │   ├── PlanDTO.java
│   │   ├── PaisDTO.java
│   │   ├── DepartamentoDTO.java
│   │   └── CiudadDTO.java
│   └── admin/
│       ├── DashboardStatsDTO.java
│       ├── ActividadRecienteDTO.java
│       └── UserDetailDTO.java
│
└── service/
    ├── IPasswordService.java            ← NUEVO — Etapa 3
    ├── impl/                            ← NUEVO — Etapa 3
    │   ├── EmailServiceStub.java
    │   └── PasswordServiceBCrypt.java
    └── validation/
        └── PropietarioValidator.java    ← NUEVO — Etapa 3
```

> Los paquetes `domain/` y `repository/` (Etapas 1 y 2) no se modificaron.

---

## Archivos Creados

### Configuración Spring (`config/`)

#### `AppConfig.java`

Declara el bean `PasswordEncoder` usando `BCryptPasswordEncoder` con factor de coste 10.

**Decisión de diseño:** El factor de coste (strength) se define aquí y solo aquí. Si se cambia de BCrypt a Argon2, solo cambia este archivo — ningún servicio de dominio se ve afectado (DIP).

**Dependencia agregada a `pom.xml`:**
```xml
<dependency>
    <groupId>org.springframework.security</groupId>
    <artifactId>spring-security-crypto</artifactId>
</dependency>
```
Solo incluye el módulo criptográfico de Spring Security. **No activa** ningún filtro de seguridad HTTP ni configuración de autenticación automática.

---

### Servicio de contraseñas (`service/`)

#### `IPasswordService.java`

Interfaz con dos métodos: `encode(rawPassword)` y `matches(rawPassword, encodedPassword)`.

**Por qué existe como interfaz:** `LoginService` y `RegisterService` (Etapa 4) dependerán de `IPasswordService`, no de `BCryptPasswordEncoder`. Si el algoritmo de hashing cambia, ningún servicio de dominio se modifica — solo la implementación.

---

### Implementaciones concretas (`service/impl/`)

#### `EmailServiceStub.java`

Implementa `IEmailService`. En lugar de conectarse a un servidor SMTP, registra cada llamada en el log con formato estructurado.

```
[EMAIL-STUB] Para    : usuario@ejemplo.com
[EMAIL-STUB] Asunto  : Recuperación de contraseña
[EMAIL-STUB] Cuerpo  : Tu token es: abc123...
```

**Anotaciones:** `@Service @Primary` — garantiza que Spring inyecte este stub cuando no haya otra implementación activa. Para activar SMTP real, se crea `EmailServiceSMTP` con `@Service` y se elimina `@Primary` del stub.

**Criterio de SRP:** Esta clase no valida contenido del correo ni decide cuándo enviarlo. Solo registra la llamada.

---

#### `PasswordServiceBCrypt.java`

Implementa `IPasswordService`. Delega a `PasswordEncoder` inyectado por Spring (configurado como BCrypt en `AppConfig`).

Incluye protección contra nulos en `matches()`: si cualquiera de los dos parámetros es nulo, retorna `false` en lugar de lanzar `NullPointerException`.

---

### Validador faltante (`service/validation/`)

#### `PropietarioValidator.java`

Validador centralizado para el perfil del propietario. Faltaba en Etapa 2.

| Método | Responsabilidad |
|---|---|
| `validateNombre(nombre, etiqueta)` | Obligatorio, 2-45 chars, regex letras/espacios/tildes |
| `validateNombreOpcional(nombre, etiqueta)` | Si presente, misma regex; si nulo/vacío, es válido |
| `validateTelefono(telefono)` | Obligatorio, 7-15 dígitos numéricos |
| `validateDireccion(direccion)` | Obligatorio, 5-100 chars, alfanum + `. , # -` |
| `validateCiudadExiste(ciudadId)` | Consulta `ICiudadRepository.existsById` |
| `validatePlanExiste(planId)` | Consulta `IPlanRepository.existsById` |
| `validatePropietario(...)` | Combina todas las validaciones anteriores |

**Reglas extraídas del PHP original** (`propietario_controller.php`):
- Nombres: `/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{2,45}$/`
- Teléfono: `/^[0-9]{7,15}$/`
- Dirección: `/^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s.,#-]{5,100}$/`

**Parámetro `etiqueta`:** Permite mensajes de error contextuales como "El primer nombre es obligatorio" y "El apellido es obligatorio" usando el mismo método de validación.

---

### Capa de DTOs (`dto/`)

Los DTOs son clases planas (sin anotaciones JPA, sin lógica de negocio) que actúan como contrato de datos entre las capas del sistema.

#### Por qué se crean en Etapa 3 (antes de los servicios)

Los servicios de Etapa 4 necesitan saber qué reciben (`*Request`) y qué devuelven (`*DTO`) antes de poder implementarse. Definir los DTOs ahora es equivalente a definir las interfaces de repositorio en Etapa 2: son los contratos de la capa.

#### Convenciones aplicadas

| Convención | Detalle |
|---|---|
| `*Request` | DTO de entrada (lo que llega de la capa Controller) |
| `*DTO` | DTO de salida (lo que devuelve el servicio al Controller) |
| Sin entidades JPA | Ningún DTO importa clases de `domain/` — son planos |
| Jakarta Validation | Los `*Request` tienen `@NotBlank`, `@NotNull`, `@Size` para validación básica de formato |
| Lombok completo | `@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder` en todos |

#### Decisiones de diseño clave

**`LoginResponse.requiresProfileCompletion`:** Implementa la recomendación de la Guía (§5, Módulo 1): el endpoint de login retorna este flag en lugar de redirigir directamente. El frontend decide la navegación.

**`PropietarioDTO` con `ResidenciaDTO` anidado:** Evita el JOIN de 6 tablas del PHP original en una sola respuesta. El servicio resuelve la ubicación completa y la entrega aplanada.

**`UserDetailDTO.propietario` nullable:** Un usuario puede existir sin propietario (nunca completó el registro). El campo se declara como referencia nullable para representar ese estado.

**`DashboardStatsDTO` con lista de actividad:** Corresponde directamente a `getDashboardData()` del PHP original, pero ahora cada campo es resultado de un método específico de `AdminDashboardService` (SRP).

---

## Convenciones Aplicadas

| Convención | Detalle |
|---|---|
| `impl/` sub-paquete | Las implementaciones concretas de interfaces viven en `service/impl/`. Las interfaces permanecen en `service/` |
| `@Primary` en stub | `EmailServiceStub` tiene `@Primary` para ser el candidato de inyección por defecto hasta que exista implementación SMTP real |
| Constructor injection | `PropietarioValidator` y `PasswordServiceBCrypt` usan constructor injection (no `@Autowired` en campo) |
| `IPasswordService` separado | BCrypt no vive en `UserValidator`; cada clase tiene una única responsabilidad |

---

## Criterios de Verificación (Etapa 3)

- `mvn compile` pasa sin errores: todos los paquetes nuevos compilan correctamente.
- Spring context arranca: se detecta un `@Service` para `IEmailService` (`EmailServiceStub`) y uno para `IPasswordService` (`PasswordServiceBCrypt`).
- `PropietarioValidator` tiene exactamente 7 métodos públicos, inyecta solo `ICiudadRepository` e `IPlanRepository` (SRP + DIP).
- Ningún DTO del paquete `dto/` importa clases del paquete `domain/`.
- `Perro.java` tiene `@GeneratedValue(strategy = GenerationType.IDENTITY)`.
- El campo `password` nunca aparece en ningún `*DTO` de respuesta.

---

## Qué NO se hizo (deliberadamente)

Estas responsabilidades pertenecen a etapas posteriores:

- **Etapa 4:** `LoginService`, `RegisterService`, `PropietarioService`, `MascotaService`, `AdminDashboardService`, `LockService`, `PasswordResetService`.
- **Etapa 5:** Controladores REST (`LoginController`, `RegisterController`, etc.) y limpieza de vistas PHP.
- **Sin tocar:** Ningún archivo dentro de `Codigo base/` — esa carpeta es solo referencia.
- **EmailServiceSMTP:** Se implementará cuando el entorno de producción tenga servidor SMTP configurado. El stub es suficiente para las Etapas 4 y 5.
