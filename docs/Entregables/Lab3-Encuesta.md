# Selección del Patrón Arquitectónico — Semana 4

## Laboratorio 3 — Selección del Patrón Arquitectónico

### Instrucciones
Este cuestionario tiene como objetivo ayudar al equipo a tomar una decisión arquitectónica fundamentada.  
Respondan cada pregunta basándose en su proyecto real. Al final, el sistema de puntuación indicará qué patrón se adapta mejor al contexto.  
La decisión final debe quedar documentada en el ADR-003.

---

## SECCIÓN 1 — EXPERIENCIA DEL EQUIPO

### Pregunta 1
¿El equipo tiene experiencia previa trabajando con Microservicios?

- A: Sí, hemos trabajado con microservicios en proyectos anteriores.  
- B: Tenemos conocimiento teórico pero nunca lo hemos implementado.  
- C: No tenemos experiencia con microservicios.  

**Opción seleccionada:** C  

**Justificación:**  
El equipo no ha implementado microservicios en proyectos reales previos. El proyecto Hachiko es el primer sistema web completo del grupo, desarrollado en PHP/MySQL.

---

### Pregunta 2
¿El equipo domina herramientas de orquestación de contenedores (Docker Compose, Kubernetes)?

- A: Sí, usamos Docker Compose con fluidez y conocemos Kubernetes.  
- B: Sabemos usar Docker básico pero no orquestación avanzada.  
- C: Apenas estamos aprendiendo Docker.  

**Opción seleccionada:** C  

**Justificación:**  
El repositorio no contiene Dockerfile ni docker-compose.yml. El entorno de desarrollo es XAMPP/WAMP con PHP y MySQL directamente.

---

### Pregunta 3
¿Cuánto tiempo tiene el equipo para completar el proyecto?

- A: Tiempo suficiente para gestionar complejidad operacional alta.  
- B: Tiempo moderado, podemos asumir algo de complejidad.  
- C: Tiempo limitado, necesitamos la solución más simple posible.  

**Opción seleccionada:** C  

**Justificación:**  
El proyecto está enmarcado en un calendario académico semestral con entregas por laboratorio. La complejidad operacional de microservicios no es viable en este tiempo.

**Impacto en la decisión:**  
Mayoría de respuestas C → Arquitectura en 3 Capas es más adecuada.

---

## SECCIÓN 2 — DOMINIOS DEL NEGOCIO

### Pregunta 4
¿El sistema tiene dominios de negocio claramente separados e independientes?

- A: Sí, tenemos 4 o más dominios completamente independientes entre sí.  
- B: Tenemos 2-3 dominios con algo de dependencia entre ellos.  
- C: El sistema tiene un dominio principal con funcionalidades relacionadas.  

**Opción seleccionada:** C  

**Dominios identificados:**

1. Autenticación y Seguridad  
2. Gestión de Propietarios y Usuarios  
3. Gestión de Mascotas  
4. Panel de Administración  

**Nota:**  
Aunque se identifican 4 dominios, todos comparten la misma base de datos MySQL con relaciones fuertemente acopladas:


users → propietario → residencia → ciudad → departamento → pais
propietario → perro → raza


Separar en microservicios requeriría una refactorización mayor no viable en el contexto actual.

---

### Pregunta 5
¿Cada dominio podría tener su propia base de datos independiente?

- A: Sí, los datos son completamente independientes.  
- B: Podrían separarse con esfuerzo.  
- C: No, están altamente relacionados.  

**Opción seleccionada:** C  

**Justificación:**  
La base de datos presenta dependencias en cadena y consultas con múltiples JOIN. Separarla implicaría consistencia eventual, lo cual supera la madurez actual del equipo.

---

### Pregunta 6
¿Los dominios tienen ciclos de vida de despliegue diferentes?

- A: Sí, cada módulo tiene su propio despliegue.  
- B: Algunos cambian más que otros.  
- C: El sistema se despliega completo.  

**Opción seleccionada:** C  

**Justificación:**  
El sistema se despliega como una unidad completa.

**Impacto en la decisión:**  
Respuestas C → 3 Capas es más adecuado.

---

## SECCIÓN 3 — ESCALABILIDAD Y RENDIMIENTO

### Pregunta 7
¿Cuántos usuarios concurrentes debe soportar el sistema?

- A: Más de 10,000  
- B: Entre 1,000 y 10,000  
- C: Menos de 1,000  

**Opción seleccionada:** C  

**Justificación:**  
El sistema es un prototipo académico con baja carga esperada.

---

### Pregunta 8
¿Hay módulos que necesiten escalar independientemente?

- A: Sí  
- B: No crítico  
- C: No  

**Opción seleccionada:** C  

**Nota:**  
El módulo con mayor carga sería main.php, pero no justifica escalado independiente.

---

### Pregunta 9
¿El sistema tiene requisitos de disponibilidad diferenciados?

- A: Sí  
- B: Alta disponibilidad uniforme  
- C: No es crítico  

**Opción seleccionada:** C  

---

### Pregunta 10
¿El rendimiento es Must Have?

- A: Sí  
- B: Should Have  
- C: No prioritario  

**Opción seleccionada:** C  

**Justificación:**  
Los atributos principales son Seguridad, Funcionalidad y Mantenibilidad.

**Impacto en la decisión:**  
Respuestas C → 3 Capas es suficiente.

---

## SECCIÓN 4 — MANTENIBILIDAD Y EQUIPO

### Pregunta 11
¿El equipo puede dividirse en servicios independientes?

- A: Sí  
- B: Con dificultad  
- C: No  

**Opción seleccionada:** C  

---

### Pregunta 12
¿El equipo está dispuesto a gestionar la complejidad de microservicios?

- A: Sí  
- B: Parcialmente  
- C: No  

**Opción seleccionada:** C  

---

### Pregunta 13
¿La mantenibilidad es Must Have?

- A: Sí  
- B: Importante  
- C: No  

**Opción seleccionada:** B  

**Impacto en la decisión:**  
Equipo pequeño → 3 Capas es más mantenible.

---

## SISTEMA DE PUNTUACIÓN

| Respuesta | Cantidad | Puntaje |
|----------|--------|--------|
| A | 0 | 0 × 2 = 0 |
| B | 1 | 1 × 1 = 1 |
| C | 12 | 12 × 0 = 0 |

---

## Fórmula de Decisión


Puntaje Total = (A × 2) + (B × 1) + (C × 0)
Puntaje Total = (0 × 2) + (1 × 1) + (12 × 0) = 1


---

## Interpretación

| Rango | Decisión | Justificación |
|------|--------|--------------|
| 18 – 26 | Microservicios | Alta complejidad y escala |
| 10 – 17 | Evaluar | Contexto mixto |
| 0 – 9 | Arquitectura 3 Capas | Simplicidad y dominio único |

---

## DECISIÓN FINAL

- Puntaje Total: 1  
- Patrón Arquitectónico seleccionado: Arquitectura en 3 Capas
