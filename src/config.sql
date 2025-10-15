/*M!999999- enable the sandbox mode */
-- MariaDB dump 10.19-11.8.1-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: poster_generator
-- ------------------------------------------------------
-- Server version	11.8.1-MariaDB-2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `author`
--

LOCK TABLES `author` WRITE;
/*!40000 ALTER TABLE `author` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `author` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `author_to_poster`
--

DROP TABLE IF EXISTS `author_to_poster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `author_to_poster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) DEFAULT NULL,
  `poster_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `poster_id` (`poster_id`),
  CONSTRAINT `author_to_poster_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE CASCADE,
  CONSTRAINT `author_to_poster_ibfk_2` FOREIGN KEY (`poster_id`) REFERENCES `poster` (`poster_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `author_to_poster`
--

LOCK TABLES `author_to_poster` WRITE;
/*!40000 ALTER TABLE `author_to_poster` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `author_to_poster` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `box`
--

DROP TABLE IF EXISTS `box`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `box` (
  `box_id` int(11) NOT NULL AUTO_INCREMENT,
  `poster_id` int(11) DEFAULT NULL,
  `content` MEDIUMBLOB NOT NULL,
  PRIMARY KEY (`box_id`),
  KEY `poster_id` (`poster_id`),
  CONSTRAINT `box_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `poster` (`poster_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `box`
--

LOCK TABLES `box` WRITE;
/*!40000 ALTER TABLE `box` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `box` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `image` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `upload_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `last_edit_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `type` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `last_modified` int(11) NOT NULL,
  `webkit_relative_path` varchar(255) NOT NULL,
  `data` longblob NOT NULL,
  `fk_poster` int(11) NOT NULL,
  `data_hash` VARCHAR(512),
  PRIMARY KEY (`image_id`),
  KEY `fk_poster` (`fk_poster`),
  CONSTRAINT `image_ibfk_1` FOREIGN KEY (`fk_poster`) REFERENCES `poster` (`poster_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image`
--

LOCK TABLES `image` WRITE;
/*!40000 ALTER TABLE `image` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `image` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `poster`
--

DROP TABLE IF EXISTS `poster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `poster` (
  `poster_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `creation_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `last_edit_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `fk_view_mode` int(11) NOT NULL DEFAULT 2,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`poster_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_view_mode` (`fk_view_mode`),
  CONSTRAINT `fk_view_mode` FOREIGN KEY (`fk_view_mode`) REFERENCES `view_modes` (`ID`),
  CONSTRAINT `poster_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poster`
--

LOCK TABLES `poster` WRITE;
/*!40000 ALTER TABLE `poster` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `poster` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `sessionID` varchar(256) NOT NULL,
  `expiration_date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `pass_sha` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `pepper` varchar(255) NOT NULL,
  `registration_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `last_login_date` int(11) NOT NULL DEFAULT unix_timestamp(),
  `access_level` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `view_modes`
--

DROP TABLE IF EXISTS `view_modes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `view_modes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `view_modes`
--

LOCK TABLES `view_modes` WRITE;
/*!40000 ALTER TABLE `view_modes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `view_modes` VALUES
(1,'public'),
(2,'private');
/*!40000 ALTER TABLE `view_modes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Final view structure for view `ranked_posters`
--

/*!50001 DROP VIEW IF EXISTS `ranked_posters`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ranked_posters` AS select row_number() over ( order by `poster`.`poster_id`) AS `local_id`,`poster`.`poster_id` AS `poster_id`,`poster`.`user_id` AS `user_id` from `poster` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Dump completed on 2025-04-16 14:16:02
