# ADR-002: Priorización de Requisitos No Funcionales mediante Matriz MoSCoW

Estado: Aceptado  
Fecha: 2025-03-14

## Contexto

El proyecto **Hachiko** es un sistema web de monitoreo emocional para mascotas que permite a dueños de perros registrar sus mascotas, visualizar su estado emocional y gestionar su perfil. Durante el proceso de definición arquitectónica, se identificaron 11 requisitos no funcionales (RNFs) mapeados bajo la norma ISO/IEC 25010, más 3 RNFs adicionales emergentes del análisis de riesgos y encuestas del proyecto (RNF-015\*, RNF-E01\*, RNF-E02\*), para un total de **18 RNFs**.

Ante la imposibilidad de abordar todos los atributos de calidad con igual nivel de prioridad en el contexto académico y de prototipo del proyecto, se requiere una decisión formal que establezca cuáles requisitos no funcionales son obligatorios para el MVP, cuáles son deseables, cuáles opcionales y cuáles quedan fuera del alcance actual. Esta decisión guiará las decisiones arquitectónicas posteriores, en particular la selección del patrón arquitectónico.

## Decisión

Se aplica la técnica de priorización **MoSCoW** sobre los 18 RNFs identificados, clasificándolos en cuatro categorías: **Must Have**, **Should Have**, **Could Have** y **Won't Have**. La clasificación se realizó considerando el nivel de criticidad de cada RNF, su impacto directo en la funcionalidad central del sistema (monitoreo IoT del collar canino), las restricciones de tiempo académico y la madurez técnica del equipo.

La matriz resultante es la siguiente:

| ID | Atributo de Calidad | Característica ISO 25010 | Sub-característica | Criticidad | Clasificación MoSCoW | Justificación / Trade-off Arquitectónico |
|---|---|---|---|---|---|---|
| **MUST HAVE** | | | | | | |
| RNF-001 | Disponibilidad 24/7 (≥99%) | Fiabilidad | Disponibilidad | Alta | MUST HAVE | El monitoreo continuo del estado emocional del perro exige que el sistema esté siempre en línea. Un fallo implica pérdida de alertas críticas para el dueño. |
| RNF-005 | Cifrado de contraseñas de usuario | Seguridad | Confidencialidad | Alta | MUST HAVE | Sin cifrado robusto el sistema es inviable desde el punto de vista de privacidad y regulación. |
| RNF-007 | Conexión segura aplicación-collar (HTTPS/TLS) | Seguridad | Confidencialidad | Alta | MUST HAVE | La transmisión de datos de sensores IoT es vulnerable a interceptación, por lo que se requiere proteger la información durante su tránsito para evitar accesos no autorizados. |
| RNF-003 | Tiempo de respuesta ≤ 15 segundos | Eficiencia del rendimiento | Comportamiento temporal | Alta | MUST HAVE | Las alertas generadas por eventos críticos detectados por los sensores (ej. inactividad prolongada, patrones de movimiento irregulares o fuera de rango) deberán procesarse y notificarse al usuario en un tiempo máximo de 15 segundos desde su detección, con una latencia de transmisión no mayor a 5 segundos. |
| **SHOULD HAVE** | | | | | | |
| RNF-002 | Backups automáticos diarios | Fiabilidad | Recuperabilidad | Alta | SHOULD HAVE | El sistema deberá realizar copias de seguridad automáticas de la información al menos una vez cada 24 horas, almacenándolas en una ubicación segura y permitiendo la recuperación de los datos en un tiempo máximo definido ante fallos. |
| RNF-006 | Auditoría de operaciones CRUD | Seguridad | Trazabilidad | Alta | SHOULD HAVE | Permite rastrear acciones sobre perfiles y datos de mascotas. Importante para detección de fraudes, aunque no bloquea el funcionamiento central del sistema. |
| RNF-008 | Interfaz intuitiva y accesible | Usabilidad | Facilidad de uso | Media | SHOULD HAVE | Impacta directamente en la adopción del producto. Una mala UX reducirá el uso real, pero el sistema puede funcionar aunque la interfaz sea mejorable. |
| RNF-004 | Soporte a grandes volúmenes de datos | Eficiencia del rendimiento | Capacidad | Media | SHOULD HAVE | Con múltiples usuarios y lecturas continuas de sensores el volumen crece rápidamente. Fundamental para escalar, aunque en MVP el conjunto de datos es manejable. |
| RNF-015\* | Estabilidad de la comunicación IoT (MQTT/BLE) | Fiabilidad | Tolerancia a fallos | Alta | SHOULD HAVE | Identificado en análisis de riesgos del proyecto: cortes de conexión entre collar y app son el riesgo técnico más probable en el entorno real de uso. |
| **COULD HAVE** | | | | | | |
| RNF-010 | Actualización eficiente de datos de trabajadores | Mantenibilidad | Modificabilidad | Media | COULD HAVE | Facilita el mantenimiento futuro del sistema, pero en las versiones iniciales el catálogo de usuarios es pequeño y los cambios son infrecuentes. |
| RNF-009 | Capacitación y manuales de usuario | Usabilidad | Aprendizaje | Baja | COULD HAVE | Mejora la adopción a largo plazo, pero dada la interfaz intuitiva planeada se puede diferir a versiones posteriores del producto. |
| RNF-E01\* | Eficiencia energética del collar (batería larga) | Eficiencia del rendimiento | Utilización de recursos | Media | COULD HAVE | Encuesta del proyecto identifica batería de larga duración como característica deseable. Relevante para la experiencia, pero no bloquea el MVP funcional. |
| **WON'T HAVE** | | | | | | |
| RNF-011 | Restricción de stack: Python + Android Studio | Portabilidad | Adaptabilidad | Baja | WON'T HAVE | Es una restricción de diseño tecnológico, no un atributo arquitectónico optimizable. No moldea decisiones de arquitectura de calidad en esta iteración. |
| RNF-E02\* | Compatibilidad multiplataforma iOS / web | Portabilidad | Coexistencia | Baja | WON'T HAVE | El alcance del proyecto está acotado a Android. Ampliar a iOS o web implica esfuerzo arquitectónico significativo que está fuera del sprint actual. |

