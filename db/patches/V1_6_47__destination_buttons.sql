CREATE TABLE IF NOT EXISTS player_stored_sector(
  player_stored_sector_id int(10) not null auto_increment,
  player_id int(10) not null,
  sector_id int(10) not null,
  label varchar(64) not null,
  offset_top int(4) not null default 0,
  offset_left int(4) not null default 0,

  PRIMARY KEY(player_stored_sector_id),
  #CONSTRAINT FOREIGN KEY (player_id) REFERENCES player (player_id) ON DELETE CASCADE ON UPDATE CASCADE,
  #CONSTRAINT FOREIGN KEY (sector_id) REFERENCES sector (sector_id) ON DELETE CASCADE ON UPDATE CASCADE,

  INDEX(player_id)
) ENGINE=InnoDB;