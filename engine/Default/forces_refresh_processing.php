<?php

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

$forces->updateExpire();

forward(create_container('skeleton.php', 'current_sector.php'));
