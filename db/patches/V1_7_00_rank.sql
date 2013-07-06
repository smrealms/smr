INSERT INTO permission (permission_name,link_to) VALUES ('Rank Management','ranking_manage.php');

CREATE TABLE IF NOT EXISTS ranking(
  ranking_id int(8) not null auto_increment,
  label varchar(64) not null,
  experience int(10) not null,
  kills int(10) not null,
  operation int(10) not null,
  utility int(4) not null default 0,
  position int(4) not null default 0,
  created_by int(10) not null,
  created_at int(10) not null,
  updated_by int(10) null,
  updated_at int(10) null,

  PRIMARY KEY(ranking_id)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS player_operation(
  player_operation_id int(10) not null auto_increment,
  player_id int(10) not null,
  sector_id int(10) not null,
  game_id int(10) not null,
  type varchar(24) not null,
  score int(4) not null,
  created_at date not null,

  PRIMARY KEY(player_operation_id),

  INDEX(player_id)

) ENGINE=InnoDB;