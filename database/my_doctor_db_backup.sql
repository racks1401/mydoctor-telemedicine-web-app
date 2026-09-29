-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: my_doctor_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_duration` int(10) unsigned NOT NULL DEFAULT 30,
  `appointment_type` enum('follow-up','consultation') NOT NULL DEFAULT 'consultation',
  `appointment_mode` enum('offline','chat','audio_call','video_call') NOT NULL,
  `appointment_charge` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('cash','upi','card','net_banking','wallet') DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','resheduled') DEFAULT 'pending',
  `problem_description` text DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `cancelled_by` enum('doctor','patient','admin') DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,58,22,'2026-07-03','09:00:00',30,'consultation','video_call',500.00,'paid','upi','TXN100001','completed','High fever for 3 days','Likely viral fever',NULL,'https://meet.mydoctor.com/room101',NULL,NULL,'2026-07-03 09:30:00','2026-07-02 09:30:56','2026-07-02 09:30:56'),(2,59,23,'2026-07-03','10:00:00',30,'consultation','chat',300.00,'paid','wallet','TXN100002','completed','Skin allergy','Possible dermatitis',NULL,NULL,NULL,NULL,'2026-07-03 10:20:00','2026-07-02 09:30:56','2026-07-02 09:30:56'),(3,60,24,'2026-07-04','11:00:00',30,'consultation','offline',700.00,'pending',NULL,NULL,'confirmed','Back pain',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-02 09:30:56','2026-07-02 09:30:56'),(4,61,25,'2026-07-04','12:30:00',30,'consultation','audio_call',400.00,'paid','card','TXN100003','completed','Headache','Migraine suspected',NULL,NULL,NULL,NULL,'2026-07-04 12:55:00','2026-07-02 09:30:56','2026-07-02 09:30:56'),(5,62,26,'2026-07-05','15:00:00',30,'consultation','video_call',800.00,'failed','upi','TXN100004','cancelled','Chest discomfort',NULL,NULL,'https://meet.mydoctor.com/room105','patient','Payment failed',NULL,'2026-07-02 09:30:56','2026-07-02 09:30:56'),(6,63,27,'2026-07-06','09:30:00',30,'consultation','offline',600.00,'paid','cash','TXN100005','completed','Diabetes follow-up','Sugar improving',NULL,NULL,NULL,NULL,'2026-07-06 09:50:00','2026-07-02 09:30:56','2026-07-02 09:30:56'),(7,64,28,'2026-07-07','14:00:00',30,'consultation','chat',250.00,'paid','wallet','TXN100006','completed','Acne treatment','Continue medication',NULL,NULL,NULL,NULL,'2026-07-07 14:15:00','2026-07-02 09:30:56','2026-07-02 09:30:56'),(8,65,29,'2026-07-08','16:00:00',30,'consultation','video_call',900.00,'paid','net_banking','TXN100007','confirmed','Joint pain',NULL,NULL,'https://meet.mydoctor.com/room108',NULL,NULL,NULL,'2026-07-02 09:30:56','2026-07-02 09:30:56'),(9,66,30,'2026-07-09','11:30:00',30,'consultation','audio_call',350.00,'refunded','card','TXN100008','cancelled','Cold and cough',NULL,NULL,NULL,'doctor','Doctor unavailable',NULL,'2026-07-02 09:30:56','2026-07-02 09:30:56'),(10,67,31,'2026-07-10','17:00:00',30,'consultation','offline',550.00,'paid','upi','TXN100009','pending','Routine health check',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-02 09:30:56','2026-07-02 09:30:56'),(11,58,2,'2026-07-11','10:00:00',30,'consultation','video_call',500.00,'pending','upi',NULL,'pending','Frequent headaches',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-08 14:56:08','2026-07-08 14:56:08'),(12,58,2,'2026-07-08','18:00:00',30,'follow-up','video_call',500.00,'paid','upi','TXN100001','confirmed','Follow-up for blood pressure',NULL,NULL,'https://meet.example.com/room-1002',NULL,NULL,NULL,'2026-07-08 14:56:08','2026-07-08 14:56:08'),(13,58,2,'2026-07-13','11:30:00',30,'consultation','video_call',700.00,'paid','card','TXN100002','confirmed','Back pain consultation',NULL,NULL,'https://meet.example.com/room-1003',NULL,NULL,NULL,'2026-07-08 14:56:08','2026-07-08 14:56:08'),(14,58,2,'2026-07-04','09:30:00',30,'consultation','offline',600.00,'paid','cash',NULL,'completed','Seasonal allergy','Patient recovering well.','Cetirizine 10mg once daily for 5 days.',NULL,NULL,NULL,'2026-07-04 20:26:08','2026-07-08 14:56:08','2026-07-08 14:56:08'),(15,58,2,'2026-06-28','15:00:00',30,'follow-up','chat',400.00,'paid','upi','TXN100003','completed','Routine diabetes follow-up','Blood sugar under control.','Continue Metformin 500mg twice daily.',NULL,NULL,NULL,'2026-06-28 20:26:08','2026-07-08 14:56:08','2026-07-08 14:56:08'),(16,61,2,'2026-07-15','09:00:00',30,'consultation','video_call',500.00,'paid','upi',NULL,'pending','General consultation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(17,61,2,'2026-07-16','10:00:00',30,'follow-up','chat',300.00,'paid','card',NULL,'confirmed','Follow-up consultation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(18,61,2,'2026-07-10','11:00:00',30,'consultation','offline',600.00,'paid','cash',NULL,'completed','Routine checkup',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(19,61,2,'2026-07-08','12:00:00',30,'consultation','audio_call',400.00,'refunded','upi',NULL,'cancelled','Cancelled appointment',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(20,62,2,'2026-07-15','09:30:00',30,'consultation','offline',700.00,'paid','cash',NULL,'pending','Fever and cough',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(21,62,2,'2026-07-16','10:30:00',30,'follow-up','video_call',500.00,'paid','upi',NULL,'confirmed','Review reports',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(22,62,2,'2026-07-09','11:30:00',30,'consultation','chat',350.00,'paid','wallet',NULL,'completed','Skin allergy',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(23,62,2,'2026-07-07','12:30:00',30,'consultation','audio_call',400.00,'failed','card',NULL,'cancelled','Patient unavailable',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(24,63,2,'2026-07-17','09:00:00',30,'consultation','chat',450.00,'pending',NULL,NULL,'pending','Back pain',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(25,63,2,'2026-07-18','10:00:00',30,'follow-up','offline',650.00,'paid','cash',NULL,'confirmed','Physiotherapy follow-up',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(26,63,2,'2026-07-11','11:00:00',30,'consultation','video_call',800.00,'paid','upi',NULL,'completed','Blood pressure check',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(27,63,2,'2026-07-06','12:00:00',30,'consultation','audio_call',300.00,'refunded','wallet',NULL,'cancelled','Doctor unavailable',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(28,64,2,'2026-07-19','09:15:00',30,'consultation','offline',500.00,'pending',NULL,NULL,'pending','Eye irritation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(29,64,2,'2026-07-20','10:15:00',30,'follow-up','video_call',600.00,'paid','upi',NULL,'confirmed','Eye checkup',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(30,64,2,'2026-07-12','11:15:00',30,'consultation','chat',350.00,'paid','card',NULL,'completed','Vision test',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(31,64,2,'2026-07-05','12:15:00',30,'consultation','audio_call',300.00,'failed','net_banking',NULL,'cancelled','Rescheduled',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(32,65,2,'2026-07-21','09:45:00',30,'consultation','video_call',750.00,'pending',NULL,NULL,'pending','Dental pain',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(33,65,2,'2026-07-22','10:45:00',30,'follow-up','offline',500.00,'paid','cash',NULL,'confirmed','Dental follow-up',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(34,65,2,'2026-07-13','11:45:00',30,'consultation','chat',400.00,'paid','upi',NULL,'completed','Cleaning completed',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(35,65,2,'2026-07-04','12:45:00',30,'consultation','audio_call',300.00,'refunded','wallet',NULL,'cancelled','Patient cancelled',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:11:04','2026-07-12 11:11:04'),(36,61,2,'2026-07-24','09:00:00',30,'consultation','video_call',500.00,'paid','upi',NULL,'pending','General consultation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(37,61,2,'2026-07-25','10:00:00',30,'follow-up','chat',300.00,'paid','wallet',NULL,'confirmed','Review treatment',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(38,61,2,'2026-07-26','11:00:00',30,'consultation','offline',600.00,'refunded','upi',NULL,'cancelled','Patient cancelled',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(39,62,2,'2026-07-24','09:30:00',30,'consultation','offline',700.00,'paid','cash',NULL,'pending','ENT consultation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(40,62,2,'2026-07-27','10:30:00',30,'follow-up','video_call',500.00,'paid','card',NULL,'confirmed','Follow-up visit',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(41,62,2,'2026-07-28','11:30:00',30,'consultation','audio_call',400.00,'refunded','upi',NULL,'cancelled','Doctor unavailable',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(42,63,2,'2026-07-29','09:00:00',30,'consultation','chat',450.00,'pending',NULL,NULL,'pending','Back pain',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(43,63,2,'2026-07-30','10:00:00',30,'follow-up','offline',650.00,'paid','cash',NULL,'confirmed','Physiotherapy review',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(44,63,2,'2026-07-31','11:00:00',30,'consultation','video_call',800.00,'refunded','wallet',NULL,'cancelled','Patient requested cancellation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(45,64,2,'2026-08-01','09:15:00',30,'consultation','offline',500.00,'pending',NULL,NULL,'pending','Eye checkup',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(46,64,2,'2026-08-02','10:15:00',30,'follow-up','video_call',600.00,'paid','upi',NULL,'confirmed','Vision review',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(47,64,2,'2026-08-03','11:15:00',30,'consultation','chat',350.00,'refunded','card',NULL,'cancelled','Cancelled by doctor',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(48,65,2,'2026-08-04','09:45:00',30,'consultation','video_call',750.00,'pending',NULL,NULL,'pending','Dental consultation',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(49,65,2,'2026-08-05','10:45:00',30,'follow-up','offline',500.00,'paid','cash',NULL,'confirmed','Dental follow-up',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03'),(50,65,2,'2026-08-06','11:45:00',30,'consultation','audio_call',300.00,'refunded','wallet',NULL,'cancelled','Patient unavailable',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-12 11:34:03','2026-07-12 11:34:03');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_logs`
