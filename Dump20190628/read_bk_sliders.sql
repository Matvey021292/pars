-- MySQL dump 10.13  Distrib 5.7.26, for Linux (x86_64)
--
-- Host: localhost    Database: read_bk
-- ------------------------------------------------------
-- Server version	5.7.26-0ubuntu0.18.04.1

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
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sliders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `img` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sliders`
--

LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;
INSERT INTO `sliders` VALUES (1,'https://librusec.pro/static/img/b/cover/6/643706','Попаданчество — оно такое','Попаданчество — оно такое<br><br>\r\nНаправленность: Джен<br><br>\r\nАвтор: 4itaka<br><br>\r\nРедактор:<br>\r\nUnslaad<br><br>\r\nФэндом:<br>\r\nThe Elder Scrolls V: Skyrim<br><br>\r\nПерсонажи: Дульвит, Алесса (довакин), ж! Альдуин, Эйла, Лидия, Дженсин (хускарл), Астрид и др.<br><br>\r\nРейтинг: R<br><br>\r\nЖанры:<br>\r\nФэнтези, Экшн (action), POV, AU,  Мифические существа<br><br>\r\nПредупреждения:<br>\r\nСмерть основного персонажа, OOC, Мэри Сью (Марти Стью), ОМП, ОЖП, Смена пола (gender switch)<br><br>\r\nРазмер:<br>\r\nМакси<br><br>\r\nКол-во глав:<br>\r\n6<br><br>\r\nСтатус:<br>\r\nЗакончен<br>\r\nРедакция фанфика с учётом накопившихся пожеланий.<br><br>\r\nПубликация на других ресурсах:<br>\r\nТолько с моего непосредственного разрешения.<br><br>\r\nПримечания автора:<br>\r\nЗарегистрировался на Самиздате. Кто хочет, может читать там, все картинки я буду выкладывать именно там: <a href=\"http://samlib.ru/4/4itaka/\" rel=\"nofollow\">http://samlib.ru/4/4itaka/</a><br><br>\r\nОписание:<br>\r\nУ меня было всё как у всех. Жил себе, потом умер. А потом, как чёрт из-под табуретки, явился передо мной господин и сделал предложение, от которого невозможно отказаться. В общем, ничего особенного. Только господин этот — не дон Корлеоне и даже не дон Педро из Бразилии, где в лесах много диких обезьян, а господь бог из одной весьма популярной игры, которую я в своё время прошёл вдоль и поперёк. Ну, и что прикажете делать в подобной ситуации? Правильно. Не можешь победить — возглавь.','popadanchestvo—onotakoe');
/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-06-28 20:21:56
