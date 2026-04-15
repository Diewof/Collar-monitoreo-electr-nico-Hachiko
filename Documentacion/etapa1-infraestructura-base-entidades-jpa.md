# Etapa 1 — Infraestructura Base y Entidades JPA

**Fecha:** Abril 2026
**Proyecto:** Hachiko Portal
**Contexto:** Migración de monolito PHP → API REST Spring Boot + PostgreSQL

---

## Objetivo de la Etapa

Establecer la fundación de la nueva arquitectura Spring Boot sin tocar el código PHP legado (`Codigo base/`). Los objetivos específicos fueron:

- Eliminar credenciales hardcodeadas (equivalente al `BaseModel.php` con datos en duro)
- Unificar la tecnología de persistencia bajo JPA/Hibernate (equivalente a unificar MySQLi y PDO en PHP)
- Establecer la estructura de paquetes del modelo de 3 capas
- Definir el modelo de dominio completo como entidades JPA a partir del esquema SQL existente

---

## Archivos Modificados

### `pom.xml`

Se agregaron las siguientes dependencias al proyecto Spring Boot 4.0.5 / Java 21:

| Dependencia | Propósito |
|-------------|-----------|
| `spring-boot-starter-data-jpa` | JPA + Hibernate como único driver de persistencia |
| `postgresql` (scope: runtime) | Driver JDBC para PostgreSQL |
| `spring-boot-starter-validation` | Bean Validation (`@NotNull`, `@Email`, etc.) para etapas siguientes |
| `lombok` | Reducción de boilerplate en entidades (getters, setters, constructors) |

También se corrigió el artefacto de test: `spring-boot-starter-webmvc-test` → `spring-boot-starter-test` (el starter correcto para testing de Spring Boot).

---

### `src/main/resources/application.yaml`

Se reemplazó la configuración mínima por una configuración completa orientada a entornos:

```yaml
spring:
  datasource:
    url: ${DB_URL:jdbc:postgresql://localhost:5432/collar}
    username: ${DB_USERNAME:postgres}
    password: ${DB_PASSWORD:}
    driver-class-name: org.postgresql.Driver
  jpa:
    hibernate:
      ddl-auto: validate
    properties:
      hibernate:
        dialect: org.hibernate.dialect.PostgreSQLDialect

server:
  port: ${PORT:8080}
```

**Decisiones clave:**
- Todas las credenciales se leen de variables de entorno. El valor después de `:` es el default para desarrollo local.
- `ddl-auto: validate` — Hibernate valida que el esquema de la BD coincida con las entidades, pero **no modifica** la base de datos. Esto protege contra cambios accidentales en producción.
- Sin credenciales hardcodeadas en ningún archivo del repositorio.

---

## Archivos Creados

### Estructura de paquetes (modelo 3 capas)

```
src/main/java/com/hachiko/portal/
├── domain/                  ← Capa de dominio (Etapa 1 - COMPLETA)
│   └── enums/               ← Tipos enumerados del negocio
├── repository/              ← Contratos de acceso a datos (Etapa 2)
├── service/                 ← Lógica de negocio (Etapa 4)
└── controller/              ← Endpoints REST (Etapa 5)
```

> Los paquetes `repository/`, `service/` y `controller/` se poblarán en las etapas siguientes. Su existencia como paquetes vacíos ya establece el contrato arquitectónico del proyecto.

---

### Enums del dominio (`domain/enums/`)

| Archivo | Valores | Origen en SQL |
|---------|---------|---------------|
| `UserRole.java` | `ADMIN`, `USER` | Columna `role` en tabla `users` |
| `Genero.java` | `M`, `F` | `ENUM('M','F')` en tabla `perro` |
| `EstadoNotificacion.java` | `PENDIENTE`, `ENVIADA`, `LEIDA` | `ENUM(...)` en tabla `notificacion` |

---

### Entidades JPA creadas

#### Módulo Auth y Seguridad

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `Usuario` | `users` | Cuenta de acceso al sistema. Tiene `email` único, `password` (hash), `role` (enum) y marcas de tiempo. |
| `LoginAttempt` | `login_attempts` | Registro de intentos fallidos de login. Permite implementar el bloqueo de cuentas (3 intentos en 15 min). |
| `PasswordReset` | `password_resets` | Token temporal para recuperación de contraseña con fecha de expiración (`expires_at`). |

