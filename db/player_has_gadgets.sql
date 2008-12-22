-- phpMyAdmin SQL Dump
-- version 2.11.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 22, 2008 at 06:52 PM
-- Server version: 5.0.54
-- PHP Version: 5.2.6-pl2-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `smr_15`
--

-- --------------------------------------------------------

--
-- Table structure for table `player_has_gadget`
--

CREATE TABLE `player_has_gadget` (
  `game_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `gadget_id` int(10) unsigned NOT NULL,
  `cooldown` int(10) unsigned NOT NULL default '0',
  `equipped` int(11) NOT NULL default '0',
  `lasts_until` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`game_id`,`account_id`,`gadget_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;