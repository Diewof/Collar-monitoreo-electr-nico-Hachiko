# Etapa 2 — Interfaces de Repositorio y Validadores

**Fecha:** Abril 2026
**Proyecto:** Hachiko Portal
**Contexto:** Migración de monolito PHP → API REST Spring Boot + PostgreSQL

---

## Objetivo de la Etapa

Definir los **contratos del sistema**: las interfaces de repositorio y los validadores que establecen el contrato entre capas antes de que exista ninguna implementación concreta.

> "Por qué antes de las implementaciones: las interfaces son los contratos. Si se crean después de las implementaciones, los servicios tienden a depender de los detalles de la implementación en lugar del contrato abstracto." — Guía de Refactorización §8.2

---

## Estructura de paquetes creada

```
src/main/java/com/hachiko/portal/
├── domain/                         ← Etapa 1 (COMPLETA)
│   └── enums/
├── repository/                     ← Etapa 2 — interfaces de repositorio (NUEVO)
│   ├── IUsuarioRepository.java
│   ├── ILoginAttemptRepository.java
│   ├── IPasswordResetRepository.java
│   ├── IPropietarioRepository.java
│   ├── IPaisRepository.java
│   ├── IDepartamentoRepository.java
│   ├── ICiudadRepository.java
│   ├── IPlanRepository.java
│   ├── IPerroRepository.java
│   ├── IRazaRepository.java
│   ├── ICollarRepository.java
│   ├── IRegistroSensoresRepository.java
│   └── INotificacionRepository.java
├── service/                        ← Etapa 2 — IEmailService + validadores (NUEVO)
│   ├── IEmailService.java
│   └── validation/
│       ├── ValidationResult.java
│       ├── UserValidator.java
│       └── MascotaValidator.java
└── controller/                     ← Etapa 5
```

> Los paquetes `service/` y `controller/` se poblarán con implementaciones en las etapas siguientes.

---

## Archivos Creados

### Interfaces de repositorio (`repository/`)

Todas las interfaces extienden `JpaRepository<Entidad, TipoId>` de Spring Data JPA.
Spring genera la implementación concreta en tiempo de ejecución — los servicios
nunca necesitan conocer ni instanciar esa implementación.

---

#### Módulo Autenticación

| Interfaz | Entidad | Métodos clave |
|---|---|---|
| `IUsuarioRepository` | `Usuario` | `findByEmail`, `existsByEmail`, `updateRole` (@Modifying), `updateLastLogin`, `updatePassword` |
| `ILoginAttemptRepository` | `LoginAttempt` | `countRecentAttempts` (@Query), `findLastAttemptTime`, `deleteByEmailOrIpAddress`, `deleteOlderThan` |
| `IPasswordResetRepository` | `PasswordReset` | `findByToken`, `findByEmail`, `existsValidTokenForEmail`, `deleteByEmail`, `deleteExpired` |

**Decisiones de diseño:**

- `ILoginAttemptRepository` usa `@Query` con JPQL en vez de métodos derivados porque la condición `(email = X OR ip = Y) AND tiempo > Z` requiere paréntesis que el naming convention no puede expresar directamente.
- `IPasswordResetRepository.existsValidTokenForEmail` recibe `now` como parámetro (no llama a `LocalDateTime.now()` internamente) para permitir tests deterministas.
- Los métodos `@Modifying` en `IUsuarioRepository` evitan cargar el objeto completo solo para actualizar un campo puntual.

---

#### Módulo Propietario y Ubicación

| Interfaz | Entidad | Métodos clave |
|---|---|---|
| `IPropietarioRepository` | `Propietario` | `findByUsuario_Id`, `existsByUsuario_Id` |
| `IPaisRepository` | `Pais` | `findAllByOrderByNombreAsc` |
| `IDepartamentoRepository` | `Departamento` | `findByPais_PaisIdOrderByNombreAsc` |
| `ICiudadRepository` | `Ciudad` | `findByDepartamento_DepartamentoIdOrderByNombreAsc` |
| `IPlanRepository` | `Plan` | `findAllByOrderByCostoAsc` |

