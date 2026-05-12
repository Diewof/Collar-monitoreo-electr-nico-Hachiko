# ADR-05: Dockerización, Infraestructura y CI/CD

Estado: Aceptado

Fecha: 2026-05-12

---

## Contexto

El portal Hachiko requería un entorno reproducible de despliegue que eliminara la dependencia del entorno local del desarrollador, garantizara paridad entre desarrollo y producción, y automatizara el ciclo de integración y despliegue continuo. El proyecto es un monorepo con backend Spring Boot (Java 21) y frontend React + TypeScript (Vite), con dependencias externas de PostgreSQL y Redis.

---

## Decisión

Se implementó una estrategia de contenedores con **Docker multi-stage** para backend y frontend, **Docker Compose** para orquestar el entorno local completo, despliegue en **Railway** como plataforma cloud, y un pipeline de **GitHub Actions** que ejecuta linter e integration tests en cada PR hacia `features`. Railway auto-deploya al detectar el merge en esa rama.

---

## Componentes Implementados

### 1. Dockerfile Backend — Multi-stage (`Dockerfile`)

**Qué hace:**
- **Stage 1 (builder):** Descarga dependencias Maven en capa separada (cacheada mientras `pom.xml` no cambie), luego compila y empaqueta el JAR con `-DskipTests`.
- **Stage 2 (runtime):** Imagen mínima `eclipse-temurin:21-jre-alpine`, corre como usuario no-root (`appuser`) por seguridad.

**¿Cumple su función?** Sí, completamente.

- Imagen base `eclipse-temurin:21-jdk-alpine` para build y `21-jre-alpine` para runtime — reduce el tamaño final eliminando el JDK.
- Usuario no-root evita escalada de privilegios si la aplicación es comprometida.
- La separación de capas de dependencias vs código acelera los rebuilds cuando solo cambia el código fuente.
- Puerto expuesto: `8080`.

---

### 2. Dockerfile Frontend — Multi-stage (`FrontEnd/Dockerfile`)

**Qué hace:**
- **Stage 1 (builder):** Instala dependencias npm y ejecuta `npm run build` generando los assets estáticos en `/app/dist`.
- **Stage 2 (runtime):** Imagen `nginx:stable-alpine` sirve los assets y actúa como reverse proxy al backend.

**¿Cumple su función?** Sí, completamente.

- `npm ci --silent` garantiza instalación reproducible desde `package-lock.json`.
- Nginx sirve la SPA con soporte para rutas de React (`try_files $uri /index.html`).
- `envsubst` reemplaza `${BACKEND_URL}` en la configuración de Nginx al arrancar el contenedor, haciendo la URL del backend configurable por variable de entorno.
- Puerto expuesto: `80`.

---

### 3. Configuración Nginx (`FrontEnd/nginx.conf`)

**Qué hace:**
- Sirve los assets estáticos de React con compresión Gzip.
- Actúa como reverse proxy para `/api/` y `/actuator/` hacia el backend.
- Soporta SPA routing enviando todas las rutas desconocidas a `index.html`.

**¿Cumple su función?** Sí, completamente.

- URL del backend inyectada via `${BACKEND_URL}` con `envsubst` — evita hardcodear la URL en la imagen.
- En Railway, la comunicación interna usa `http://backend.railway.internal:8080` (red privada).
- En Docker Compose local, usa `http://backend:8080` (red Docker interna).
- `resolver 127.0.0.11 valid=10s ipv6=off` fuerza re-resolución DNS en cada request. Necesario en Railway porque cuando un merge dispara auto-deploy simultáneo de backend y frontend, nginx cachea la IP del backend al arrancar y luego el backend obtiene una nueva IP — sin el resolver, todas las peticiones fallan con timeout hasta el próximo redeploy del frontend.

---

### 4. Docker Compose — Entorno Local (`docker-compose.yml`)

**Qué hace:**
- Orquesta los 4 servicios: `db` (PostgreSQL 16), `redis` (Redis 7), `backend` (Spring Boot), `frontend` (React + Nginx).
- Define el orden de arranque con `depends_on` y `healthcheck` para garantizar que la BD y Redis estén listos antes de arrancar el backend.
- Monta `init.sql` como script de inicialización de PostgreSQL.

**¿Cumple su función?** Sí, completamente.

- Todas las credenciales y configuraciones son variables de entorno con valores por defecto para desarrollo local.
- Healthchecks en todos los servicios con `pg_isready` (Postgres) y `redis-cli ping` (Redis).
- Volúmenes persistentes para datos de Postgres y Redis entre reinicios.

---

### 5. Despliegue en Railway

**Qué hace:**
- Hospeda los 4 servicios (Backend, Frontend, PostgreSQL, Redis) como contenedores independientes en la misma red privada de Railway.
- PostgreSQL y Redis son plugins gestionados por Railway (provisioning automático).
- Backend y Frontend se despliegan desde el repositorio GitHub usando sus respectivos Dockerfiles.

**¿Cumple su función?** Sí, completamente.

**Configuración de variables de entorno (Backend):**