## Consecuencias

### Positivas

- La arquitectura se diseña priorizando los cuatro atributos **Must Have** (disponibilidad, seguridad de credenciales, seguridad en tránsito IoT y tiempo de respuesta), lo que orienta las decisiones técnicas hacia los riesgos más críticos del sistema.
- Los cinco atributos **Should Have** están acotados y documentados, permitiendo que sean incorporados en iteraciones posteriores sin generar deuda técnica no controlada.
- Contar con una clasificación formal evita el sobrediseño: los atributos **Could Have** y **Won't Have** quedan explícitamente fuera del MVP, liberando capacidad del equipo para la lógica de negocio central.
- La matriz sirve como insumo directo para la selección del patrón arquitectónico (ADR-003) y para la construcción de los diagramas C4 del sistema.

### Negativas

- Al clasificar RNF-009 (capacitación) y RNF-010 (actualización eficiente) como **Could Have**, existe el riesgo de que el sistema sea difícil de adoptar y mantener en versiones futuras si no se retoman oportunamente.
- La exclusión de RNF-E02\* (compatibilidad multiplataforma) limita el alcance comercial del producto en su estado actual.
- RNF-011 clasificado como **Won't Have** implica que la restricción tecnológica (Python + Android Studio) no se gestiona como atributo de calidad, lo que podría generar inconsistencias si el stack tecnológico evoluciona sin una decisión formal posterior.

## Alternativas Consideradas

- **Priorización por criticidad única (Alta/Media/Baja):** Se consideró usar solamente el nivel de criticidad ISO/IEC 25010 como criterio de priorización. Fue descartada porque no permite distinguir entre atributos igualmente críticos que difieren en su viabilidad de implementación dentro del tiempo académico disponible.
- **Priorización por votación del equipo sin marco formal:** Se descartó por carecer de trazabilidad y no generar un artefacto que soporte las decisiones arquitectónicas documentadas en los ADRs subsiguientes.
