# ADR-001: Definición de Atributos de Calidad del Sistema (ISO/IEC 25010)

Estado: Aceptado  
Fecha: 2026-04-04

## Contexto

El sistema Hachiko requiere una base formal de requisitos no funcionales que guíe las decisiones de diseño arquitectónico. Sin una definición explícita y justificada de atributos de calidad, las decisiones técnicas carecen de criterios objetivos de evaluación. Se adoptó el estándar ISO/IEC 25010 como marco de referencia para clasificar y priorizar estos requisitos.

## Decisión

Se identificaron y documentaron 11 requisitos no funcionales (RNF-001 a RNF-011), mapeados a las características y subcaracterísticas de ISO/IEC 25010, con nivel de criticidad asignado:

| ID      | Requisito No Funcional                                                                  | Característica ISO 25010   | Subcaracterística      | Criticidad |
|---------|-----------------------------------------------------------------------------------------|----------------------------|------------------------|------------|
| RNF-001 | Disponibilidad 24/7 con mínimo del 99%.                                                 | Fiabilidad                 | Disponibilidad         | Alta       |
| RNF-002 | Copias de seguridad automáticas diarias de la base de datos.                            | Fiabilidad                 | Recuperabilidad        | Alta       |
| RNF-003 | Tiempo de respuesta promedio ≤ 15 segundos.                                             | Eficiencia del rendimiento | Comportamiento temporal| Alta       |
| RNF-004 | La BD debe soportar grandes volúmenes sin degradar el rendimiento.                      | Eficiencia del rendimiento | Capacidad              | Media      |
| RNF-005 | Contraseñas almacenadas con algoritmos de cifrado seguro.                               | Seguridad                  | Confidencialidad       | Alta       |
| RNF-006 | Todas las operaciones deben ser auditadas.                                              | Seguridad                  | Trazabilidad           | Alta       |
| RNF-007 | Conexión segura garantizada en Android.                                                 | Seguridad                  | Integridad             | Alta       |
| RNF-008 | Interfaz intuitiva y fácil de usar.                                                     | Usabilidad                 | Facilidad de uso       | Media      |
| RNF-009 | Capacitación y manuales de usuario disponibles.                                         | Usabilidad                 | Aprendizaje            | Baja       |
| RNF-010 | Actualización eficiente de la información del sistema.                                  | Mantenibilidad             | Modificabilidad        | Media      |
| RNF-011 | Desarrollo con Python y Android Studio.                                                 | Portabilidad / Restricción | Adaptabilidad          | Baja       |

## Consecuencias

### Positivas
- Proporciona criterios objetivos y trazables para evaluar decisiones arquitectónicas futuras.
- Permite priorizar esfuerzos de desarrollo según el nivel de criticidad de cada atributo.
- Facilita la comunicación entre stakeholders al usar un lenguaje estandarizado (ISO/IEC 25010).
- Los atributos de alta criticidad (RNF-001, 002, 003, 005, 006, 007) actúan como restricciones duras en el diseño.

### Negativas
- Algunos requisitos como RNF-011 restringen la libertad tecnológica del equipo al imponer un stack fijo.
- Alcanzar 99% de disponibilidad (RNF-001) implica costos adicionales en infraestructura y estrategias de redundancia.
- El cumplimiento simultáneo de todos los atributos de alta criticidad puede generar tensiones arquitectónicas (p. ej., seguridad vs. rendimiento).

## Alternativas Consideradas

- **No adoptar un estándar formal:** Definir atributos de calidad de forma ad hoc sin referencia a ISO/IEC 25010. Descartado por falta de trazabilidad y criterios de evaluación objetivos.
- **Usar ISO/IEC 9126 (versión anterior):** Estándar predecesor de ISO/IEC 25010. Descartado por estar obsoleto y no cubrir características modernas como seguridad y compatibilidad de forma explícita.
- **Aplicar solo los atributos de alta criticidad:** Reducir el alcance a los 6 RNF críticos. Descartado porque los atributos de criticidad media y baja igualmente afectan la experiencia del usuario y la mantenibilidad a largo plazo.