--

DROP TABLE IF EXISTS `blocked_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` enum('blocked','unblocked') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_blocked_logs_user_log` (`user_id`,`log_id`),
  CONSTRAINT `blocked_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `blocked_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_logs`
--

LOCK TABLES `blocked_logs` WRITE;
/*!40000 ALTER TABLE `blocked_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_availability`
--

DROP TABLE IF EXISTS `doctor_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctor_availability` (
  `availability_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` tinyint(3) unsigned NOT NULL DEFAULT 30,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`availability_id`),
  KEY `idx_doctor_day` (`doctor_id`,`day_of_week`),
  CONSTRAINT `fk_doctor_availability_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_availability`
--

LOCK TABLES `doctor_availability` WRITE;
/*!40000 ALTER TABLE `doctor_availability` DISABLE KEYS */;
INSERT INTO `doctor_availability` VALUES (1,58,'Monday','09:00:00','12:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(2,58,'Wednesday','10:00:00','01:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(3,58,'Friday','02:00:00','05:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(4,58,'Saturday','09:00:00','11:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(5,58,'Sunday','10:00:00','12:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(6,59,'Monday','10:00:00','01:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(7,59,'Tuesday','09:00:00','12:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(8,59,'Thursday','02:00:00','06:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(9,59,'Friday','09:00:00','11:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(10,59,'Saturday','03:00:00','06:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(11,60,'Tuesday','09:00:00','12:00:00',20,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(12,60,'Wednesday','02:00:00','05:00:00',20,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(13,60,'Thursday','10:00:00','01:00:00',20,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(14,60,'Friday','09:00:00','12:00:00',20,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(15,60,'Sunday','03:00:00','06:00:00',20,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(16,61,'Monday','08:00:00','11:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(17,61,'Tuesday','01:00:00','05:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(18,61,'Thursday','09:00:00','12:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(19,61,'Saturday','10:00:00','01:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(20,61,'Sunday','09:00:00','11:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(21,62,'Monday','09:30:00','12:30:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(22,62,'Wednesday','09:00:00','01:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(23,62,'Thursday','02:00:00','05:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(24,62,'Friday','10:00:00','01:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19'),(25,62,'Saturday','09:00:00','12:00:00',30,1,'2026-07-15 05:42:19','2026-07-15 05:42:19');
/*!40000 ALTER TABLE `doctor_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_slots`
--

DROP TABLE IF EXISTS `doctor_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctor_slots` (
  `slot_id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `status` enum('available','booked','blocked','leave') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`slot_id`),
  KEY `fk_slot_appointment` (`appointment_id`),
  KEY `idx_slot_date` (`doctor_id`,`slot_date`),
  KEY `idx_slot_status` (`status`),
  CONSTRAINT `fk_slot_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_slot_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_slots`
--

LOCK TABLES `doctor_slots` WRITE;
/*!40000 ALTER TABLE `doctor_slots` DISABLE KEYS */;
INSERT INTO `doctor_slots` VALUES (1,58,'2026-07-16','09:00:00','09:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(2,58,'2026-07-16','09:30:00','10:00:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(3,58,'2026-07-17','10:00:00','10:30:00',NULL,'blocked','2026-07-15 05:42:30','2026-07-15 05:42:30'),(4,58,'2026-07-18','09:00:00','09:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(5,58,'2026-07-19','10:00:00','10:30:00',NULL,'leave','2026-07-15 05:42:30','2026-07-15 05:42:30'),(6,59,'2026-07-16','10:00:00','10:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(7,59,'2026-07-17','09:00:00','09:30:00',NULL,'blocked','2026-07-15 05:42:30','2026-07-15 05:42:30'),(8,59,'2026-07-18','02:00:00','02:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(9,59,'2026-07-19','03:00:00','03:30:00',NULL,'leave','2026-07-15 05:42:30','2026-07-15 05:42:30'),(10,59,'2026-07-20','09:00:00','09:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(11,60,'2026-07-16','09:00:00','09:20:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(12,60,'2026-07-17','02:00:00','02:20:00',NULL,'blocked','2026-07-15 05:42:30','2026-07-15 05:42:30'),(13,60,'2026-07-18','10:00:00','10:20:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(14,60,'2026-07-19','09:00:00','09:20:00',NULL,'leave','2026-07-15 05:42:30','2026-07-15 05:42:30'),(15,60,'2026-07-20','03:00:00','03:20:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(16,61,'2026-07-16','08:00:00','08:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(17,61,'2026-07-17','01:00:00','01:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(18,61,'2026-07-18','09:00:00','09:30:00',NULL,'blocked','2026-07-15 05:42:30','2026-07-15 05:42:30'),(19,61,'2026-07-19','10:00:00','10:30:00',NULL,'leave','2026-07-15 05:42:30','2026-07-15 05:42:30'),(20,61,'2026-07-20','09:00:00','09:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(21,62,'2026-07-16','09:30:00','10:00:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(22,62,'2026-07-17','09:00:00','09:30:00',NULL,'blocked','2026-07-15 05:42:30','2026-07-15 05:42:30'),(23,62,'2026-07-18','02:00:00','02:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30'),(24,62,'2026-07-19','10:00:00','10:30:00',NULL,'leave','2026-07-15 05:42:30','2026-07-15 05:42:30'),(25,62,'2026-07-20','09:00:00','09:30:00',NULL,'available','2026-07-15 05:42:30','2026-07-15 05:42:30');
/*!40000 ALTER TABLE `doctor_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `medicines` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medicines`)),
  `lab_tests` text DEFAULT NULL,
  `advice` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`prescription_id`),
  UNIQUE KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (1,1,'Viral Fever','[{\"name\": \"Paracetamol\", \"dosage\": \"500 mg\", \"frequency\": \"1-1-1\", \"duration\": \"5 Days\"}, {\"name\": \"Vitamin C\", \"dosage\": \"1000 mg\", \"frequency\": \"1-0-0\", \"duration\": \"7 Days\"}]','CBC','Drink plenty of fluids and take proper rest.','2026-07-10','2026-07-02 09:31:46'),(2,2,'Dermatitis','[{\"name\": \"Cetirizine\", \"dosage\": \"10 mg\", \"frequency\": \"0-0-1\", \"duration\": \"7 Days\"}, {\"name\": \"Calamine Lotion\", \"dosage\": \"Apply\", \"frequency\": \"2 Times\", \"duration\": \"10 Days\"}]',NULL,'Avoid dust and allergens.','2026-07-15','2026-07-02 09:31:46'),(3,3,'Muscle Strain','[{\"name\": \"Ibuprofen\", \"dosage\": \"400 mg\", \"frequency\": \"1-1-1\", \"duration\": \"5 Days\"}]','X-Ray Lumbar Spine','Avoid heavy lifting.','2026-07-20','2026-07-02 09:31:46'),(4,4,'Migraine','[{\"name\": \"Sumatriptan\", \"dosage\": \"50 mg\", \"frequency\": \"SOS\", \"duration\": \"As Needed\"}]',NULL,'Maintain proper sleep schedule.',NULL,'2026-07-02 09:31:46'),(5,5,'Acid Reflux','[{\"name\": \"Pantoprazole\", \"dosage\": \"40 mg\", \"frequency\": \"1-0-0\", \"duration\": \"14 Days\"}]',NULL,'Avoid spicy food.','2026-07-19','2026-07-02 09:31:46'),(6,6,'Type 2 Diabetes','[{\"name\": \"Metformin\", \"dosage\": \"500 mg\", \"frequency\": \"1-0-1\", \"duration\": \"30 Days\"}]','HbA1c','Continue regular exercise.','2026-08-06','2026-07-02 09:31:46'),(7,7,'Acne Vulgaris','[{\"name\": \"Doxycycline\", \"dosage\": \"100 mg\", \"frequency\": \"1-0-0\", \"duration\": \"15 Days\"}, {\"name\": \"Benzoyl Peroxide Gel\", \"dosage\": \"Apply\", \"frequency\": \"Night\", \"duration\": \"30 Days\"}]',NULL,'Wash face twice daily.','2026-07-28','2026-07-02 09:31:46'),(8,8,'Osteoarthritis','[{\"name\": \"Diclofenac\", \"dosage\": \"50 mg\", \"frequency\": \"1-0-1\", \"duration\": \"10 Days\"}]','Knee X-Ray','Do light stretching exercises.','2026-07-22','2026-07-02 09:31:46'),(9,9,'Upper Respiratory Infection','[{\"name\": \"Azithromycin\", \"dosage\": \"500 mg\", \"frequency\": \"1-0-0\", \"duration\": \"3 Days\"}]',NULL,'Take warm fluids.',NULL,'2026-07-02 09:31:46'),(10,10,'General Health Check','[{\"name\": \"Multivitamin\", \"dosage\": \"1 Tablet\", \"frequency\": \"1-0-0\", \"duration\": \"30 Days\"}]','CBC, Blood Sugar, Lipid Profile','Maintain a balanced diet and exercise regularly.','2026-08-10','2026-07-02 09:31:46');
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('admin','doctor','patient') NOT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pin` varchar(10) DEFAULT NULL,
  `profile_url` varchar(255) DEFAULT NULL,
  `privacy_accepted` tinyint(1) DEFAULT 0,
  `privacy_accepted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_at` timestamp NULL DEFAULT NULL,
  `theme` enum('light','dark') DEFAULT 'light',
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `last_login_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_status_blocked_at` (`status`,`blocked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','active','Ravi Kumar','rkprabudh1401@gmail.com','$2y$10$jJj3n0lxJRiVKvf4c4/Tz.ssNOKblj4JVi.r7k7O83tH6bswN2Zm2','1234567890','male','2001-03-05','LIG 18, Saketpuri Colony','Faizabad','Uttar Pradesh','224001',NULL,1,'2026-06-21 16:24:23',NULL,'2026-07-06 10:03:14','2026-06-21 16:24:23','2026-07-06 10:22:52',NULL,'light',1,1,'::1'),(2,'patient','active','Ravi Kumar Prabudh','racks1401@gmail.com','$2y$10$cMrSbxH/ZhcPjlUrTYhOceSRG6bsDMr0VxP5S8zLIxyZCoABnEoYq','9123456701','male','2016-02-10','Saketpur Colony','Faizabad','Uttar Pradesh','224001',NULL,1,'2026-06-21 16:48:19',NULL,'2026-09-29 04:33:59','2026-06-21 16:48:19','2026-09-29 04:33:59',NULL,'light',1,0,'::1'),(3,'patient','active','Priya Singh','priya.singh01@gmail.com','$2y$10$iRSKRedaHfiKNu4LhjXgoOvxNCORPzfeupsonxaHWA3v/1ss3/o22','9123456702','female','2026-06-11','Kankarbagh','Patna','Bihar','800020',NULL,1,'2026-06-21 16:50:10',NULL,NULL,'2026-06-21 16:50:10','2026-06-21 16:50:10',NULL,'light',1,0,NULL),(4,'patient','active','Aman Verma','aman.verma01@gmail.com','$2y$10$rn6n0QmbKBggc0hoVkc8zOnx.nSc2F9p1aGGpU27wXjKlxfo.qX6W','9123456703','male','2026-06-03','Civil Lines','Lucknow','Uttar Pradesh','226001',NULL,1,'2026-06-21 16:51:25',NULL,NULL,'2026-06-21 16:51:25','2026-06-21 16:51:25',NULL,'light',1,0,NULL),(5,'patient','active','Neha Gupta','neha.gupta01@gmail.com','$2y$10$QuI9M/yE/t4uyKMy4S0ub.GkdC.C3QWcz6yXTXoQlzxoBbw9xEnF.','9123456704','female','2026-06-08','Gomti Nagar','Lucknow','Uttar Pradesh','226010',NULL,1,'2026-06-21 16:52:32',NULL,NULL,'2026-06-21 16:52:32','2026-06-30 09:50:30',NULL,'light',1,0,NULL),(22,'patient','active','Rohit Sharma','rohit.sharma01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456705','male','1995-07-25','Rajendra Nagar','Patna','Bihar','800016',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-21 17:04:54',NULL,'light',1,0,NULL),(23,'patient','active','Pooja Yadav','pooja.yadav01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456706','female','2001-01-17','Bailey Road','Patna','Bihar','800014',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-21 17:04:54',NULL,'light',1,0,NULL),(24,'patient','active','Deepak Mishra','deepak.mishra01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456707','male','1996-04-30','Alambagh','Lucknow','Uttar Pradesh','226005',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-30 10:02:30',NULL,'light',1,0,NULL),(25,'patient','active','Anjali Kumari','anjali.kumari01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456708','female','2002-06-11','Gardanibagh','Patna','Bihar','800002',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-21 17:04:54',NULL,'light',1,0,NULL),(26,'patient','active','Saurabh Tiwari','saurabh.tiwari01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456709','male','1998-09-19','Swaroop Nagar','Kanpur','Uttar Pradesh','208002',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-21 17:04:54',NULL,'light',1,0,NULL),(27,'patient','blocked','Nidhi Pandey','nidhi.pandey01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456710','female','2000-12-03','Fraser Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:50:23',NULL,'light',1,0,NULL),(28,'patient','blocked','Karan Srivastava','karan.srivastava01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456711','male','1997-02-24','Indira Nagar','Lucknow','Uttar Pradesh','226016',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:51:18',NULL,'light',1,0,NULL),(29,'patient','blocked','Shreya Sinha','shreya.sinha01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456712','female','2001-10-15','Patliputra Colony','Patna','Bihar','800013',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:52:26',NULL,'light',1,0,NULL),(30,'patient','active','Mohit Jain','mohit.jain01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456713','male','1994-08-27','Mahanagar','Lucknow','Uttar Pradesh','226006',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:49:54',NULL,'light',1,0,NULL),(31,'patient','blocked','Kavita Roy','kavita.roy01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456714','female','1999-05-09','Exhibition Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:51:50',NULL,'light',1,0,NULL),(32,'patient','blocked','Vivek Agrawal','vivek.agrawal01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456715','male','1996-01-31','Kakadeo','Kanpur','Uttar Pradesh','208025',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-30 09:50:21',NULL,'light',1,0,NULL),(33,'patient','blocked','Meera Choudhary','meera.choudhary01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456716','female','2000-07-18','Jankipuram','Lucknow','Uttar Pradesh','226021',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-30 09:52:09',NULL,'light',1,0,NULL),(34,'patient','blocked','Aditya Dubey','aditya.dubey01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456717','male','1998-11-22','Hazratganj','Lucknow','Uttar Pradesh','226001',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:50:29',NULL,'light',1,0,NULL),(35,'patient','blocked','Riya Sharma','riya.sharma01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456718','female','2002-03-05','Boring Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:51:24',NULL,'light',1,0,NULL),(36,'patient','blocked','Manish Kumar','manish.kumar01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456719','male','1995-06-13','Gandhi Maidan','Patna','Bihar','800001',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:50:36',NULL,'light',1,0,NULL),(37,'patient','blocked','Sneha Verma','sneha.verma01@gmail.com','$2y$10$yVZjWtgJfgttshmgsPwOyu9aErnBEns3Nr.je5GXAnsBs5p73oCxu','9123456720','female','2001-09-29','Rajajipuram','Lucknow','Uttar Pradesh','226017',NULL,1,'2026-06-21 17:04:54',NULL,NULL,'2026-06-21 17:04:54','2026-06-29 15:50:17',NULL,'light',1,0,NULL),(58,'doctor','active','Dr. Amit Sharma','amit.sharma@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543210','male','1985-04-12','Civil Lines','Lucknow','Uttar Pradesh','226001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-30 09:28:40',NULL,'light',1,0,NULL),(59,'doctor','active','Dr. Priya Verma','priya.verma@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543211','female','1988-07-21','Rajendra Nagar','Patna','Bihar','800016',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(60,'doctor','active','Dr. Rahul Singh','rahul.singh@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543212','male','1982-01-15','Gomti Nagar','Lucknow','Uttar Pradesh','226010',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-29 15:47:28',NULL,'light',1,0,NULL),(61,'doctor','blocked','Dr. Neha Gupta','neha.gupta@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543213','female','1990-03-25','Boring Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-30 11:23:54',NULL,'light',1,0,NULL),(62,'doctor','active','Dr. Arjun Kumar','arjun.kumar@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543214','male','1984-11-18','Kankarbagh','Patna','Bihar','800020',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(63,'doctor','active','Dr. Sneha Mishra','sneha.mishra@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543215','female','1989-06-30','Hazratganj','Lucknow','Uttar Pradesh','226001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(64,'doctor','active','Dr. Vikram Yadav','vikram.yadav@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543216','male','1983-02-08','Ashok Nagar','Kanpur','Uttar Pradesh','208001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(65,'doctor','active','Dr. Pooja Sinha','pooja.sinha@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543217','female','1991-09-14','Bailey Road','Patna','Bihar','800014',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(66,'doctor','active','Dr. Karan Mehta','karan.mehta@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543218','male','1987-05-11','Alambagh','Lucknow','Uttar Pradesh','226005',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(67,'doctor','active','Dr. Ritu Pandey','ritu.pandey@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543219','female','1986-12-03','Gandhi Maidan','Patna','Bihar','800001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(68,'doctor','active','Dr. Mohit Tiwari','mohit.tiwari@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543220','male','1985-08-17','Swaroop Nagar','Kanpur','Uttar Pradesh','208002',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(69,'doctor','active','Dr. Anjali Roy','anjali.roy@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543221','female','1992-04-22','Fraser Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(70,'doctor','active','Dr. Deepak Jain','deepak.jain@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543222','male','1981-10-09','Indira Nagar','Lucknow','Uttar Pradesh','226016',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(71,'doctor','active','Dr. Shreya Kapoor','shreya.kapoor@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543223','female','1993-01-28','Rajendra Nagar','Patna','Bihar','800016',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(72,'doctor','active','Dr. Manish Srivastava','manish.srivastava@gmail.com','DOCTOR_HASH','9876543224','male','1980-07-05','Mahanagar','Lucknow','Uttar Pradesh','226006',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(73,'doctor','active','Dr. Kavita Singh','kavita.singh@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543225','female','1988-11-11','Gardanibagh','Patna','Bihar','800002',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(74,'doctor','active','Dr. Saurabh Agrawal','saurabh.agrawal@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543226','male','1984-03-19','Kakadeo','Kanpur','Uttar Pradesh','208025',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(75,'doctor','active','Dr. Nidhi Choudhary','nidhi.choudhary@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543227','female','1990-06-07','Exhibition Road','Patna','Bihar','800001',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(76,'doctor','active','Dr. Rakesh Dubey','rakesh.dubey@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543228','male','1983-09-01','Jankipuram','Lucknow','Uttar Pradesh','226021',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL),(77,'doctor','active','Dr. Meera Joshi','meera.joshi@gmail.com','$2y$10$bC7j44dvtN5vDZaEYld0JuID0b8bmb2cYfmik1Tah3dsUc3ScYX6y','9876543229','female','1991-12-15','Patliputra Colony','Patna','Bihar','800013',NULL,1,'2026-06-21 17:11:08',NULL,NULL,'2026-06-21 17:11:08','2026-06-21 17:11:08',NULL,'light',1,0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verification_documents`
--

DROP TABLE IF EXISTS `verification_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verification_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_type` enum('medical_license','government_id','board_certification','insurance_card','others') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`document_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `verification_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verification_documents`
--

LOCK TABLES `verification_documents` WRITE;
/*!40000 ALTER TABLE `verification_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_documents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-29 12:31:26
