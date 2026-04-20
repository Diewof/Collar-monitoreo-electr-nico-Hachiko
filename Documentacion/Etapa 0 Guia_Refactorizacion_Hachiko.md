# HACHIKO — Guía de Refactorización Arquitectónica
### De Monolito PHP a API REST con Spring + PostgreSQL
*Versión 1.0 | Abril 2026 | Arquitectura de Software — Documento de diseño interno*

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Análisis del Estado Actual](#2-análisis-del-estado-actual)
3. [Identificación de Módulos Funcionales](#3-identificación-de-módulos-funcionales)
4. [Estrategia de Refactorización](#4-estrategia-de-refactorización)
5. [Guía de Migración por Módulo](#5-guía-de-migración-por-módulo)
6. [Tratamiento de Lógica en Vistas](#6-tratamiento-de-lógica-en-vistas)
7. [Reglas Obligatorias de Diseño (SOLID)](#7-reglas-obligatorias-de-diseño-solid)
8. [Orden Global de Ejecución](#8-orden-global-de-ejecución)
9. [Estrategia de Transición](#9-estrategia-de-transición)
10. [Criterios Globales de Finalización — Semana 7](#10-criterios-globales-de-finalización--semana-7)
11. [Apéndice — Resumen Ejecutivo de Deuda Técnica](#11-apéndice--resumen-ejecutivo-de-deuda-técnica)

---

## 1. Introducción

Este documento constituye la guía oficial de refactorización arquitectónica del portal web Hachiko. Su propósito es transformar el sistema monolítico PHP actual —con lógica acoplada en vistas, controladores God-class y dependencias directas sobre implementaciones concretas— en una arquitectura modular, con alta cohesión y bajo acoplamiento, preparada para una migración progresiva hacia una API REST en Spring Boot con persistencia en PostgreSQL.

El análisis se basa en el código fuente real del sistema: vistas PHP (`main.php`, `admin_main.php`, `landing.php`), controladores (`auth_controller.php`, `admin_controller.php`, `BaseController.php`), modelos (`authmodel.php`, `mascota_model.php`, `propietario_model.php`, `BaseModel.php`), y lógica JavaScript dispersa (`main.js`, `auto_logout.php`).

La guía está organizada en bloques ejecutables y no incluye implementación de código, sino decisiones de diseño, secuencias de refactorización y criterios de validación.

---

## 2. Análisis del Estado Actual

### 2.1 Violaciones Identificadas por Categoría

| Problema Identificado | Impacto | Descripción en el sistema actual |
|---|:---:|---|
| `AuthController` realiza login, registro, logout y recuperación de contraseña | **ALTO** | Un solo `switch` en `processRequest()` despacha cuatro flujos completamente distintos. Viola SRP: cada flujo debería vivir en un servicio separado. |
| `AuthController` instancia `PropietarioModel` directamente tras login exitoso | **ALTO** | `require_once + new PropietarioModel()` dentro de `handleLogin()`. Viola DIP: el controlador depende de una implementación concreta, no de una abstracción. |
| `admin_main.php` mezcla autenticación, carga de datos y renderizado HTML | **ALTO** | La vista verifica sesión, instancia `AdminController`, llama `getDashboardData()`, define la función `timeAgo()` y renderiza todo el HTML. Una vista no debería ejecutar lógica de negocio. |
| `BaseModel` tiene credenciales de BD en duro (host, user, password, dbname) | **ALTO** | Credenciales hardcodeadas en el constructor de `BaseModel`. Impide separación de entornos y es un riesgo de seguridad. |
| `propietario_model.php` usa `mysqli` mientras `BaseModel` usa `PDO` | **MEDIO** | Convivencia de dos drivers distintos. Imposible abstraer el acceso a datos con una interfaz común. |
| `logout_controller.php` y `logout.controller.php` son archivos duplicados | **MEDIO** | Dos archivos con lógica idéntica. Cualquier cambio debe replicarse manualmente; fuente de inconsistencias. |
| `auto_logout.php` es un script PHP suelto sin clase ni contexto | **MEDIO** | No pertenece a ningún controlador. Es invocado directamente desde JS. La gestión de sesión debe centralizarse. |
| Temporizador de inactividad (15 min) vive completamente en `main.js` | **MEDIO** | La lógica de expiración de sesión se divide entre el JS del frontend y `auto_logout.php` en backend. No hay fuente única de verdad. |
| `admin_main.php` define `timeAgo()` como función PHP libre dentro de la vista | **BAJO** | Función utilitaria de presentación definida inline. Debería estar en un helper de presentación separado. |
| `main.php` resuelve `propietario_id` con doble consulta a la BD si no está en sesión | **BAJO** | La vista hace consultas de contingencia a la BD directamente. Lógica que pertenece al servicio de sesión/usuario. |

### 2.2 Lógica JavaScript Dispersa

El archivo `main.js` concentra tres responsabilidades que deben ser separadas:

| Lógica actual | Destino |
|---|---|
| Temporizador de inactividad (countdown visual) | UX en frontend; el trigger real de logout debe venir de expiración de JWT en backend |
| Cambio de tema light/dark | Permanece en frontend (lógica puramente de presentación) |
| Carrusel de imágenes | Permanece en frontend |
| `window.location.href` a `auto_logout.php` | Migrar a `POST /api/auth/logout` |

---

## 3. Identificación de Módulos Funcionales

### Módulo 1 — Autenticación (Auth)

**Responsabilidad real:** Gestionar el ciclo completo de identidad del usuario: login, registro, logout, recuperación de contraseña y verificación de bloqueo por intentos fallidos.

**Responsabilidades mezcladas (problema actual):**
- `AuthController` mezcla las cuatro operaciones en un solo `switch`, sin separación por casos de uso.
- La lógica de bloqueo (`isUserLocked`, `recordFailedAttempt`) vive en `AuthModel`, mezclando regla de negocio con acceso a datos.
- La invalidación de sesión está replicada en 3 archivos: `logout_controller.php`, `logout.controller.php` y `auto_logout.php`.
- El registro incluye un flujo de segundo paso (perfil de propietario) que no pertenece al módulo de autenticación.

---

### Módulo 2 — Gestión de Usuarios

**Responsabilidad real:** Administrar el ciclo de vida del usuario: creación, consulta, actualización de rol, cambio de contraseña y eliminación.

**Responsabilidades mezcladas (problema actual):**
- `AdminController` concentra CRUD de usuarios, estadísticas del dashboard, cambio de rol y registro desde el panel, todo en un solo archivo.
- La consulta SQL en `getUserData()` une 6 tablas directamente en el controlador.
- No existe separación entre la vista del administrador y la del usuario regular para operaciones sobre su propio perfil.

---

### Módulo 3 — Propietario

**Responsabilidad real:** Gestionar el perfil extendido del usuario como dueño de mascota: datos personales, ubicación y plan contratado.

**Responsabilidades mezcladas (problema actual):**
- `PropietarioModel` usa `mysqli` directamente (no PDO como el resto), rompiendo la abstracción de acceso a datos.
- La lógica de "primer login" que redirige a completar perfil vive en `auth_controller.php` y en `main.php` simultáneamente.
- La verificación de existencia del propietario se ejecuta dentro del flujo de login y también en `main.php` como contingencia.

---

### Módulo 4 — Mascota (Perro)

**Responsabilidad real:** Gestionar el registro, actualización y consulta de mascotas vinculadas a un propietario.

**Responsabilidades mezcladas (problema actual):**
- `MascotaModel` valida la existencia de la raza con una query SQL interna al modelo, mezclando validación con persistencia.
- La verificación de "tiene mascotas" se hace en `main.php` directamente instanciando `MascotaModel` desde la vista.
- No existe control de acceso: no se verifica que la mascota pertenezca al propietario autenticado.

---

### Módulo 5 — Collar y Sensores

**Responsabilidad real:** Gestionar los collares físicos vinculados a mascotas y el procesamiento de datos de sensores (decibelios, frecuencia, aceleración, temperatura, pulsaciones).

**Responsabilidades mezcladas (problema actual):**
- Las tablas `registro_sensores` y `registro_comportamiento` existen en la BD pero no hay ningún modelo, controlador ni vista que las gestione.
- Este módulo está completamente sin implementar. Debe diseñarse desde cero.
- Es el módulo con mayor volumen de datos futuros; requiere consideración de caché desde el diseño inicial.

---

### Módulo 6 — Administración y Reportes

**Responsabilidad real:** Proveer al rol administrador de capacidades de supervisión: estadísticas de acceso, gestión de cuentas bloqueadas y actividad reciente.

**Responsabilidades mezcladas (problema actual):**
- `getDashboardData()` en `AdminController` acumula 5 consultas de estadísticas distintas en un solo método.
- La función `timeAgo()` de presentación está definida directamente en la vista `admin_main.php`.
- Las secciones "analíticas" y "reportes" están marcadas como "en desarrollo" sin estructura de módulo definida.

---

## 4. Estrategia de Refactorización

### 4.1 Capas y Sus Responsabilidades

#### Capa 1 — Controladores (Controllers)

Responsabilidad única: recibir la solicitud HTTP, delegar al servicio correspondiente y devolver la respuesta. No contienen lógica de negocio, no acceden a la base de datos, no instancian dependencias.

- Reciben y validan el formato de la solicitud entrante (tipos de datos, campos requeridos).
- Delegan el procesamiento a la capa de servicio a través de una interfaz.
- Transforman la respuesta del servicio en el formato HTTP adecuado (JSON, código de estado).
- No deciden flujos de negocio: eso es responsabilidad del servicio.

#### Capa 2 — Servicios (Services)

Responsabilidad única: orquestar la lógica de negocio de un caso de uso específico. No acceden directamente a la base de datos. Conocen repositorios e interfaces, nunca implementaciones concretas.

- Un servicio por caso de uso (`LoginService`, `RegisterService`, no `AuthService` con todo).
- Coordinan validadores, repositorios y servicios auxiliares para completar un flujo.
- Son el único lugar donde vive la regla de negocio (ej: "bloquear cuenta tras 3 intentos fallidos").
- Pueden llamar a otros servicios del mismo módulo; no deben llamar directamente a servicios de otro módulo.

#### Capa 3 — Validadores (Validators)

Responsabilidad única: verificar que los datos de entrada cumplen las reglas de negocio. Son stateless, sin efecto secundario.

- Validación de formato (email válido, campos no vacíos, longitud de contraseña).
- Validación de reglas de negocio previas (ej: "el email no debe existir ya" — consulta al repositorio).
- Devuelven resultados de validación estructurados, no excepciones genéricas.
- No modifican datos, no invocan servicios de dominio.

#### Capa 4 — Repositorios (Repositories)

Responsabilidad única: abstraer completamente el acceso a la base de datos. El servicio nunca escribe SQL directamente.

- Una interfaz de repositorio por entidad (`IUserRepository`, `IMascotaRepository`).
- La implementación concreta (PostgreSQL, en memoria para tests) se inyecta, nunca se instancia directamente.
- Métodos con nombres de negocio: `findByEmail()`, `findByPropietarioId()`, no `executeQuery()`.
- Las consultas costosas son candidatas a caché en esta capa.

#### Capa 5 — Servicios Auxiliares (Support Services)

Responsabilidad única: proveer funcionalidades transversales que no son de dominio.

- **`EmailService`**: envío de correos. Implementado sobre `IEmailService`.
- **`PasswordService`**: hash y verificación con bcrypt. Nunca en el modelo ni en el controlador.
- **`SessionService`**: generación y validación de tokens JWT. Reemplaza toda la gestión de `$_SESSION`.
- **`NotificationService`**: formateo y entrega de mensajes de sistema.

### 4.2 Reglas de Dependencia entre Capas

| Capa | Puede conocer | NO puede conocer directamente |
|---|---|---|
| Controlador | Interfaz de Servicio, DTOs de request/response | Repositorios, Modelos de BD, otras implementaciones |
| Servicio | Interfaces de Repositorio, Interfaces de Servicios Auxiliares, Validadores | Implementaciones concretas de repositorios, otros servicios de dominio directamente |
| Repositorio (interfaz) | Entidades de dominio (no tablas de BD) | Servicios de dominio, otros repositorios, controladores |

---

## 5. Guía de Migración por Módulo

### Módulo 1 — Autenticación

#### Problema actual
- `AuthController.processRequest()` despacha login, registro, logout y recuperación con un `switch`.
- La lógica de bloqueo vive en `AuthModel.isUserLocked()` mezclada con queries SQL.
- La invalidación de sesión está en 3 archivos distintos.
- Tras el login exitoso, `AuthController` instancia `PropietarioModel` directamente (violación DIP).

#### Estructura objetivo

| Componente | Responsabilidad |
|---|---|
| `LoginService` | Recibe credenciales → valida formato → consulta `IUserRepository` → verifica bloqueo → retorna token JWT o error |
| `RegisterService` | Recibe datos → valida con `UserValidator` → persiste con `IUserRepository` → dispara `EmailService` |
| `LogoutService` | Invalida el token JWT. No hay sesión PHP que destruir |
| `LockService` | Registra intentos fallidos, evalúa bloqueo. Persiste con `ILoginAttemptRepository` |
| `PasswordResetService` | Genera token de recuperación → persiste con TTL → envía email con `IEmailService` |

#### Secuencia de refactorización
1. **Centralizar logout**: eliminar `logout_controller.php` y `logout.controller.php`. Crear un único `SessionService`.
2. **Extraer `LockService`**: mover `isUserLocked()`, `recordFailedAttempt()` y `clearFailedAttempts()` fuera de `AuthModel`.
3. **Separar casos de uso del Auth**: dividir `AuthController` en `LoginController`, `RegisterController`, `LogoutController`.
4. **Eliminar dependencia de `PropietarioModel` en Auth**: el flujo post-login delega al frontend; tras login exitoso, el frontend llama a `GET /api/propietario/me`.

#### Riesgos comunes
- **ALTO**: Romper el flujo de redirección post-login si se elimina la lógica de propietario del Auth antes de que el frontend pueda consultarlo.
- La cookie de sesión PHP y el JWT coexistirán temporalmente; definir claramente cuándo se abandona la sesión PHP.

#### Criterios de validación
- Un solo endpoint maneja el logout; `auto_logout.php` no existe en la nueva arquitectura.
- `LoginService` no importa ni instancia ninguna clase de Propietario.
- El bloqueo de cuentas funciona aunque se cambie la implementación del repositorio de intentos.

---

### Módulo 2 — Gestión de Usuarios

#### Problema actual
- `AdminController` concentra CRUD de usuarios, estadísticas, cambio de rol, registro y consulta de datos en un único archivo de más de 300 líneas.
- `getUserData()` ejecuta una query con JOIN de 6 tablas directamente en el controlador.
- Las validaciones (email, contraseña, rol) están duplicadas entre `AuthController` y `AdminController`.

#### Estructura objetivo

| Componente | Responsabilidad |
|---|---|
| `UserService` | Buscar, actualizar email/contraseña/rol, eliminar. Reglas como "no puede eliminarse el último admin" |
| `UserValidator` | Valida email, formato de contraseña, rol permitido. Reutilizable por Auth y Admin |
| `IUserRepository` | `findById()`, `findByEmail()`, `findAll()`, `save()`, `delete()`, `updateRole()` |
| `AdminDashboardService` | Obtiene métricas del dashboard. Candidato a caché |

#### Secuencia de refactorización
1. Extraer `UserValidator`: mover todas las validaciones a una clase independiente.
2. Crear `IUserRepository` con métodos semánticos, implementar con la lógica SQL actual.
3. Crear `UserService`: mover lógica de negocio de `AdminController` al servicio.
4. Separar `AdminDashboardService`: cada consulta de estadística es un método con nombre de negocio.

#### Criterios de validación
- `UserValidator` es la única clase que valida email y contraseña en todo el sistema.
- `AdminController` no contiene ninguna query SQL ni lógica de negocio.
- Es posible sustituir la implementación de `IUserRepository` sin tocar `UserService`.

---

### Módulo 3 — Propietario

#### Problema actual
- `PropietarioModel` usa `mysqli` mientras todos los demás modelos usan PDO. Imposible abstraer con interfaz común.
- La lógica de "primer login" existe en `auth_controller.php` y en `main.php` simultáneamente.
- Actualizar un propietario requiere dos queries en transacción que vive en el modelo, no en un servicio.

#### Estructura objetivo

| Componente | Responsabilidad |
|---|---|
| `PropietarioService` | Orquesta creación y actualización del perfil. Coordina residencia y propietario en transacción |
| `IPropietarioRepository` | `findByUserId()`, `save()`, `update()`. Gestiona la transacción entre tablas |
| `IUbicacionRepository` | `findPaises()`, `findDepartamentosByPais()`, `findCiudadesByDepartamento()`. Candidatos a caché |

#### Secuencia de refactorización
1. Migrar `PropietarioModel` de `mysqli` a PDO extendiendo `BaseModel`.
2. Centralizar lógica de primer login: el endpoint de login retorna flag `requires_profile_completion`; el frontend decide la redirección.
3. Crear `PropietarioService` con transacción: el repositorio ejecuta la persistencia, el servicio orquesta el flujo.

#### Criterios de validación
- Todo el acceso a BD del módulo usa PDO; no hay referencia a `mysqli`.
- `main.php` no instancia `PropietarioModel` ni hace consultas de contingencia.
- Si la actualización de residencia falla, la actualización de propietario se revierte automáticamente.

---

### Módulo 4 — Mascota

#### Problema actual
- `MascotaModel` valida la existencia de la raza con una query SQL interna al modelo.
- La verificación "tiene mascotas" se ejecuta en `main.php` instanciando `MascotaModel` desde la vista.
- No existe control de acceso: no se verifica propiedad antes de modificar una mascota.

#### Estructura objetivo

| Componente | Responsabilidad |
|---|---|
| `MascotaService` | CRUD de mascotas. Verifica que la mascota pertenece al propietario autenticado |
| `MascotaValidator` | Valida género (M/F), peso, fecha de nacimiento y existencia de raza vía `IRazaRepository` |
| `IMascotaRepository` | `findById()`, `findByPropietarioId()`, `save()`, `update()` |
| `IRazaRepository` | `findById()`, `findAll()`. Datos de solo lectura, candidatos a caché |

#### Secuencia de refactorización
1. Extraer `MascotaValidator`: mover validación de raza, género y datos numéricos fuera del modelo.
2. Crear `MascotaService` con control de propiedad: verifica `propietario_id` antes de cualquier escritura.
3. Eliminar instanciación desde vistas: el estado "tiene mascotas" viene de la respuesta del endpoint de perfil.

#### Criterios de validación
- `MascotaModel` no contiene ninguna query de validación; solo persistencia pura.
- Un usuario no puede actualizar una mascota que no le pertenece.
- `IRazaRepository` puede ser reemplazada por una implementación en memoria para tests.

---

### Módulo 5 — Collar y Sensores

#### Problema actual
- El módulo está completamente sin implementar. Las tablas `collar`, `registro_sensores` y `registro_comportamiento` existen en la BD pero no tienen código asociado.
- Es el módulo con mayor volumen de datos esperado.

#### Estructura objetivo — diseñar desde cero

| Componente | Responsabilidad |
|---|---|
| `CollarService` | Vincular/desvincular un collar a una mascota. Verifica unicidad del vínculo |
| `SensorDataService` | Recibir, validar y persistir lecturas de sensores. Debe ser asíncrono o por lotes |
| `ComportamientoService` | Procesar lecturas para inferir emociones |
| `IRegistroSensorRepository` | Candidato principal a caché de lecturas recientes |

#### Consideraciones de caché
- Lecturas de las últimas 24h de un collar: frecuencia alta de consulta, TTL natural.
- Último estado emocional de una mascota: ideal para Redis.
- Listado de razas: catálogo estático, puede cachearse indefinidamente.

#### Criterios de validación
- Un collar solo puede estar vinculado a una mascota a la vez.
- `SensorDataService` puede recibir datos en lotes sin llamar al repositorio una vez por lectura.
- `ComportamientoService` no conoce la implementación de persistencia de sensores.

---

## 6. Tratamiento de Lógica en Vistas

### 6.1 Lógica PHP a Extraer de las Vistas

| Lógica actual en la vista | Destino | Cómo extraerla |
|---|---|---|
| Verificación de sesión (`$_SESSION`) al inicio de cada vista | Middleware de autenticación | Un filtro centralizado verifica el token en cada request |
| Redirección por rol (admin → `admin_main.php`, user → `main.php`) | `LoginService` + respuesta del endpoint | El endpoint de login retorna el rol y URL de redirección; el frontend navega |
| Consulta de contingencia de `propietario_id` en `main.php` | `GET /api/propietario/me` | El frontend consulta este endpoint al cargar el dashboard |
| `timeAgo()` definida en `admin_main.php` | Helper de presentación o frontend | Mover a `DateHelper`; en versión API, calcular en el frontend |
| Verificación de `tieneMascotas()` desde `main.php` | `GET /api/mascotas` (lista vacía = sin mascotas) | El frontend interpreta una lista vacía y muestra el modal |
| Notificaciones via `$_SESSION['notification']` | `NotificationService` o respuesta JSON | La API retorna mensajes en el cuerpo; el frontend los muestra |

### 6.2 Lógica JavaScript — Qué Permanece y Qué Migra

| Lógica JS actual | Decisión | Justificación |
|---|:---:|---|
| Carrusel de imágenes (`main.js`) | ✅ FRONTEND | Lógica puramente de presentación |
| Cambio de tema light/dark | ✅ FRONTEND | Estado de UI del usuario; puede persistirse en `localStorage` |
| Countdown visual del temporizador | ✅ FRONTEND | El contador visual es UX; el mecanismo real de expiración es el TTL del JWT |
| `setTimeout` que llama a `auto_logout.php` | 🔄 MIGRAR | Reemplazar por expiración del JWT: el backend rechaza con 401 y el frontend redirige |
| `window.location.href` a `auto_logout.php` | 🔄 MIGRAR | Reemplazar por llamada a `POST /api/auth/logout` |
| AJAX para `fetchStats` en `AdminController` | ⚠️ FRONTEND + BACKEND | Mantener patrón AJAX; migrar endpoint a `GET /api/admin/stats` |

---

## 7. Reglas Obligatorias de Diseño (SOLID)

### Regla 1 — Una responsabilidad por clase (SRP)
*Derivada de: `AuthController` con 4 casos de uso, `AdminController` con 6, `admin_main.php` con lógica de negocio embebida.*

- Si una clase tiene más de un método público que represente casos de uso distintos, debe dividirse.
- Los controladores solo tienen métodos de manejo de request. Uno por caso de uso.
- Los servicios solo tienen métodos que orquestan su caso de uso principal más helpers privados.
- **Prueba**: si te preguntas "qué hace esta clase", la respuesta debe caber en una frase sin "y".

### Regla 2 — Depender de abstracciones, no de implementaciones (DIP)
*Derivada de: `AuthController` instanciando `PropietarioModel` directamente, `BaseModel` con credenciales hardcodeadas, convivencia de `mysqli` y `PDO`.*

- Todo servicio recibe sus dependencias inyectadas por el contenedor de IoC. Nunca usa `new NombreDeImplementacion()`.
- Las credenciales de BD viven en variables de entorno / `application.properties`. Nunca en el código.
- Cada módulo comunica con otro a través de una interfaz.

### Regla 3 — Prohibición de archivos duplicados o scripts sueltos
*Derivada de: `logout_controller.php` y `logout.controller.php` con lógica idéntica, `auto_logout.php` como script suelto.*

- No puede existir más de un archivo con la misma responsabilidad funcional.
- No existen scripts sueltos invocables por URL que realicen operaciones de sesión, BD o negocio.
- Toda operación invocable desde el cliente pasa por un controlador registrado en el router de la API.

### Regla 4 — Las vistas no ejecutan lógica de negocio ni acceden a la BD
*Derivada de: `main.php` consultando `propietario_id` como contingencia, `admin_main.php` definiendo `timeAgo()` y haciendo `require` de múltiples modelos.*

- En Spring + frontend desacoplado: los componentes del frontend solo consumen respuestas JSON de la API.
- Durante la fase PHP: ninguna vista debe hacer `require_once` de modelos ni instanciar clases de acceso a datos.

### Regla 5 — Validación centralizada y no duplicada
*Derivada de: validaciones de email y contraseña duplicadas en `AuthController` y `AdminController`.*

- Cada tipo de validación tiene un único validador. `UserValidator` es la fuente única de verdad para datos de usuario.
- Los validadores son stateless e inyectables.
- **Prueba**: un cambio en la regla de validación de contraseña afecta exactamente un archivo.

### Regla 6 — Uniformidad de tecnología de persistencia
*Derivada de: `PropietarioModel` usando `mysqli` mientras el resto usa PDO.*

- En la fase PHP: todos los modelos deben extender `BaseModel` y usar PDO. No existe ningún uso de `mysqli`.
- En Spring Boot: JPA/Hibernate es el único mecanismo de acceso a datos, salvo casos documentados en un ADR.

---

## 8. Orden Global de Ejecución

### 8.1 Por qué importa el orden

El sistema Hachiko tiene tres dependencias de orden críticas:

- **`BaseModel` debe estar unificado** (PDO, sin credenciales hardcodeadas) **ANTES** de crear cualquier interfaz de repositorio.
- **Los validadores deben existir ANTES de los servicios.** Un servicio que valida internamente viola SRP desde el día uno.
- **Los repositorios deben estar listos ANTES de los servicios.** Un servicio que no puede inyectar un repositorio tiene que instanciarlo directamente.

### 8.2 Las 5 Etapas en Orden

```
ETAPA 1 → ETAPA 2 → ETAPA 3 → ETAPA 4 → ETAPA 5
  Infra    Interfaces  Repos    Servicios  Controllers
```

---

#### 🔴 ETAPA 1 — Saneamiento de infraestructura base *(prerequisito de todo lo demás)*

**Qué se hace:**
- Unificar driver de BD: migrar `PropietarioModel` de `mysqli` a PDO extendiendo `BaseModel`.
- Eliminar credenciales hardcodeadas de `BaseModel`: moverlas a variables de entorno (`.env`).
- Eliminar archivos duplicados: borrar `logout_controller.php` y `logout.controller.php`; dejar un único `SessionService`.
- Eliminar `auto_logout.php`: centralizar la invalidación de sesión en el `SessionService`.

> **Por qué primero:** Sin un driver unificado no se puede definir `IBaseRepository`. Sin eliminar los duplicados, cualquier refactor del logout genera inconsistencias entre los 3 archivos que aún existen.

---

#### 🟡 ETAPA 2 — Definición de interfaces y validadores *(contratos del sistema)*

**Qué se hace:**
- Definir `IUserRepository` con métodos semánticos (`findByEmail`, `findById`, `save`, `delete`, `updateRole`).
- Definir `IPropietarioRepository` con métodos de negocio (`findByUserId`, `save`, `update`).
- Definir `IMascotaRepository` (`findByPropietarioId`, `findById`, `save`, `update`).
- Crear `UserValidator`: validación de email, contraseña y rol. Fuente única de estas reglas.
- Crear `MascotaValidator`: validación de género, peso, fecha de nacimiento y existencia de raza.
- Definir `IEmailService` con método `send(destinatario, asunto, cuerpo)`.

> **Por qué antes de las implementaciones:** Las interfaces son los contratos. Si se crean después de las implementaciones, los servicios tienden a depender de los detalles de la implementación en lugar del contrato abstracto.

---

#### 🔵 ETAPA 3 — Implementación de repositorios *(acceso a datos aislado)*

**Qué se hace:**
- Implementar `UserRepositoryPDO` implementando `IUserRepository`. Mover las queries de `AuthModel` y `AdminModel`.
- Implementar `PropietarioRepositoryPDO` implementando `IPropietarioRepository`. Migrar la transacción de dos tablas aquí.
- Implementar `MascotaRepositoryPDO` implementando `IMascotaRepository`.
- Implementar `EmailServiceSMTP` implementando `IEmailService` (puede ser un stub que registra en log durante la transición).
- Implementar `LockRepositoryPDO` para la lógica de intentos fallidos y bloqueo de cuentas.

> **Criterio de avance:** Cada repositorio puede ser reemplazado por una implementación en memoria sin tocar ningún otro archivo. Si un cambio en la implementación rompe algo externo, la interfaz no está bien definida.

---

#### 🟢 ETAPA 4 — Construcción de servicios de negocio *(lógica limpia)*

**Qué se hace:**
- Crear `LoginService`: recibe credenciales → llama `UserValidator` → consulta `IUserRepository` → delega bloqueo a `LockService`.
- Crear `RegisterService`: recibe datos → llama `UserValidator` → persiste con `IUserRepository` → notifica con `IEmailService`.
- Crear `LogoutService`: invalida sesión usando `SessionService`.
- Crear `PropietarioService`: orquesta creación/actualización del perfil completo.
- Crear `MascotaService`: gestiona CRUD con control de propiedad (verifica `propietario_id`).
- Crear `AdminDashboardService`: 5 métodos individuales, no un `getDashboardData` monolítico.

> **Orden interno:** Auth primero → Propietario → Mascota → Admin (puede ir en paralelo con Propietario).

---

#### ⚫ ETAPA 5 — Limpieza de controladores y vistas *(capa de presentación desacoplada)*

**Qué se hace:**
- Separar `AuthController` en `LoginController`, `RegisterController`, `LogoutController`.
- Vaciar `AdminController`: delegar cada operación a `UserService` o `AdminDashboardService`.
- Limpiar `main.php` y `admin_main.php`: eliminar `require_once` de modelos, consultas de contingencia y funciones PHP libres.
- Mover `timeAgo()` a un `DateHelper` reutilizable.
- Reemplazar `window.location.href` a `auto_logout.php` en JS por llamada al endpoint de logout.

> **Nota:** Esta etapa tiene mayor riesgo de romper funcionalidad visible. Ver Sección 9 — Estrategia de Transición.

---

## 9. Estrategia de Transición

### 9.1 Principio rector: el sistema debe funcionar en cada commit

Ningún commit debe dejar el sistema en un estado roto. Cada unidad de refactorización debe completarse antes de hacer commit. No se aceptan commits "trabajo en progreso" que rompan login, registro o navegación.

### 9.2 Técnica Strangler Fig por módulo

La estrategia consiste en crear la nueva estructura en paralelo al código antiguo y redirigir el tráfico gradualmente, nunca eliminando el código viejo hasta que el nuevo esté verificado.

**Paso 1 — Crear la clase nueva sin tocar la vieja**
Crear `LoginService` sin modificar `AuthController`. El sistema sigue funcionando exactamente igual.

**Paso 2 — Conectar el controlador al servicio nuevo manteniendo el comportamiento**
Modificar `AuthController.handleLogin()` para llamar a `LoginService`. Verificar manualmente que el login funciona. Si falla, el rollback es trivial.

**Paso 3 — Eliminar el código viejo solo cuando el servicio esté verificado**
Una vez confirmado que `LoginService` produce los mismos resultados, eliminar la lógica duplicada en `AuthController`. Commit.

**Paso 4 — Repetir por cada caso de uso, no por módulo completo**
No refactorizar Auth completo de una vez. Primero login, commit, verificar. Luego registro, commit, verificar.

### 9.3 Protocolo para cambios de alto riesgo

#### ⚠️ Cambio de alto riesgo 1: Unificación de driver (mysqli → PDO en PropietarioModel)

1. Crear `PropietarioRepositoryPDO` que extiende `BaseModel`. No eliminar `PropietarioModel` aún.
2. En el controlador que usa `PropietarioModel`, crear una bandera de feature flag temporal: si `$usarNuevoRepo = true`, usar el nuevo; si no, usar el viejo.
3. Activar el nuevo repositorio solo en el flujo de actualización de perfil (el de menor tráfico). Verificar en entorno local con datos reales.
4. Extender al resto de flujos. Al completar todos, eliminar `PropietarioModel` y el feature flag.

#### ⚠️ Cambio de alto riesgo 2: Separación de AuthController

1. Crear `LoginController` vacío que extiende `BaseController`. No modificar el router aún.
2. Mover `handleLogin()` a `LoginController`. Mantener el método en `AuthController` delegando al nuevo: `$this->loginController->handleLogin()`.
3. Actualizar el formulario HTML para que apunte al nuevo `LoginController` directamente.
4. Verificar todos los casos (credenciales incorrectas, cuenta bloqueada). Eliminar la delegación de `AuthController`.

#### ⚠️ Cambio de alto riesgo 3: Limpieza de vistas (main.php, admin_main.php)

1. Antes de eliminar cualquier `require_once` o instanciación, verificar que el servicio correspondiente ya está disponible y probado.
2. Eliminar de a una dependencia por commit. No limpiar toda la vista en un solo commit.
3. Las consultas de contingencia (buscar `propietario_id` si no está en sesión) deben eliminarse solo después de confirmar que el flujo de login siempre establece la sesión correctamente.

### 9.4 Gestión de ramas Git para la transición

| Rama | Propósito y regla |
|---|---|
| `main` / `develop` | El sistema siempre compila y el login/registro funcionan. Nunca hacer merge de código roto |
| `refactor/etapa-1-infra` | Saneamiento de BaseModel, unificación de driver, eliminación de duplicados. Un PR por ítem |
| `refactor/etapa-2-interfaces` | Definición de interfaces y validadores. Riesgo mínimo: son archivos nuevos |
| `refactor/etapa-3-repos` | Implementaciones de repositorios. Cada repositorio en su propio PR |
| `refactor/etapa-4-servicios` | Un PR por servicio: `login-service`, `register-service`, `propietario-service`, etc. |
| `refactor/etapa-5-controllers` | Limpieza de controladores y vistas. El PR más riesgoso: requiere revisión funcional manual antes del merge |

---

## 10. Criterios Globales de Finalización — Semana 7

### 10.1 Entregables mínimos del Lab 6

Los siguientes archivos son los entregables explícitos definidos en el plan de estudios. Sin estos, el lab no está completo independientemente de la calidad del resto del código:

| # | Archivo / Entregable | Responsable | Principio SOLID demostrado |
|:---:|---|---|---|
| 1 | `src/services/UserValidator.php` — valida email, contraseña y rol | Estudiante 1 | SRP: solo valida, no persiste ni autentica |
| 2 | `src/repositories/IUserRepository.php` — interfaz con métodos semánticos | Estudiante 2 | DIP: define el contrato abstracto |
| 3 | `src/repositories/UserRepositoryPDO.php` — implementación de `IUserRepository` | Estudiante 2 | DIP: implementación concreta sustituible |
| 4 | `src/services/EmailService.php` — envío de correos sobre `IEmailService` | Estudiante 3 | DIP + SRP: desacoplado y con una sola responsabilidad |
| 5 | `docs/adr/ADR-001-solid.md` — decisiones de diseño documentadas | Arquitecto | Trazabilidad de decisiones |
| 6 | PR de cada estudiante revisado y aprobado por un compañero | Todos | Garantía de calidad entre pares |

### 10.2 Criterios de corrección arquitectónica

Estos criterios se verifican durante la revisión de PR. Un PR que incumple cualquiera de estos puntos debe ser rechazado y corregido antes del merge.

#### SRP — Cada clase tiene una sola razón para cambiar
- `UserValidator` no contiene lógica de persistencia ni redirecciones.
- `UserRepositoryPDO` no contiene reglas de negocio (no decide si el email es "válido", solo lo busca).
- `EmailService` no valida el contenido del correo ni construye la lógica de cuándo enviarlo.
- **Prueba:** si se cambia la regla de longitud mínima de contraseña, solo `UserValidator` se modifica.

#### DIP — Dependencias apuntan hacia abstracciones, no implementaciones
- Todo servicio que use un repositorio recibe una instancia de `IUserRepository`, no de `UserRepositoryPDO`.
- `EmailService` se declara con el tipo `IEmailService` en todos los servicios que lo usen.
- **Prueba:** es posible crear un `UserRepositoryInMemory` para tests sin modificar ningún servicio.

#### Alta cohesión — Los métodos de cada clase se relacionan entre sí
- `UserValidator` solo tiene métodos de validación. No tiene métodos de persistencia ni formateo.
- `IUserRepository` solo tiene métodos de acceso a datos de usuario. No tiene métodos de estadísticas.
- **Prueba:** el nombre de la clase describe exactamente todos los métodos que contiene.

#### Bajo acoplamiento — Los módulos no se conocen directamente
- `UserValidator` no importa ninguna clase de repositorio directamente.
- `EmailService` no conoce la existencia de `UserService` ni de `AuthService`.
- Ningún servicio hace `require_once` de la implementación concreta de un repositorio.
- **Prueba:** es posible reusar `UserValidator` en otro contexto sin modificarlo.

### 10.3 Señales de alerta — el refactor está incompleto

> 🚨 `UserValidator` contiene un `require_once` o `new` de cualquier clase de repositorio o modelo.

> 🚨 `UserRepositoryPDO` no implementa `IUserRepository` o implementa métodos adicionales no definidos en la interfaz.

> 🚨 `EmailService` es invocado con `new EmailService()` desde un servicio o controlador (debe ser inyectado).

> ⚠️ El `ADR-001-solid.md` no explica por qué se aplicó SRP y DIP en estos archivos (solo describe qué se hizo, no por qué).

> ⚠️ Existe un PR sin revisión de un compañero (la revisión entre pares es parte del criterio de finalización, no opcional).

> ⚠️ Los tres archivos entregables no coexisten en la misma rama al cierre de la semana.

### 10.4 Checklist de cierre — Semana 7

```
□  UserValidator.php existe, no tiene dependencias de BD y es llamado desde al menos
   un controlador o servicio.

□  IUserRepository.php define la interfaz; UserRepositoryPDO.php la implementa
   con PDO (no mysqli).

□  EmailService.php implementa IEmailService; ningún servicio lo instancia
   directamente con new.

□  ADR-001-solid.md documenta: contexto del problema, decisión tomada,
   consecuencias y alternativas consideradas.

□  Cada PR tiene al menos un comentario de revisión de un compañero
   (aprobación o solicitud de cambio con justificación).

□  El login y el registro del sistema PHP funcionan igual que antes del refactor
   (prueba manual en navegador).

□  No existen archivos duplicados de logout en el repositorio.

□  Las credenciales de BD no aparecen en ningún archivo del repositorio
   (verificar con: git grep -r "password" -- "*.php").
```

---

## 11. Apéndice — Resumen Ejecutivo de Deuda Técnica

Prioridades de acción organizadas por urgencia para el equipo de desarrollo:

| # | Acción | Módulo | Prioridad |
|:---:|---|---|:---:|
| 1 | Centralizar logout: eliminar `logout_controller.php`, `logout.controller.php` y `auto_logout.php` | Auth | 🔴 ALTA |
| 2 | Migrar `PropietarioModel` de `mysqli` a PDO para habilitar interfaz de repositorio común | Propietario | 🔴 ALTA |
| 3 | Mover credenciales de `BaseModel` a variables de entorno | Global | 🔴 ALTA |
| 4 | Separar casos de uso de `AuthController` en controladores individuales | Auth | 🔴 ALTA |
| 5 | Extraer `UserValidator` y eliminar validaciones duplicadas entre Auth y Admin | Usuarios | 🟡 MEDIA |
| 6 | Extraer `LockService` fuera de `AuthModel` | Auth | 🟡 MEDIA |
| 7 | Separar `getDashboardData()` en `AdminDashboardService` con métodos individuales | Admin | 🟡 MEDIA |
| 8 | Eliminar instanciación de `MascotaModel` y `PropietarioModel` desde vistas PHP | Mascota/Prop. | 🟡 MEDIA |
| 9 | Extraer `MascotaValidator` con validación de raza a través de interfaz | Mascota | 🟡 MEDIA |
| 10 | Diseñar `ICollarRepository` e `IRegistroSensorRepository` desde cero antes de implementar | Collar | 🟢 BAJA |
| 11 | Mover `timeAgo()` a un `DateHelper` separado | Admin | 🟢 BAJA |
| 12 | Reemplazar `setTimeout` de inactividad JS por expiración de token JWT en el backend | Auth | 🟢 BAJA* |

*El ítem 12 tiene prioridad BAJA en la fase PHP pero debe implementarse en la primera iteración de Spring Boot.*

---

*Hachiko — Guía de Refactorización Arquitectónica v1.0 | Abril 2026*
