# ADR-06: Táctica de Caché — Cache-Aside con Redis

Estado: Aceptado

Fecha: 2026-05-12

---

## Contexto

El portal Hachiko presentaba contención severa en la base de datos bajo alta concurrencia. Bajo 400 VUs en k6, los endpoints de datos de referencia (`/api/referencia/*`) ejecutaban full table scans en cada request — datos que raramente cambian. El endpoint `/api/admin/stats` ejecutaba 5+ queries secuenciales por llamada. `/api/admin/usuarios` cargaba la tabla completa sin ningún límite.

Los tests de carga medían p(95) en el rango de 3–6 segundos para estos endpoints, con tasas de fallo superiores al 5% a partir de 200 VUs. La ausencia de cualquier capa de caché era el cuello de botella principal del sistema.

---

## Decisión

Se implementó el patrón **cache-aside** usando **Redis** como store de caché externo, con Spring Cache como capa de abstracción (`@Cacheable` / `@CacheEvict`). Redis corre como servicio independiente, tanto en Docker Compose local como como plugin gestionado en Railway.

---

## Componentes Implementados

### 1. Infraestructura de Caché — `CacheConfig.java`

**Qué hace:**
- Define un `RedisCacheManager` con TTL diferenciado por cache.
- Configura serialización JSON via Jackson 2.x (disponible en classpath por `jjwt-jackson`), con serializers tipados por cache para evitar ambigüedad en deserialización.
- Registra `SimpleCacheErrorHandler` para degradación silenciosa cuando Redis no está disponible.

**¿Cumple su función?** Sí, completamente.

**TTLs configurados:**

| Cache | TTL | Justificación |
|---|---|---|
| `referencia-paises` | 24 horas | Datos estáticos, cambian con migraciones de BD |
| `referencia-departamentos` | 24 horas | Ídem |
| `referencia-ciudades` | 24 horas | Ídem |
| `referencia-razas` | 24 horas | Ídem |
| `referencia-planes` | 24 horas | Planes de suscripción cambian raramente |
| `admin-stats` | 60 segundos | Dashboard tolera datos con 1 minuto de desfase |
| `admin-usuarios` | 30 segundos | Lista de usuarios tolera desfase mínimo |
| `mascotas` | 5 minutos | Datos por propietario, baja frecuencia de cambio |

**Decisión de serialización:**
Se descartó `activateDefaultTyping` (Jackson default typing con `AS_PROPERTY` y `WRAPPER_ARRAY`) porque ambas modalidades fallan cuando el valor cacheado es una colección inmutable (`ImmutableCollections$ListN`) retornada por `.toList()`, y el deserializer genérico `Object.class` no tiene suficiente información de tipo para reconstruir `List<PaisDTO>`.

La solución final usa un `RedisSerializer<Object>` anónimo por cache, donde cada instancia conoce su `JavaType` concreto:
- Caches de lista: `mapper.getTypeFactory().constructCollectionType(ArrayList.class, ElementDTO.class)`
- Caches de objeto: `mapper.getTypeFactory().constructType(DtoClass.class)`

Esto elimina la necesidad de embeber metadatos de tipo en el JSON de Redis y evita vulnerabilidades de deserialización polimórfica.

---

### 2. Caches de Datos de Referencia — `ReferenciasServiceImpl`

**Qué hace:**
- Agrega `@Cacheable` a los 5 métodos de referencia (`getPaises`, `getDepartamentosByPais`, `getCiudadesByDepartamento`, `getRazas`, `getPlanes`).
- Primera llamada: ejecuta la query y almacena el resultado en Redis.
- Llamadas subsiguientes (dentro del TTL): devuelve directamente desde Redis sin tocar la BD.

**Claves de caché:**

| Método | Cache | Clave SpEL |
|---|---|---|
| `getPaises()` | `referencia-paises` | `'all'` |
| `getDepartamentosByPais(paisId)` | `referencia-departamentos` | `#paisId` |
| `getCiudadesByDepartamento(departamentoId)` | `referencia-ciudades` | `#departamentoId` |
| `getRazas()` | `referencia-razas` | `'all'` |
| `getPlanes()` | `referencia-planes` | `'all'` |

---

### 3. Cache del Dashboard Admin — `AdminDashboardServiceImpl`

**Qué hace:**
- `@Cacheable(cacheNames = CACHE_ADMIN_STATS, key = "'global'")` sobre `getDashboardStats()`.
- El método ejecuta múltiples queries secuenciales (conteos, actividad reciente, ordenamiento en memoria). Con caché, esas queries solo se ejecutan una vez cada 60 segundos independientemente de cuántos admins consulten el dashboard simultáneamente.

---

### 4. Cache de Usuarios Admin — `AdminUserServiceImpl`

**Qué hace:**
- `@Cacheable` sobre `getAllUsers()` — la lista completa se cachea con clave `'all'`.
- `@CacheEvict` sobre `updateUserRole()` y `deleteUser()` — invalida el caché al mutar datos.

