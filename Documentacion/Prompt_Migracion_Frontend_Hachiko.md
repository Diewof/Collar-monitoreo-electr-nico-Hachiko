# Prompt — Guía de Migración del Frontend Hachiko
## Integración con Hachiko API REST (Spring Boot + PostgreSQL)

---

## Rol y Contexto

Actúa como un **Arquitecto de Software Senior con experiencia en migración de frontends** acoplados a backends monolíticos hacia arquitecturas desacopladas que consumen APIs REST.

Tu objetivo es **definir cómo transformar las vistas PHP/HTML/JS del portal Hachiko** en un frontend moderno, desacoplado e integrado con la API REST de Hachiko que ya existe en producción.

### Lo que ya existe (NO se toca)

La API REST de Hachiko está construida en **Spring Boot + PostgreSQL** y expone los siguientes contratos que el frontend DEBE consumir:

**Autenticación — públicos (sin token):**
- `POST /api/auth/login` → retorna `{ userId, email, role, token, requiresProfileCompletion }`
- `POST /api/auth/register` → retorna `UsuarioDTO`
- `POST /api/auth/logout` → requiere token JWT
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`

**Propietario — requieren token JWT:**
- `GET  /api/propietario/me` → retorna `PropietarioDTO` con residencia anidada
- `POST /api/propietario` → crea perfil por primera vez
- `PUT  /api/propietario/{propietarioId}` → actualiza perfil

**Mascotas — requieren token JWT:**
- `GET    /api/mascotas` → lista mascotas del propietario autenticado
- `GET    /api/mascotas/{perroId}`
- `POST   /api/mascotas`
- `PUT    /api/mascotas/{perroId}`
- `DELETE /api/mascotas/{perroId}`

**Catálogos — públicos:**
- `GET /api/referencia/paises`
- `GET /api/referencia/departamentos?paisId={id}`
- `GET /api/referencia/ciudades?departamentoId={id}`
- `GET /api/referencia/razas`
- `GET /api/referencia/planes`

**Administración — requieren rol ADMIN:**
- `GET/POST/PUT/DELETE /api/admin/**`

**Seguridad:** La API usa JWT stateless. El token se envía en el header `Authorization: Bearer <token>`. No hay sesiones PHP del lado del servidor. La API retorna `401` cuando el token expira o es inválido.

### Lo que hay que reemplazar (el frontend actual)

El frontend actual es PHP/HTML con lógica acoplada distribuida en:
- `collar/vista/landing.php` — página pública
- `collar/vista/login-registro.php` — formularios de auth
- `collar/vista/main.php` — dashboard del usuario (con lógica de sesión PHP y consultas de contingencia a la BD)
- `collar/vista/admin_main.php` — panel admin (con función `timeAgo()` inline, estadísticas embebidas)
- `collar/js/main.js` — lógica UI mezclada con temporizador de inactividad que llama a `auto_logout.php`
- `collar/js/admin.js` — validaciones de formulario duplicadas con el backend
- `collar/control/auto_logout.php` — script suelto invocado desde JS

---

## Instrucciones

### 1. Análisis del estado actual del frontend

Identifica en el código PHP/JS existente:

- **Lógica de presentación** que debe permanecer en el frontend (carrusel, tema dark/light, contadores visuales).
- **Lógica de negocio** incrustada en vistas que la API ya resuelve (verificación de sesión, rol, existencia de propietario, consultas a BD desde la vista).
- **Lógica de navegación acoplada** a sesiones PHP que debe reemplazarse por navegación basada en el JWT y el campo `requiresProfileCompletion` de `LoginResponse`.
- **Duplicación de validaciones** entre `admin.js`/formularios PHP y los validadores del backend (UserValidator, MascotaValidator).
- **Dependencias a eliminar**: `auto_logout.php`, `logout_controller.php`, manejo de `$_SESSION` en vistas.

Clasifica cada problema por impacto: **alto** (bloquea la integración), **medio** (genera inconsistencias), **bajo** (deuda técnica tolerable).

### 2. Identificación de módulos del frontend

Divide el nuevo frontend en módulos funcionales alineados con los módulos de la API. Para cada módulo define:

- Su **responsabilidad real** como componente de interfaz (no la actual).
- Las **responsabilidades mezcladas** que deben separarse (ej: `main.php` mezcla guard de sesión + consulta de propietario + renderizado).
- El **contrato de datos** que consume: qué endpoint llama, qué DTO espera, qué estados de error debe manejar (`401`, `403`, `404`, `422`).

Los módulos mínimos son: Autenticación, Perfil de Propietario, Mascotas, Administración, Catálogos y Navegación/Sesión.

### 3. Estrategia de integración con la API

Define cómo el frontend se conecta con la API ya existente:

- **Gestión del token JWT**: dónde se almacena tras el login (localStorage o cookie `HttpOnly`), cómo se adjunta a cada request, cómo se detecta su expiración (`401`).
- **Reemplazo del temporizador de inactividad**: el `setTimeout` de 15 minutos en `main.js` que redirigía a `auto_logout.php` debe ser reemplazado por detección de `401` en cualquier llamada a la API. Define el flujo.
- **Flujo de primer login**: `LoginResponse.requiresProfileCompletion = true` indica que el usuario debe completar el perfil antes del dashboard. Define cómo el frontend navega a ese flujo sin depender de `$_SESSION['is_first_login']`.
- **Navegación por rol**: la API retorna el `role` en el `LoginResponse`. Define cómo el frontend enruta al dashboard de usuario o al panel de administración sin lógica PHP condicional.
- **Manejo centralizado de errores de API**: un interceptor o servicio único que procese los códigos `401`, `403`, `404`, `422` y muestre los mensajes correspondientes, reemplazando el sistema de `$_SESSION['notification']` del `BaseController.php`.

### 4. Guía de migración por módulo

Para cada módulo del frontend (Autenticación, Propietario, Mascotas, Administración, Catálogos):

- **Problema actual**: qué hace el archivo PHP/JS actual y por qué está mal estructurado.
- **Estructura objetivo**: qué componentes/páginas/servicios lo reemplazan y cómo se comunican.
- **Mapping de endpoints**: qué llamadas concretas a la API reemplazan cada lógica PHP actual.
- **Secuencia de migración paso a paso**: en qué orden ejecutar los cambios sin romper la funcionalidad.
- **Riesgos comunes**: qué puede romperse en cada paso y cómo prevenirlo.
- **Criterios de validación**: cómo saber que el módulo está correctamente migrado (qué flujo funciona, qué ya no existe en el frontend).

### 5. Separación definitiva de responsabilidades

Define con precisión qué queda en el frontend y qué queda exclusivamente en la API:

**Permanece en el frontend:**
- Renderizado de componentes UI (carrusel, tema dark/light, animaciones).
- Validación de formato en formularios (campos vacíos, formato de email) — feedback inmediato antes de llamar a la API.
- Almacenamiento y gestión del token JWT en el cliente.
- Lógica de navegación entre páginas basada en el estado del token y el rol.
- Countdown visual de inactividad (solo decorativo; la seguridad real la da el JWT).

**Se elimina del frontend (ya lo resuelve la API):**
- Toda verificación de sesión (`$_SESSION`, `isset($_SESSION['is_logged_in'])`).
- Toda consulta directa a la BD desde vistas o scripts JS.
- Lógica de bloqueo de cuenta (maneja `LockService` en el backend).
- Validación de existencia de propietario en `main.php` (responde `GET /api/propietario/me`).
- Redireccionamiento por rol desde PHP (lo define el `role` en el JWT).
- `auto_logout.php` — no existe en la nueva arquitectura.

**No se duplica:**
- Las reglas de negocio (contraseña mínima 8 caracteres, rol válido) están en `UserValidator.java`. El frontend puede hacer validación de formato, pero la **fuente de verdad** es el backend. Si el backend retorna `422`, el frontend muestra el error sin re-validar.

### 6. Reglas obligatorias de integración

Define las reglas no negociables que gobiernan la integración frontend ↔ API:

- **Una sola fuente de verdad para el estado de sesión**: el JWT y los datos devueltos por la API. Ningún estado de sesión del lado PHP.
- **Toda llamada autenticada lleva el header `Authorization: Bearer <token>`**. No hay cookies de sesión PHP.
- **El frontend no asume el rol del usuario sin consultar el token**. Si el token no contiene el rol, se llama a la API.
- **Ningún módulo del frontend hace llamadas directas a la BD**. Toda interacción con datos pasa por un endpoint de la API.
- **Las validaciones del frontend son de formato, no de negocio**. La lógica de negocio vive en el backend; el frontend solo mejora la experiencia de usuario.
- **Los errores de la API no se ignoran**. Un `401` siempre redirige al login. Un `403` muestra pantalla de acceso denegado. Un `422` muestra los errores de validación del campo correspondiente.

### 7. Orden global de ejecución

Define las etapas de migración del frontend en orden, indicando por qué cada etapa es prerequisito de la siguiente. Las etapas deben abarcar:

1. Configuración del cliente HTTP y gestión del JWT.
2. Módulo de autenticación (login, registro, logout, recuperación).
3. Flujo de primer login y perfil de propietario.
4. Dashboard de usuario y módulo de mascotas.
5. Panel de administración.
6. Limpieza final: eliminación de archivos PHP obsoletos.

### 8. Estrategia de transición

Define cómo ejecutar la migración sin romper el acceso al sistema durante el proceso. Incluye:

- Cómo coexisten temporalmente el frontend PHP y el nuevo frontend durante la transición.
- Cómo se gestiona la rama Git de cada etapa.
- El criterio para dar de baja cada archivo PHP (cuándo es seguro eliminarlo).
- Qué pruebas manuales ejecutar antes de cada merge.

### 9. Criterios globales de finalización

Define cómo saber que la migración del frontend está completa. Incluye:

- Lista de archivos PHP que deben haber sido eliminados.
- Lista de flujos que deben funcionar correctamente consumiendo la API.
- Checklist de integración verificable por el equipo.
- Señales de alerta que indican que la migración está incompleta.

---

## Restricciones del entregable

- **Sin código**: solo decisiones de diseño, mapeos de endpoints, secuencias de pasos y criterios de validación.
- **Sin teoría**: cada sección responde "qué hacer", no "qué es".
- **Anclado en lo que ya existe**: cada decisión de frontend debe referenciar el contrato de la API que la soporta (endpoint, DTO, código de estado).
- **Progresivo**: el documento debe poder ejecutarse etapa por etapa sin necesidad de leer todo antes de empezar.
- **Sin replicar errores**: ninguna decisión de diseño del nuevo frontend debe reproducir los antipatrones del monolito PHP (lógica en vistas, validaciones duplicadas, scripts sueltos).
