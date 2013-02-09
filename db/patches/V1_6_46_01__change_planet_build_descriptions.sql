ALTER TABLE planet_construction 
CHANGE construction_description construction_description VARCHAR( 100 );
UPDATE planet_construction
SET construction_description = 'Increases planet&rsquo;s maximum shield capacity by 100 shields' WHERE construction_id = 1;
UPDATE planet_construction
SET construction_description = 'Increases planet&rsquo;s maximum drone capacity by 20 drones' WHERE construction_id = 2;
UPDATE planet_construction
SET construction_description = 'Builds a turret capable of dealing 250 damage to enemy ships when fired on' WHERE construction_id = 3;