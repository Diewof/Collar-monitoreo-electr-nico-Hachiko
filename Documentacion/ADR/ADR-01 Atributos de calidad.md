# ADR-001: Definición de Atributos de Calidad del Sistema (ISO/IEC 25010)

Estado: Aceptado  
Fecha: 2026-04-04

## Contexto

El sistema Hachiko requiere una base formal de requisitos no funcionales que guíe las decisiones de diseño arquitectónico. Sin una definición explícita y justificada de atributos de calidad, las decisiones técnicas carecen de criterios objetivos de evaluación. Se adoptó el estándar ISO/IEC 25010 como marco de referencia para clasificar y priorizar estos requisitos.

## Decisión

Se identificaron y documentaron 11 requisitos no funcionales (RNF-001 a RNF-011), mapeados a las características y subcaracterísticas de ISO/IEC 25010, con justificación y nivel de criticidad asignado:

| ID      | Requisito No Funcional                                                                  | Característica ISO 25010     | Subcaracterística        | Justificación                                                                 | Criticidad |
|---------|-----------------------------------------------------------------------------------------|------------------------------|--------------------------|-------------------------------------------------------------------------------|------------|
| RNF-001 | El sistema deberá estar disponible 24/7 con una disponibilidad mínima del 99%.          | Fiabilidad                   | Disponibilidad           | El sistema debe garantizar acceso continuo a los usuarios.                    | Alta       |
| RNF-002 | El sistema deberá realizar copias de seguridad automáticas diarias de la base de datos. | Fiabilidad                   | Recuperabilidad          | Permite restaurar información ante fallos o pérdidas de datos.                | Alta       |
| RNF-003 | El sistema deberá responder a las consultas en un tiempo promedio ≤ 15 segundos.        | Eficiencia del rendimiento   | Comportamiento temporal  | Reduce tiempos de espera y mejora la experiencia del usuario.                 | Alta       |
| RNF-004 | La base de datos deberá soportar grandes volúmenes sin degradar el rendimiento.         | Eficiencia del rendimiento   | Capacidad                | Garantiza estabilidad ante crecimiento de datos.                              | Media      |
| RNF-005 | Las contraseñas deberán almacenarse con algoritmos de cifrado seguro.                   | Seguridad                    | Confidencialidad         | Protege credenciales contra accesos no autorizados.                           | Alta       |
| RNF-006 | Todas las operaciones deberán ser auditadas.                                            | Seguridad                    | Trazabilidad             | Permite controlar y rastrear acciones realizadas en el sistema.               | Alta       |
| RNF-007 | El sistema deberá garantizar conexión segura en Android.                                | Seguridad                    | Integridad               | Previene alteraciones o interceptación de datos en tránsito.                  | Alta       |
| RNF-008 | La aplicación deberá tener una interfaz intuitiva y fácil de usar.                      | Usabilidad                   | Facilidad de uso         | Mejora la interacción usuario-sistema.                                        | Media      |
| RNF-009 | Se deberá proporcionar capacitación y manuales de usuario.                              | Usabilidad                   | Aprendizaje              | Facilita la adopción y correcto uso del sistema.                              | Baja       |
| RNF-010 | El sistema deberá permitir actualización eficiente de la información.                   | Mantenibilidad               | Modificabilidad          | Facilita cambios y mantenimiento del sistema.                                 | Media      |
| RNF-011 | El sistema deberá desarrollarse con Python y Android Studio.                            | Portabilidad / Restricción   | Adaptabilidad            | Define el entorno tecnológico del sistema.                                    | Baja       |

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
