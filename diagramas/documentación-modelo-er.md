# Documentación de la Base de Datos

## Tabla: PROPIETARIO  
**Descripción:** Almacena información de los dueños de perros registrados en el sistema.  

| Atributo          | propietario_id   | primer_nombre   | apellido       | contraseña     | telefono         | email             | residencia_id   | plan_id   | segundo_nombre   | segundo_apellido   |
|-------------------|------------------|-----------------|----------------|----------------|------------------|-------------------|-----------------|-----------|------------------|--------------------|
| **Tipo Clave**    | PK               | \-              | \-             | \-             | \-               | UNIQUE            | FK              | FK        | \-               | \-                 |
| **Nulos/Únicos**  | NN               | NN              | NN             | NN             | NN               | NN, UNIQUE        | NN              | NULL      | NULL             | NULL               |
| **Tabla Foránea** | \-               | \-              | \-             | \-             | \-               | \-                | Residencia      | Plan      | \-               | \-                 |
| **Columna Fk**    | \-               | \-              | \-             | \-             | \-               | \-                | residencia_id   | plan_id   | \-               | \-                 |
| **TipoDatos**     | MEDIUMINT UNSIGNED | VARCHAR(50)    | VARCHAR(50)    | CHAR(60)       | VARCHAR(15)      | VARCHAR(100)      | INT UNSIGNED    | TINYINT UNSIGNED | VARCHAR(50)        | VARCHAR(50)         |
| **Longitud**      | 8                | 50              | 50             | 60             | 15               | 100               | 10              | 3         | 50               | 50                 |
| **Ejemplo**       | 1000             | "Ana"           | "López"        | "hash_seguro"  | "+573001234567"  | "ana.lopez@mail.com" | 25              | 2         | "María"          | "García"           |

---

## Tabla: PLAN  
**Descripción:** Define los planes de suscripción disponibles para los propietarios.  

| Atributo          | plan_id   | nombre_plan   | descripcion                | costo    |
|-------------------|-----------|---------------|----------------------------|----------|
| **Tipo Clave**    | PK        | UNIQUE        | \-                         | \-       |
| **Nulos/Únicos**  | NN        | NN, UNIQUE    | NULL                       | NN       |
| **Tabla Foránea** | \-        | \-            | \-                         | \-       |
| **Columna Fk**    | \-        | \-            | \-                         | \-       |
| **TipoDatos**     | TINYINT UNSIGNED | VARCHAR(30) | VARCHAR(255)              | DECIMAL(6,2) UNSIGNED |
| **Longitud**      | 3         | 30            | 255                        | 6,2      |
| **Ejemplo**       | 3         | "Premium"     | "Acceso a análisis avanzados..." | 29.99    |

---

## Tabla: PERRO  
**Descripción:** Almacena información de los perros registrados en el sistema, asociados a sus dueños.  

| Atributo          | perro_id   | nombre   | fecha_nacimiento   | peso    | genero   | esterilizado   | propietario_id   | raza_id   |
|-------------------|------------|----------|--------------------|---------|----------|----------------|------------------|-----------|
| **Tipo Clave**    | PK         | \-       | \-                 | \-      | \-       | \-             | FK               | FK        |
| **Nulos/Únicos**  | NN         | NN       | NULL               | NULL    | NN       | DEFAULT (FALSE)| NN               | NN        |
| **Tabla Foránea** | \-         | \-       | \-                 | \-      | \-       | \-             | Propietario      | Raza      |
| **Columna Fk**    | \-         | \-       | \-                 | \-      | \-       | \-             | propietario_id   | raza_id   |
| **TipoDatos**     | INT UNSIGNED | VARCHAR(50) | DATETIME(6)      | DECIMAL(4,2) | ENUM('M','F') | BOOLEAN         | MEDIUMINT UNSIGNED | SMALLINT UNSIGNED |
| **Longitud**      | 10         | 50       | \-                 | 4,2     | \-       | \-             | 8                | 5         |
| **Ejemplo**       | 500        | "Max"    | 20/03/2000         | 25.50   | 'M'      | TRUE           | 1000             | 15        |

---

## Tabla: RAZA  
**Descripción:** Almacena las razas de perros y su predisposición a problemas de conducta.  

| Atributo          | raza_id   | nombre_raza   | predisposicion_problemas_conducta   |
|-------------------|-----------|---------------|--------------------------------------|
| **Tipo Clave**    | PK        | UNIQUE        | \-                                   |
| **Nulos/Únicos**  | NN        | NN, UNIQUE    | NULL                                 |
| **Tabla Foránea** | \-        | \-            | \-                                   |
| **Columna Fk**    | \-        | \-            | \-                                   |
| **TipoDatos**     | SMALLINT UNSIGNED | VARCHAR(50) | VARCHAR(255)                        |
| **Longitud**      | 5         | 50            | 255                                  |
| **Ejemplo**       | 15        | "Labrador"    | "Ansiedad por separación"            |

---

## Tabla: NOTIFICACION  
**Descripción:** Registra las notificaciones enviadas a los propietarios, con su estado y tipo.  