#### Módulo Ubicación y Planes

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `Pais` | `pais` | País (dato de referencia, ID fijo). |
| `Departamento` | `departamento` | Departamento/Estado, vinculado a `Pais` por `@ManyToOne`. |
| `Ciudad` | `ciudad` | Ciudad, vinculada a `Departamento` por `@ManyToOne`. |
| `Residencia` | `residencia` | Dirección física con ciudad. ID auto-incremental. |
| `Plan` | `plan` | Plan de suscripción con nombre, descripción y costo (dato de referencia, ID fijo). |

#### Módulo Propietario

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `Propietario` | `propietario` | Perfil extendido del usuario como dueño de mascota. Relaciona `Usuario` (1:1), `Residencia` (1:1 con cascade) y `Plan` (N:1). |

#### Módulo Mascota

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `Raza` | `raza` | Catálogo de razas con predisposición a problemas de conducta (dato de referencia). |
| `Perro` | `perro` | Mascota con nombre, fecha de nacimiento, peso, género (enum), estado de esterilización, y vínculos a `Propietario` y `Raza`. |

#### Módulo Collar y Sensores

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `Collar` | `collar` | Dispositivo físico vinculado a un perro (1:1). Tiene versión de firmware, fechas y nivel de batería. |
| `RegistroSensores` | `registro_sensores` | Lectura de sensores (decibelios, frecuencia, aceleración, temperatura, pulsaciones). Candidato principal a caché por volumen. |
| `Emocion` | `emocion` | Tipo de emoción detectada (dato de referencia). |
| `TipoPatron` | `tipo_patron` | Tipo de patrón de comportamiento detectado (dato de referencia). |
| `RegistroComportamiento` | `registro_comportamiento` | Patrón de comportamiento inferido a partir de sensores, con emoción, certeza y duración. |
| `Medio` | `medio` | Recurso multimedia para sugerencias etológicas. |
| `SugerenciaEtologica` | `sugerencia_etologica` | Contenido de manejo etológico asociado a una emoción y un medio. |

#### Módulo Notificaciones

| Clase | Tabla SQL | Descripción |
|-------|-----------|-------------|
| `TipoNotificacion` | `tipo_notificacion` | Categoría de notificación (dato de referencia). |
| `Notificacion` | `notificacion` | Notificación enviada a un propietario con estado (enum) y marca de tiempo. |

---

## Convenciones Aplicadas en Todas las Entidades

| Convención | Detalle |
|------------|---------|
| **Imports JPA** | `jakarta.persistence.*` — Spring Boot 4 usa Jakarta EE 10; NO `javax.persistence` |
| **Lazy loading** | `@ManyToOne(fetch = FetchType.LAZY)` en todas las relaciones para evitar N+1 queries |
| **Enums en BD** | `@Enumerated(EnumType.STRING)` para que la BD almacene el nombre legible, no un índice numérico |
| **IDs auto-increment** | `@GeneratedValue(strategy = GenerationType.IDENTITY)` — delega al motor de BD |
| **IDs de referencia** | Sin `@GeneratedValue` — datos de catálogo con ID asignado manualmente (pais, raza, plan, etc.) |
| **Nombres de columna** | `@Column(name = "...")` explícito en todos los PKs y columnas cuyo nombre difiere del mapeo automático |
| **Lombok** | `@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder` — se evita `@Data` en entidades JPA para no interferir con lazy loading en `toString`/`hashCode` |

---

## Criterios de Verificación (Etapa 1)

- `mvn test` compila y el contexto de Spring arranca correctamente.
- `git grep -r "password" -- "*.java"` no retorna ninguna credencial hardcodeada.
- Ningún archivo `.java` importa `javax.persistence` (debe ser `jakarta.persistence`).
- No existe ningún uso de JDBC manual ni drivers de BD instanciados directamente.
- La aplicación arranca si se proveen las variables `DB_URL`, `DB_USERNAME` y `DB_PASSWORD`.

---

## Qué NO se hizo (deliberadamente)

Estas responsabilidades pertenecen a etapas posteriores y no deben anticiparse:

- **Etapa 2:** Interfaces de repositorio (`IUsuarioRepository`, `IPropietarioRepository`, etc.)
- **Etapa 3:** Implementaciones custom de repositorios (si aplica)
- **Etapa 4:** Clases de servicio (`LoginService`, `PropietarioService`, etc.)
- **Etapa 5:** Controladores REST (`LoginController`, `MascotaController`, etc.)
- **Pendiente:** Configuración de Spring Security (se aborda al crear los servicios de autenticación)
- **Sin tocar:** Ningún archivo dentro de `Codigo base/` — esa carpeta es solo referencia