**Decisiones de diseño:**

- La jerarquía de ubicación (País → Departamento → Ciudad) se resuelve con métodos de naming convention de Spring Data, que genera la query JOIN automáticamente usando el path de navegación de entidades JPA (`pais.paisId`, `departamento.departamentoId`).
- `IPaisRepository`, `IDepartamentoRepository`, `ICiudadRepository` y `IPlanRepository` son candidatos a `@Cacheable` en la capa de servicio: sus datos son cuasi-estáticos y de alta consulta.
- `IPropietarioRepository.existsByUsuario_Id` permite que `LoginService` retorne el flag `requires_profile_completion` sin cargar el objeto `Propietario` completo.

---

#### Módulo Mascota

| Interfaz | Entidad | Métodos clave |
|---|---|---|
| `IPerroRepository` | `Perro` | `findByPropietario_PropietarioIdOrderByNombreAsc`, `existsByPerroIdAndPropietario_PropietarioId`, `countByPropietario_PropietarioId` |
| `IRazaRepository` | `Raza` | `findAllByOrderByNombreRazaAsc` (+ `existsById` heredado) |

**Decisiones de diseño:**

- `IPerroRepository.existsByPerroIdAndPropietario_PropietarioId` es el punto de control de acceso: `MascotaService` lo invoca antes de cualquier operación de escritura para garantizar que el usuario no pueda modificar mascotas ajenas. Esta verificación **no** vive en el repositorio — el repositorio solo provee el dato; el servicio toma la decisión.
- `IRazaRepository` no necesita métodos adicionales porque `MascotaValidator` usa el `existsById` heredado de `JpaRepository` para verificar existencia de raza.

---

#### Módulo Collar y Sensores

| Interfaz | Entidad | Métodos clave |
|---|---|---|
| `ICollarRepository` | `Collar` | `findByPerro_PerroId`, `existsByPerro_PerroId` |
| `IRegistroSensoresRepository` | `RegistroSensores` | `findByCollar_CollarIdOrderByMarcaTiempoDesc` (con Pageable), `findByCollar_CollarIdAndMarcaTiempoAfterOrderByMarcaTiempoDesc` |

**Decisiones de diseño:**

- `IRegistroSensoresRepository` acepta `Pageable` para controlar el volumen retornado; devolver todas las lecturas de un collar sin límite es inviable con el volumen esperado.
- Las lecturas recientes de un collar son el candidato principal a caché con TTL natural (Redis en etapas posteriores).

---

#### Módulo Notificaciones

| Interfaz | Entidad | Métodos clave |
|---|---|---|
| `INotificacionRepository` | `Notificacion` | `findByPropietario_...OrderByFechaGeneracionDesc`, `findByPropietario_...AndEstado`, `countByPropietario_...AndEstadoNot`, `marcarTodasComoLeidas` (@Modifying) |

---

### Servicio auxiliar (`service/`)

#### `IEmailService.java`

Interfaz con un único método `send(destinatario, asunto, cuerpo)`.

**Propósito:** Desacoplar `RegisterService` y `PasswordResetService` de cualquier implementación concreta de correo (SMTP, SendGrid, etc.). La implementación real se inyecta por el contenedor de IoC.

**Lo que NO hace:** No valida el contenido del correo, no decide cuándo enviarlo — esa lógica pertenece al servicio de dominio que la invoca.

---

### Validadores (`service/validation/`)

#### `ValidationResult.java`

Clase auxiliar que acumula errores de validación. Permite múltiples errores por operación y retorna la lista completa al cliente en una sola respuesta.

Métodos:
- `addError(String)` — agrega un error
- `isValid()` — retorna `true` si no hay errores
- `getErrors()` — lista inmutable de errores
- `getFirstError()` — primer error (para contextos de un solo mensaje)

---

#### `UserValidator.java`

