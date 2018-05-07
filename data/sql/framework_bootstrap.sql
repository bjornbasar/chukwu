/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_actions`
--

DROP TABLE IF EXISTS `auth_actions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auth_actions` (
  `auth_actionid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`auth_actionid`),
  KEY `index_2` USING BTREE (`name`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `auth_actions`
--

LOCK TABLES `auth_actions` WRITE;
/*!40000 ALTER TABLE `auth_actions` DISABLE KEYS */;
INSERT INTO `auth_actions` VALUES (1,'view','view module',1),(2,'notice','receive notices from module',1),(3,'update','can update data in module',1),(4,'create','can create new data in module',1),(5,'assign','assign data in module',1),(6,'admin','admin rights',1);
/*!40000 ALTER TABLE `auth_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_modules`
--

DROP TABLE IF EXISTS `auth_modules`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auth_modules` (
  `auth_moduleid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `inherits` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `sla1` int(10) unsigned NOT NULL,
  `sla2` int(10) unsigned NOT NULL,
  `sla3` int(10) unsigned NOT NULL,
  `emailfrom` varchar(100) NOT NULL,
  PRIMARY KEY  (`auth_moduleid`),
  KEY `index_2` USING BTREE (`name`,`inherits`,`status`,`sla1`,`sla2`,`sla3`,`emailfrom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auth_permissions`
--

DROP TABLE IF EXISTS `auth_permissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auth_permissions` (
  `auth_permissionid` int(10) unsigned NOT NULL auto_increment,
  `auth_roleid` int(10) unsigned NOT NULL,
  `auth_moduleid` int(10) unsigned NOT NULL,
  `auth_actionid` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`auth_permissionid`),
  UNIQUE KEY `index_2` USING BTREE (`auth_roleid`,`auth_moduleid`,`auth_actionid`),
  KEY `index_3` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auth_roles`
--

DROP TABLE IF EXISTS `auth_roles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auth_roles` (
  `auth_roleid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `inherits` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`auth_roleid`),
  KEY `index_2` (`name`,`inherits`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `auth_users`
--

DROP TABLE IF EXISTS `auth_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `auth_users` (
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `auth_roleid` int(10) unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY  (`username`),
  KEY `index_3` (`auth_roleid`),
  KEY `index_2` USING BTREE (`password`,`status`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