| Variable | Valor |
|---|---|
| `SPRING_PROFILES_ACTIVE` | `docker` |
| `DB_URL` | `jdbc:postgresql://${{Postgres.PGHOST}}:${{Postgres.PGPORT}}/${{Postgres.PGDATABASE}}` |
| `DB_USERNAME` | `${{Postgres.PGUSER}}` |
| `DB_PASSWORD` | `${{Postgres.PGPASSWORD}}` |
| `REDIS_HOST` | `${{Redis.REDISHOST}}` |
| `REDIS_PORT` | `${{Redis.REDISPORT}}` |
| `REDIS_PASSWORD` | `${{Redis.REDISPASSWORD}}` |
| `JWT_SECRET` | secret de producción (mín. 32 chars) |
| `CORS_ALLOWED_ORIGINS` | URL pública del frontend en Railway |
| `SPRING_JPA_HIBERNATE_DDL_AUTO` | `update` |

**Configuración de variables de entorno (Frontend):**

| Variable | Valor |
|---|---|
| `BACKEND_URL` | `http://backend.railway.internal:8080` |
| `PORT` | `80` |

- La comunicación entre Frontend y Backend usa la red privada de Railway (`*.railway.internal`), sin exponer el backend directamente a internet.
- El schema de la BD se inicializa ejecutando `init.sql` manualmente en el editor de queries de Railway (PostgreSQL Data tab) en el primer despliegue.

---

### 6. Pipeline CI/CD — GitHub Actions (`.github/workflows/ci.yml`)

**Qué hace:**
- Se dispara en cada PR hacia `features` y en cada push a `features` (incluye merges).
- Ejecuta tres jobs: **Lint Backend**, **Lint Frontend** (en paralelo) y **Integration Tests** (espera a que lint-backend pase).
- El deploy lo hace Railway automáticamente al detectar el push a `features` — GitHub Actions actúa como guardián de calidad.

**Job: Lint Backend**
- Java 21, Maven. Corre `mvn compile` para detectar errores de compilación.

**Job: Lint Frontend**
- Node 20. Corre `npm ci` + `npm run build` (`tsc && vite build`) para verificar tipos TypeScript y que el bundle compila.

**Job: Integration Tests**
- Corre `mvn test` con Testcontainers: levanta PostgreSQL 16 y Redis 7 reales en Docker dentro del runner.
- Tests incluidos:
  - `contextLoads()` — verifica que el contexto de Spring arranca con BD y Redis reales.
  - `register_withValidData_returns201` — registro exitoso devuelve 201 con email en respuesta.
  - `login_withValidCredentials_returns200WithToken` — login correcto devuelve 200 con JWT no vacío.
  - `login_withWrongPassword_returns401` — contraseña incorrecta devuelve 401.
- Si algún test falla, el pipeline falla y bloquea el merge del PR.

**¿Cumple su función?** Sí, completamente.

- No se requieren GitHub Secrets para el pipeline — el deploy lo gestiona Railway directamente.
- El pipeline completo (lint + tests) tarda aproximadamente 3-5 minutos.

---

## Consecuencias

### Positivas
- Paridad total entre entornos: el mismo Dockerfile que corre en local corre en producción.
- El pipeline bloquea merges con errores de compilación o tests fallidos antes de llegar a producción.
- Las variables de entorno están completamente externalizadas — ninguna credencial en el código fuente.
- Railway reference variables (`${{Postgres.PGHOST}}`) eliminan hardcodeo de URLs de infraestructura.
- La red privada de Railway (`railway.internal`) evita exponer el backend directamente a internet.
- Multi-stage builds producen imágenes mínimas: el runtime del backend no incluye el JDK ni las herramientas de build.

### Negativas
- El schema de la BD debe inicializarse manualmente en el primer despliegue (ejecutar `init.sql` via Railway Data tab). No está automatizado en el pipeline.
- `ddl-auto=update` en Railway puede causar migraciones inesperadas si se renombran entidades — en producción real debería usarse Flyway o Liquibase.
- El plan gratuito de Railway (~$5 USD/mes de crédito) se agota en 2-3 semanas con los 4 servicios corriendo 24/7.
- La blacklist de tokens JWT es en memoria — un redeploy del backend la limpia, permitiendo temporalmente el uso de tokens revocados.

---

## Alternativas Consideradas

- **Heroku** en lugar de Railway: descartado porque eliminó su plan gratuito y Railway ofrece mejor integración con Docker y GitHub.
- **Docker Compose en VPS** (DigitalOcean, Linode): descartado por mayor complejidad operacional (gestión de servidor, SSL, DNS) innecesaria para el alcance académico.
- **Kubernetes** para orquestación: descartado por complejidad excesiva para un proyecto con 4 servicios y un equipo pequeño.
- **Flyway / Liquibase** para migraciones de BD: considerado como alternativa a `ddl-auto=update`, descartado por tiempo de implementación. Queda pendiente para una iteración futura si el proyecto escala.
- **Jenkins** en lugar de GitHub Actions: descartado porque requiere infraestructura propia y GitHub Actions está integrado nativamente con el repositorio sin costo adicional.
- **Rama `main` como trigger del pipeline**: se evaluó usar `main` como rama de integración continua, pero se optó por `features` como rama target del pipeline para reflejar el flujo de trabajo real del equipo — los cambios se integran en `features` antes de llegar a `main`.
- **Job de deploy en GitHub Actions**: se evaluó usar `railway up` desde el pipeline para tener control total sobre cuándo se deploya. Se descartó porque Railway ya ofrece auto-deploy nativo al detectar push en la rama configurada, evitando gestionar `RAILWAY_TOKEN` como GitHub Secret y reduciendo la complejidad del workflow.
