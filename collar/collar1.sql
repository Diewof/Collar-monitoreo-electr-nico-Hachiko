-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: collar
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ciudad`
--

DROP TABLE IF EXISTS `ciudad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ciudad` (
  `ciudad_id` mediumint NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `departamento_id` smallint DEFAULT NULL,
  PRIMARY KEY (`ciudad_id`),
  KEY `departamento_id` (`departamento_id`),
  CONSTRAINT `ciudad_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`departamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciudad`
--

LOCK TABLES `ciudad` WRITE;
/*!40000 ALTER TABLE `ciudad` DISABLE KEYS */;
INSERT INTO `ciudad` VALUES (1,'Barranquilla',1),(2,'Medellín',2),(3,'Bogotá',3),(4,'Cali',5),(5,'Bucaramanga',6),(6,'Cartagena',1),(7,'Manizales',2),(8,'Pereira',2);
/*!40000 ALTER TABLE `ciudad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collar`
--

DROP TABLE IF EXISTS `collar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collar` (
  `collar_id` mediumint NOT NULL,
  `perro_id` int DEFAULT NULL,
  `version_firmware` varchar(20) DEFAULT NULL,
  `fecha_fabricacion` date DEFAULT NULL,
  `fecha_instalacion` date DEFAULT NULL,
  `bateria` tinyint DEFAULT NULL,
  PRIMARY KEY (`collar_id`),
  KEY `perro_id` (`perro_id`),
  CONSTRAINT `collar_ibfk_1` FOREIGN KEY (`perro_id`) REFERENCES `perro` (`perro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collar`
--

LOCK TABLES `collar` WRITE;
/*!40000 ALTER TABLE `collar` DISABLE KEYS */;
/*!40000 ALTER TABLE `collar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamento`
--

DROP TABLE IF EXISTS `departamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departamento` (
  `departamento_id` smallint NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `pais_id` tinyint DEFAULT NULL,
  PRIMARY KEY (`departamento_id`),
  KEY `pais_id` (`pais_id`),
  CONSTRAINT `departamento_ibfk_1` FOREIGN KEY (`pais_id`) REFERENCES `pais` (`pais_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamento`
--

LOCK TABLES `departamento` WRITE;
/*!40000 ALTER TABLE `departamento` DISABLE KEYS */;
INSERT INTO `departamento` VALUES (1,'Atlántico',1),(2,'Antioquia',1),(3,'Bogotá',1),(4,'Cundinamarca',1),(5,'Valle del Cauca',1),(6,'Santander',1);
/*!40000 ALTER TABLE `departamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emocion`
--

DROP TABLE IF EXISTS `emocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emocion` (
  `emocion_id` tinyint NOT NULL,
  `nombre_emocion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`emocion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emocion`
--

LOCK TABLES `emocion` WRITE;
/*!40000 ALTER TABLE `emocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `emocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`,`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medio`
--

DROP TABLE IF EXISTS `medio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medio` (
  `medio_id` tinyint NOT NULL,
  `tipo_medio` varchar(100) DEFAULT NULL,
  `ruta` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`medio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medio`
--

LOCK TABLES `medio` WRITE;
/*!40000 ALTER TABLE `medio` DISABLE KEYS */;
/*!40000 ALTER TABLE `medio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacion` (
  `notificacion_id` bigint NOT NULL,
  `propietario_id` mediumint DEFAULT NULL,
  `tipo_notificacion_id` tinyint DEFAULT NULL,
  `mensaje` varchar(500) DEFAULT NULL,
  `estado` enum('pendiente','enviada','leída') DEFAULT NULL,
  `fecha_generacion` datetime DEFAULT NULL,
  PRIMARY KEY (`notificacion_id`),
  KEY `propietario_id` (`propietario_id`),
  KEY `tipo_notificacion_id` (`tipo_notificacion_id`),
  CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`propietario_id`) REFERENCES `propietario` (`propietario_id`),
  CONSTRAINT `notificacion_ibfk_2` FOREIGN KEY (`tipo_notificacion_id`) REFERENCES `tipo_notificacion` (`tipo_notificacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion`
--

LOCK TABLES `notificacion` WRITE;
/*!40000 ALTER TABLE `notificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pais`
--

DROP TABLE IF EXISTS `pais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pais` (
  `pais_id` tinyint NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pais_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pais`
--

LOCK TABLES `pais` WRITE;
/*!40000 ALTER TABLE `pais` DISABLE KEYS */;
INSERT INTO `pais` VALUES (1,'Colombia');
/*!40000 ALTER TABLE `pais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'dsd@gmail.com','974230b1e36b1ee368e93dc2c381a98e302dab4495b2c614240f40510c6a779a','2025-04-18 05:01:40','2025-04-17 21:01:40'),(2,'jdgutierrezotalvaro04@gmail.com','29a52fef551356a320ba0a1f940685e769fa1d124df3a7c3cb24b25e399ef823','2025-04-18 05:02:35','2025-04-17 21:02:35');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perro`
--

DROP TABLE IF EXISTS `perro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perro` (
  `perro_id` int NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `fechanacimiento` date DEFAULT NULL,
  `peso` decimal(4,2) DEFAULT NULL,
  `genero` enum('M','F') DEFAULT NULL,
  `esterilizado` tinyint(1) DEFAULT NULL,
  `propietario_id` mediumint DEFAULT NULL,
  `raza_id` smallint DEFAULT NULL,
  PRIMARY KEY (`perro_id`),
  KEY `propietario_id` (`propietario_id`),
  KEY `raza_id` (`raza_id`),
  CONSTRAINT `perro_ibfk_1` FOREIGN KEY (`propietario_id`) REFERENCES `propietario` (`propietario_id`),
  CONSTRAINT `perro_ibfk_2` FOREIGN KEY (`raza_id`) REFERENCES `raza` (`raza_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perro`
--

LOCK TABLES `perro` WRITE;
/*!40000 ALTER TABLE `perro` DISABLE KEYS */;
/*!40000 ALTER TABLE `perro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan` (
  `plan_id` tinyint NOT NULL,
  `nombre_plan` varchar(30) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `costo` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan`
--

LOCK TABLES `plan` WRITE;
/*!40000 ALTER TABLE `plan` DISABLE KEYS */;
INSERT INTO `plan` VALUES (1,'Plan Básico','Acceso limitado a funciones principales',19.99),(2,'Plan Premium','Acceso completo con soporte prioritario',49.99),(3,'Plan Familiar','Permite múltiples usuarios bajo una sola cuenta',29.99);
/*!40000 ALTER TABLE `plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `propietario`
--

DROP TABLE IF EXISTS `propietario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propietario` (
  `propietario_id` mediumint NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `primer_nombre` varchar(50) DEFAULT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `residencia_id` int DEFAULT NULL,
  `plan_id` tinyint DEFAULT NULL,
  PRIMARY KEY (`propietario_id`),
  KEY `residencia_id` (`residencia_id`),
  KEY `plan_id` (`plan_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `propietario_ibfk_1` FOREIGN KEY (`residencia_id`) REFERENCES `residencia` (`residencia_id`),
  CONSTRAINT `propietario_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plan` (`plan_id`),
  CONSTRAINT `propietario_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propietario`
--

LOCK TABLES `propietario` WRITE;
/*!40000 ALTER TABLE `propietario` DISABLE KEYS */;
INSERT INTO `propietario` VALUES (1,9,'Juan','Diego','Gutierrez','Otalvaro','3023417958','dsd@gmail.com',1,1);
/*!40000 ALTER TABLE `propietario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raza`
--

DROP TABLE IF EXISTS `raza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `raza` (
  `raza_id` smallint NOT NULL,
  `nombre_raza` varchar(50) DEFAULT NULL,
  `predisposicion_problemas_conducta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`raza_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raza`
--

LOCK TABLES `raza` WRITE;
/*!40000 ALTER TABLE `raza` DISABLE KEYS */;
/*!40000 ALTER TABLE `raza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registro_comportamiento`
--

DROP TABLE IF EXISTS `registro_comportamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registro_comportamiento` (
  `registro_id` bigint NOT NULL,
  `collar_id` mediumint DEFAULT NULL,
  `emocion_id` tinyint DEFAULT NULL,
  `tipo_patron_id` tinyint DEFAULT NULL,
  `certeza` decimal(3,0) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `duracion` smallint DEFAULT NULL,
  PRIMARY KEY (`registro_id`),
  KEY `collar_id` (`collar_id`),
  KEY `emocion_id` (`emocion_id`),
  KEY `tipo_patron_id` (`tipo_patron_id`),
  CONSTRAINT `registro_comportamiento_ibfk_1` FOREIGN KEY (`collar_id`) REFERENCES `collar` (`collar_id`),
  CONSTRAINT `registro_comportamiento_ibfk_2` FOREIGN KEY (`emocion_id`) REFERENCES `emocion` (`emocion_id`),
  CONSTRAINT `registro_comportamiento_ibfk_3` FOREIGN KEY (`tipo_patron_id`) REFERENCES `tipo_patron` (`tipo_patron_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_comportamiento`
--

LOCK TABLES `registro_comportamiento` WRITE;
/*!40000 ALTER TABLE `registro_comportamiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `registro_comportamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registro_sensores`
--

DROP TABLE IF EXISTS `registro_sensores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registro_sensores` (
  `registro_id` bigint NOT NULL,
  `collar_id` mediumint DEFAULT NULL,
  `decibelios` decimal(4,2) DEFAULT NULL,
  `frecuencia` decimal(6,2) DEFAULT NULL,
  `aceleracion_x` decimal(8,2) DEFAULT NULL,
  `temperatura` decimal(4,1) DEFAULT NULL,
  `pulsaciones_min` smallint DEFAULT NULL,
  `marca_tiempo` datetime DEFAULT NULL,
  PRIMARY KEY (`registro_id`),
  KEY `collar_id` (`collar_id`),
  CONSTRAINT `registro_sensores_ibfk_1` FOREIGN KEY (`collar_id`) REFERENCES `collar` (`collar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registro_sensores`
--

LOCK TABLES `registro_sensores` WRITE;
/*!40000 ALTER TABLE `registro_sensores` DISABLE KEYS */;
/*!40000 ALTER TABLE `registro_sensores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `residencia`
--

DROP TABLE IF EXISTS `residencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `residencia` (
  `residencia_id` int NOT NULL AUTO_INCREMENT,
  `ciudad_id` mediumint DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`residencia_id`),
  KEY `ciudad_id` (`ciudad_id`),
  CONSTRAINT `residencia_ibfk_1` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudad` (`ciudad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residencia`
--

LOCK TABLES `residencia` WRITE;
/*!40000 ALTER TABLE `residencia` DISABLE KEYS */;
INSERT INTO `residencia` VALUES (1,1,'Carrera 59 #67-40');
/*!40000 ALTER TABLE `residencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sugerencia_etologica`
--

DROP TABLE IF EXISTS `sugerencia_etologica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sugerencia_etologica` (
  `sug_id` int NOT NULL,
  `emocion_id` tinyint DEFAULT NULL,
  `medio_id` tinyint DEFAULT NULL,
  `contenido` text,
  PRIMARY KEY (`sug_id`),
  KEY `emocion_id` (`emocion_id`),
  KEY `medio_id` (`medio_id`),
  CONSTRAINT `sugerencia_etologica_ibfk_1` FOREIGN KEY (`emocion_id`) REFERENCES `emocion` (`emocion_id`),
  CONSTRAINT `sugerencia_etologica_ibfk_2` FOREIGN KEY (`medio_id`) REFERENCES `medio` (`medio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sugerencia_etologica`
--

LOCK TABLES `sugerencia_etologica` WRITE;
/*!40000 ALTER TABLE `sugerencia_etologica` DISABLE KEYS */;
/*!40000 ALTER TABLE `sugerencia_etologica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_notificacion`
--

DROP TABLE IF EXISTS `tipo_notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_notificacion` (
  `tipo_notificacion_id` tinyint NOT NULL,
  `nombre_tipo` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`tipo_notificacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_notificacion`
--

LOCK TABLES `tipo_notificacion` WRITE;
/*!40000 ALTER TABLE `tipo_notificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipo_notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_patron`
--

DROP TABLE IF EXISTS `tipo_patron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_patron` (
  `tipo_patron_id` tinyint NOT NULL,
  `nombre_patron` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`tipo_patron_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_patron`
--

LOCK TABLES `tipo_patron` WRITE;
/*!40000 ALTER TABLE `tipo_patron` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipo_patron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `role` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'jdgutierrezotalvaro04@gmail.com','$2y$10$phOaZlo7a8hZUCFe7AIHcO2WOazU4yQgy.d28LQDCw0c6FFIKNa9W','2025-04-17 21:02:10','2025-05-07 00:55:59','admin'),(9,'dsd@gmail.com','$2y$10$Nl7.PQrDc3YeGzvYSrRUA./cEwi8bqmn8SYaUHB/p7m8q6NsQNrJm','2025-05-05 10:14:55','2025-05-05 11:42:53','usuario');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-09 12:51:53
