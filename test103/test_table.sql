-- phpMyAdmin SQL Dump
-- version 4.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 13, 2013 at 10:39 AM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `test_table`
--

DROP TABLE IF EXISTS `test_table`;
CREATE TABLE IF NOT EXISTS `test_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comments` text COLLATE utf8_unicode_ci,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `test_table`
--

INSERT INTO `test_table` (`id`, `name`, `attributes`, `comments`, `modified`, `created`) VALUES
(1, 'image', '{src:''img/testimage.jpg'', coord:{x:150, y:39}, width:200, height:200, strokeStyle : ''#FF0000'', angle : 45}', 'image element with attributes', '2013-12-11 15:44:44', '2013-11-22 00:00:00'),
(4, 'triangle', '{fillStyle:''#FF0000'',coord:{x:400, y:200},width:100,height:100, angle :180}', 'triangle', '2013-12-12 10:38:15', '2013-11-22 00:00:00'),
(5, 'square', '{fillStyle:''#FF00FF'',coord:{x:500, y:200},width:100,height:100, angle :-45}', 'Just a square', '2013-12-11 12:36:43', '0000-00-00 00:00:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