**Fuente única de verdad** para reglas de validación de datos de usuario.

| Método | Responsabilidad |
|---|---|
| `validateEmailFormat(email)` | Formato RFC básico, longitud máxima 255 |
| `validateEmailNotTaken(email)` | Consulta `IUsuarioRepository.existsByEmail` |
| `validatePassword(password)` | Obligatoria, mínimo 8 caracteres |
| `validateRole(role)` | Debe ser uno de los valores del enum `UserRole` |
| `validateNewUser(email, password)` | Combina formato + disponibilidad de email + contraseña |

**Regla demostrable (criterio del Lab):** Si la regla de longitud mínima de contraseña cambia, solo se modifica la constante `PASSWORD_MIN_LENGTH` en este archivo.

---

#### `MascotaValidator.java`

Validador centralizado para datos de mascota (perro).

| Método | Responsabilidad |
|---|---|
| `validateNombre(nombre)` | Obligatorio, máximo 50 caracteres |
| `validateFechaNacimiento(fecha)` | No futura, no anterior a 30 años |
| `validatePeso(peso)` | Entre 0.1 kg y 120 kg |
| `validateGenero(generoStr)` | Solo acepta "M" o "F" (case-insensitive) |
| `validateRazaExiste(razaId)` | Consulta `IRazaRepository.existsById` |
| `validateMascota(...)` | Combina todas las validaciones anteriores |

**Separación lograda respecto al PHP legado:** En `MascotaModel.php`, la validación de la raza era una query SQL interna al modelo, mezclando acceso a datos con validación. Aquí, `MascotaValidator` consulta el repositorio (solo lectura) y el resultado de validación se retorna al servicio antes de cualquier operación de escritura.

---

## Convenciones Aplicadas

| Convención | Detalle |
|---|---|
| **Prefijo `I`** | Todas las interfaces de repositorio llevan el prefijo `I` para distinguirlas de implementaciones (`IUsuarioRepository` vs `UsuarioRepository`) |
| **Spring Data naming** | Los métodos derivados usan la convención de Spring Data (`findBy`, `existsBy`, `countBy`) para que la implementación se genere automáticamente |
| **@Query con JPQL** | Solo cuando el naming convention no puede expresar la condición (OR entre columnas, condiciones complejas) |
| **@Modifying** | Obligatorio en toda operación de escritura declarada con `@Query` |
| **Validadores sin estado** | `UserValidator` y `MascotaValidator` son `@Component` singleton — no guardan estado entre llamadas |
| **ValidationResult** | Los validadores retornan objetos estructurados, no lanzan excepciones genéricas |

---

## Criterios de Verificación (Etapa 2)

- Ninguna interfaz de repositorio contiene lógica de negocio (decisiones como "bloquear cuenta" pertenecen al servicio).
- `UserValidator` no importa ni instancia ninguna clase de servicio de dominio — solo `IUsuarioRepository`.
- `MascotaValidator` no importa ni instancia ninguna clase de servicio — solo `IRazaRepository`.
- Es posible crear una implementación en memoria de cualquier repositorio para tests sin modificar ningún validador ni servicio.
- `IEmailService` es declarada con ese tipo de interfaz en todos los servicios que la usen — nunca la clase concreta.
- `mvn compile` pasa sin errores: los paquetes `repository/`, `service/` y `service/validation/` compilan correctamente.

---

## Qué NO se hizo (deliberadamente)

Estas responsabilidades pertenecen a etapas posteriores:

- **Etapa 3:** Implementaciones concretas de repositorios (si se requiere lógica SQL custom más allá de Spring Data).
- **Etapa 4:** Implementación de `IEmailService` (EmailServiceSMTP o stub); clases de servicio (`LoginService`, `RegisterService`, `PropietarioService`, `MascotaService`, etc.).
- **Etapa 5:** Controladores REST y limpieza de vistas PHP.
- **Sin tocar:** Ningún archivo dentro de `Codigo base/` — esa carpeta es solo referencia.
