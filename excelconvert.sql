-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 20. Aug 2019 um 16:16
-- Server-Version: 10.1.38-MariaDB
-- PHP-Version: 7.2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `excelconvert`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `public_job_id` varchar(36) CHARACTER SET latin1 NOT NULL,
  `filename` varchar(500) CHARACTER SET latin1 NOT NULL,
  `fileextension` varchar(5) NOT NULL,
  `option_include_utf8bom` tinyint(1) NOT NULL DEFAULT '1',
  `option_delimiter` varchar(1) NOT NULL DEFAULT ',',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_start` timestamp NULL DEFAULT NULL,
  `date_finished` timestamp NULL DEFAULT NULL,
  `job_status` enum('pending','in_progress','failed','finished') CHARACTER SET latin1 NOT NULL DEFAULT 'pending',
  `failed_information` mediumtext CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
