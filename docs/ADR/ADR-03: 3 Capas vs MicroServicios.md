
# ADR-003: Selección del Patrón Arquitectónico — Arquitectura en 3 Capas
 
**Estado:** Aceptado 
**Fecha:** 2025-03-20 
**Rama:** `feature/est3-lab3-adr-arquitectura` 
**Commit sugerido:** `docs(adr): crear ADR-003 con decisión de patrón arquitectónico justificada`
 
---
 
## Contexto
 
El proyecto **Hachiko** es un sistema web de monitoreo emocional para mascotas
que permite a dueños de perros registrar sus mascotas, visualizar su estado
emocional y gestionar su perfil. El sistema está desarrollado en PHP con
arquitectura MVC, MySQL como base de datos relacional y un frontend HTML/CSS/JS.
 
El equipo es un grupo académico de estudiantes de Ingeniería de Software que
trabaja en su primer sistema web completo. En el Laboratorio 3 se debe tomar
una decisión formal sobre el patrón arquitectónico que guiará el desarrollo
del resto del proyecto.
 
Los criterios evaluados fueron:
 
- **Experiencia del equipo:** Sin experiencia real con microservicios ni
orquestación de contenedores (Docker Compose, Kubernetes).
- **Dominios del negocio:** Se identificaron 4 dominios
(Autenticación, Propietarios/Usuarios, Mascotas, Administración), pero
todos comparten una única base de datos MySQL con relaciones fuertemente
acopladas mediante claves foráneas en cadena:
`users → propietario → residencia → ciudad → departamento → pais`
y `propietario → perro → raza`.
- **Escalabilidad:** La carga esperada es inferior a 1.000 usuarios
concurrentes en la fase de prototipo. No hay módulos con cargas
diferenciadas que requieran escalado independiente.
- **Tiempo disponible:** Marco de tiempo académico semestral con entregas
por laboratorio. No es viable la complejidad operacional de microservicios.
- **Atributos Must Have (Lab 2):** Seguridad (autenticación con bloqueo por
intentos, gestión de sesiones), Funcionalidad (CRUD completo) y
Mantenibilidad a corto plazo.
 
**Puntaje del cuestionario de evaluación:**
 
| Respuesta | Cantidad | Puntaje |
|-----------|----------|---------|
| A (Microservicios) | 0 | 0 × 2 = 0 |
| B (Neutral) | 1 | 1 × 1 = 1 |
| C (3 Capas) | 12 | 12 × 0 = 0 |
| **TOTAL** | | **1 punto** |
 
Rango 0–9 → **Decisión recomendada: Arquitectura en 3 Capas.**
 
---
 
## Decisión
 
Se adopta la **Arquitectura en 3 Capas (Three-Tier Architecture)** para el
proyecto Hachiko, organizada de la siguiente manera:
 
```
┌─────────────────────────────────────────────────────┐
│ CAPA DE PRESENTACIÓN (Vista) │
│ collar/vista/*.php | collar/css/ | collar/js/ │
│ HTML + CSS + JavaScript → interfaz de usuario │
├─────────────────────────────────────────────────────┤
│ CAPA DE LÓGICA DE NEGOCIO (Controlador) │
│ collar/control/*Controller.php │
│ BaseController, AuthController, AdminController, │
│ MascotaController, PropietarioController, etc. │
├─────────────────────────────────────────────────────┤
│ CAPA DE DATOS (Modelo) │
│ collar/modelo/*Model.php | collar/conexion/ │
│ BaseModel (PDO), AuthModel, AdminModel, │
│ MascotaModel, PropietarioModel, ResidenciaModel │
│ Base de datos MySQL: 'collar' │
└─────────────────────────────────────────────────────┘
```
 
Esta decisión se alinea con la estructura ya implementada en el repositorio:
el código existente sigue el patrón MVC con carpetas `vista/`, `control/` y
`modelo/`, herencia en `BaseController` y `BaseModel`, y una conexión
centralizada en `conexion/conexion.php`.
 
---
 
## Consecuencias
 
### Positivas
 
- **Compatibilidad con el código existente:** La estructura `vista/control/modelo`
ya implementada en el repositorio es la manifestación natural de la
Arquitectura en 3 Capas. No se requiere refactorización mayor.
- **Curva de aprendizaje mínima:** El equipo domina PHP MVC. La adopción formal
de 3 Capas consolida lo que ya se practica.
- **Consistencia transaccional garantizada:** Las operaciones críticas
(registro de propietario + residencia) ya usan `begin_transaction` /
`commit` / `rollback` de MySQLi. En una BD única esto es trivial; en
microservicios requeriría el patrón SAGA.
- **Depuración simplificada:** Los `error_log()` distribuidos por los
controladores son suficientes para trazar errores. No se necesita
distributed tracing.
- **Seguridad centralizada:** El `BaseController` gestiona sesiones PHP y
validación de acceso en un único punto. El manejo de CSRF y bloqueo
por intentos fallidos (`login_attempts`) es coherente sin necesidad de
autenticación federada.
- **Costo operacional bajo:** Un servidor web Apache/Nginx + PHP + MySQL.
No se requieren orquestadores, múltiples instancias ni API Gateway.
- **Entrega académica en tiempo:** El equipo puede enfocarse en la lógica
de negocio (monitoreo emocional, collar IoT) en lugar de infraestructura.
 
### Negativas
 
- **Escalabilidad vertical:** Si el número de usuarios crece
significativamente (>10.000 concurrentes), todo el servidor debe escalar
como unidad. No es posible escalar solo el módulo de análisis emocional
independientemente del de autenticación.
- **Despliegue acoplado:** Una nueva funcionalidad en el módulo de mascotas
requiere desplegar todo el sistema. Si un módulo falla durante el
despliegue, puede afectar a toda la aplicación.
- **Punto único de fallo:** La base de datos MySQL es un SPOF. Si el servidor
de BD cae, toda la aplicación deja de funcionar. Microservicios con BDs
independientes mitigarían esto.
- **Deuda técnica a largo plazo:** Si Hachiko crece a un producto de
producción real con miles de usuarios, será necesaria una migración
hacia microservicios usando el patrón *Strangler Fig*, lo que implicará
un esfuerzo considerable.
- **Menor aislamiento de dominios:** Un bug en el modelo de Mascotas podría
generar inconsistencias que afecten al módulo de Propietarios si no se
manejan correctamente las transacciones.
 
---
 
## Alternativas Consideradas
 
### Alternativa 1: Microservicios
 
**Descripción:** Dividir el sistema en servicios independientes:
`auth-service`, `owner-service`, `pet-service`, `admin-service`,
cada uno con su propia base de datos y expuesto mediante una API REST.
Un API Gateway enruta las peticiones del frontend.
 
**Por qué fue descartada:**
- El equipo no tiene experiencia con orquestación de contenedores
(Docker Compose, Kubernetes).
- La base de datos actual tiene relaciones fuertemente acopladas
(JOINs de 6 tablas en `AdminController::getDashboardData()`).
Separar en BDs independientes requeriría eliminar FK y gestionar
consistencia eventual, complejidad no viable en el tiempo disponible.
- El puntaje del cuestionario fue 1/26, muy por debajo del umbral de 18
requerido para justificar microservicios.
- La carga esperada (<1.000 usuarios) no justifica la complejidad operacional. 
---

