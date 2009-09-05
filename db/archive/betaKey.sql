CREATE TABLE IF NOT EXISTS `beta_key` (
  `key` char(5) NOT NULL default '0',
  `used` enum('TRUE','FALSE') NOT NULL default 'FALSE',
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;