| Atributo          | notificacion_id   | propietario_id   | tipo_notificacion_id   | mensaje                | estado               | fecha_generacion          |
|-------------------|-------------------|-------------------|-------------------------|------------------------|----------------------|---------------------------|
| **Tipo Clave**    | PK                | FK                | FK                      | \-                     | \-                   | \-                        |
| **Nulos/Únicos**  | NN                | NN                | NN                      | NN                     | NN                   | NN                        |
| **Tabla Foránea** | \-                | Propietario       | Tipo_Notificacion       | \-                     | \-                   | \-                        |
| **Columna Fk**    | \-                | propietario_id    | tipo_notificacion_id    | \-                     | \-                   | \-                        |
| **TipoDatos**     | BIGINT UNSIGNED   | MEDIUMINT UNSIGNED | TINYINT UNSIGNED       | VARCHAR(500)           | ENUM('pendiente', 'enviada', 'leida') | DATETIME(6) |
| **Longitud**      | 20                | 8                 | 3                       | 500                    | \-                   | \-                        |
| **Ejemplo**       | 1001              | 1000              | 2                       | "Su perro Max mostró ansiedad..." | "pendiente" | "2023-10-05 15:05:30.123456" |

---

## Tabla: COLLAR  
**Descripción:** Registra los collares inteligentes asociados a los perros, con datos técnicos y de uso.  

| Atributo          | collar_id   | perro_id   | version_firmware   | fecha_fabricacion   | fecha_instalacion   | bateria   |
|-------------------|-------------|------------|--------------------|---------------------|---------------------|-----------|
| **Tipo Clave**    | PK          | FK         | \-                 | \-                  | \-                  | \-        |
| **Nulos/Únicos**  | NN          | NN         | NN                 | NN                  | NN                  | NULL      |
| **Tabla Foránea** | \-          | Perro      | \-                 | \-                  | \-                  | \-        |
| **Columna Fk**    | \-          | perro_id   | \-                 | \-                  | \-                  | \-        |
| **TipoDatos**     | MEDIUMINT UNSIGNED | INT UNSIGNED | VARCHAR(20)       | DATE                | DATE                | TINYINT UNSIGNED |
| **Longitud**      | 8           | 10         | 20                 | \-                  | \-                  | 3         |
| **Ejemplo**       | 200         | 500        | "v2.1.5"           | 2023-05-15          | 2023-06-01          | 85        |

---

## Tabla: REGISTRO_SENSORES  
**Descripción:** Almacena datos físicos recopilados por los sensores del collar.  

| Atributo          | registro_id   | collar_id   | decibelios   | frecuencia   | aceleracion_x   | temperatura   | pulsaciones_min   | marca_tiempo          |
|-------------------|---------------|-------------|--------------|--------------|-----------------|---------------|-------------------|-----------------------|
| **Tipo Clave**    | PK            | FK          | \-           | \-           | \-              | \-            | \-                | \-                    |
| **Nulos/Únicos**  | NN            | NN          | NULL         | NULL         | NULL            | NULL          | NULL              | NN                    |
| **Tabla Foránea** | \-            | Collar      | \-           | \-           | \-              | \-            | \-                | \-                    |
| **Columna Fk**    | \-            | collar_id   | \-           | \-           | \-              | \-            | \-                | \-                    |
| **TipoDatos**     | BIGINT UNSIGNED | MEDIUMINT UNSIGNED | DECIMAL(4,2) UNSIGNED | DECIMAL(6,2) UNSIGNED | DECIMAL(8,2) | DECIMAL(4,1) | SMALLINT UNSIGNED | DATETIME(6) |
| **Longitud**      | 20            | 8           | 4,2          | 6,2          | 8,2             | 4,1           | 5                 | \-                    |
| **Ejemplo**       | 1001          | 200         | 65.50        | 150.00       | 2.15            | 38.5          | 120               | "2023-10-05 14:30:00.123456" |

---

## Tabla: REGISTRO_COMPORTAMIENTO  
**Descripción:** Registra patrones de comportamiento detectados en los perros.  

| Atributo          | registro_id   | collar_id   | emocion_id   | tipo_patron_id   | certeza   | hora_inicio      | duracion   |
|-------------------|---------------|-------------|--------------|-------------------|-----------|------------------|------------|
| **Tipo Clave**    | PK            | FK          | FK           | FK                | \-        | \-               | \-         |
| **Nulos/Únicos**  | NN            | NN          | NN           | NN                | NULL      | NULL             | NULL       |
| **Tabla Foránea** | \-            | Collar      | Emocion      | Tipo_Patron       | \-        | \-               | \-         |
| **Columna Fk**    | \-            | collar_id   | emocion_id   | tipo_patron_id    | \-        | \-               | \-         |
| **TipoDatos**     | BIGINT UNSIGNED | MEDIUMINT UNSIGNED | TINYINT UNSIGNED | TINYINT UNSIGNED | DECIMAL(3,0) UNSIGNED | TIME(3) | SMALLINT UNSIGNED |
| **Longitud**      | 20            | 8           | 3            | 3                 | 3         | \-               | 5          |
| **Ejemplo**       | 5001          | 200         | 2            | 4                 | 90        | "14:30:00.123"   | 300        |
