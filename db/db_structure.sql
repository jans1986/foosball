-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 05 nov 2013 om 10:47
-- Serverversie: 5.5.20
-- PHP-Versie: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `foosball`
--
CREATE DATABASE `foosball` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `foosball`;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `match_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_1_p1` int(5) NOT NULL DEFAULT '0',
  `team_1_p2` int(5) NOT NULL DEFAULT '0',
  `team_2_p1` int(5) NOT NULL DEFAULT '0',
  `team_2_p2` int(5) NOT NULL DEFAULT '0',
  `team_1_score` int(2) NOT NULL,
  `team_2_score` int(2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`match_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=175 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `player_stats`
--

CREATE TABLE IF NOT EXISTS `player_stats` (
  `match_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `goals` int(11) NOT NULL,
  `goals_against` int(11) NOT NULL,
  `points` int(1) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`match_id`,`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=29 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