**Consistencia eventual:** La lista puede estar desactualizada hasta 30 segundos si otro admin modifica roles. Aceptable para el panel de administración.

---

### 5. Cache de Mascotas — `MascotaServiceImpl`

**Qué hace:**
- `@Cacheable(key = "#propietarioId")` sobre `listByPropietario()` — cada propietario tiene su entrada independiente en el caché.
- `@CacheEvict(key = "#request.propietarioId")` sobre `create()` y `update()` — invalida solo la entrada del propietario afectado.
- `@CacheEvict(key = "#propietarioId")` sobre `delete()` — ídem.

El scope por propietario evita que la invalidación de un usuario afecte la entrada cacheada de otro.

---

### 6. Evict al Registrar Usuario — `RegisterServiceImpl`

**Qué hace:**
- `@CacheEvict(cacheNames = CACHE_ADMIN_USUARIOS, key = "'all'")` sobre `register()`.
- Cuando un usuario nuevo se registra públicamente, la lista de admin-usuarios queda desactualizada. El evict garantiza que la próxima consulta del admin vea al usuario recién creado.

---

### 7. Degradación ante Fallo de Redis

**Qué hace:**
- `SimpleCacheErrorHandler` captura cualquier excepción de Redis (conexión rechazada, timeout, `LOADING`) y la descarta silenciosamente.
- En `@Cacheable GET`: cache miss → el método real se ejecuta (fallback a BD).
- En `@CacheEvict`: el evict no ocurre → la escritura en BD sí ocurre.

El sistema nunca devuelve HTTP 500 por un fallo de Redis. La degradación es transparente para el cliente a costa de latencia adicional mientras Redis no está disponible.

---

## Consecuencias

### Positivas
- Los endpoints de referencia pasan de full table scan por request a una única query cada 24 horas por dato.
- El dashboard de admin pasa de 5+ queries secuenciales por request a 0 queries durante 60 segundos tras el primer hit.
- La contención en la BD bajo alta concurrencia se reduce drásticamente — Redis absorbe la carga de lectura.
- La caída de Redis no genera errores en producción — los usuarios experimentan mayor latencia pero el sistema sigue funcionando.
- Las claves son legibles en `redis-cli` (e.g., `referencia-razas::all`, `mascotas::42`) — facilita debugging.

### Negativas
- **Consistencia eventual:** datos cacheados pueden estar desactualizados hasta el TTL del cache. Aceptable para el dominio actual (datos de referencia, stats de admin), pero requiere evaluación caso por caso si se agregan nuevos caches.
- **Estado adicional en producción:** Redis es una dependencia de infraestructura más. Si Railway baja el plugin de Redis, el sistema degrada a latencia sin caché pero no falla.
- **Invalidación parcial:** `@CacheEvict` invalida entradas individuales pero no hay invalidación transversal. Si un cambio en la BD afecta múltiples caches simultáneamente (e.g., un plan que aparece en `referencia-planes` y en stats de admin), la consistencia entre caches no está garantizada hasta que cada TTL expire.
- **Primera request fría:** tras un reinicio del backend o `FLUSHALL` en Redis, la primera request a cada endpoint vuelve a la BD. Bajo alta concurrencia simultánea esto puede causar un "stampede" de cache miss. No implementado dog-pile prevention en esta iteración.

---

## Alternativas Consideradas

- **Caffeine (caché en memoria):** descartado porque no persiste entre reinicios del backend y no es compartido entre múltiples instancias. Si Railway escala el backend horizontalmente, cada instancia tendría su propio caché inconsistente.
- **`@Cacheable` con `ConcurrentHashMap` (SimpleCacheManager):** descartado por las mismas razones que Caffeine, y adicionalmente no soporta TTL nativo.
- **Caché a nivel de base de datos (pgBouncer, materialized views):** considerado para `admin-stats`, descartado porque requiere infraestructura adicional y acceso DBA para mantener las vistas materializadas. Redis es más simple de operar para este caso.
- **`activateDefaultTyping` con `AS_PROPERTY`:** probado y descartado. Falla al deserializar listas (`MismatchedInputException: START_OBJECT`) porque `AS_PROPERTY` no puede incrustar el tipo en un JSON array.
- **`activateDefaultTyping` con `WRAPPER_ARRAY`:** probado y descartado. Falla con `ImmutableCollections$ListN` retornada por `.toList()` — Jackson puede serializar colecciones inmutables pero no puede instanciarlas en deserialización.
- **`GenericJackson2JsonRedisSerializer`:** descartado porque está deprecated en Spring Data Redis 4.0 y tiene los mismos problemas de default typing.
- **Serialización con `TypeReference` explícita en cada servicio:** considerado como alternativa a `CacheConfig` tipado. Descartado porque exige `RedisTemplate` manual en cada servicio, pierde la abstracción de `@Cacheable` y aumenta el acoplamiento entre la lógica de negocio y la infraestructura de caché.
