-- phpMyAdmin SQL Dump
-- version 2.11.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 22, 2008 at 07:20 PM
-- Server version: 5.0.54
-- PHP Version: 5.2.6-pl2-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `smr_15`
--

-- --------------------------------------------------------

--
-- Table structure for table `player_repaired`
--

CREATE TABLE `player_repaired` (
  `account_id` int(10) unsigned NOT NULL,
  `game_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `amount` int(10) NOT NULL,
  `source` enum('Normal','Breakdown') NOT NULL,
  PRIMARY KEY  (`account_id`,`game_id`,`time`,`amount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
