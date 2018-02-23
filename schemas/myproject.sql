# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.35)
# Database: myproject
# Generation Time: 2017-09-05 02:02:50 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table cities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cities`;

CREATE TABLE `cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;

INSERT INTO `cities` (`id`, `name`, `country`)
VALUES
	(1,'Bogotá','Colombia'),
	(2,'Medellín','Colombia'),
	(3,'Cali','Colombia'),
	(4,'Barranquilla','Colombia'),
	(5,'Cartagena','Colombia'),
	(6,'Bucaramanga','Colombia');

/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `level` varchar(45) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(150) DEFAULT NULL,
  `mobile` varchar(150) DEFAULT NULL,
  `address` text,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `authorised` tinyint(1) DEFAULT '0',
  `block_expires` datetime DEFAULT NULL,
  `login_attempts` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username_2` (`username`),
  KEY `email` (`email`(100)),
  KEY `username` (`username`) USING BTREE,
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `username`, `password`, `firstname`, `lastname`, `level`, `email`, `phone`, `mobile`, `address`, `country`, `city`, `birthday`, `authorised`, `block_expires`, `login_attempts`)
VALUES
	(1,'admin','$2y$10$QS.T0VwA5/a78y2s6m2Yiulu2bhGPzH/W1ay7QslX830PO4RTCVj.','Super','Admin','Superuser','my@email.com','123123','123123123','My address','Colombia','Bucaramanga','1980-01-01',1,NULL,0);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users_access`;

CREATE TABLE `users_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `ip` varchar(250) DEFAULT NULL,
  `browser` text,
  `date` datetime DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `country` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
