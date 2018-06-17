<?php
require_once(get_file_loc('SmrForce.class.inc'));
$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

$forces->updateExpire();

forward(create_container('skeleton.php', 'current_sector.php'));
