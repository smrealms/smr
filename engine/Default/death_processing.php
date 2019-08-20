<?php declare(strict_types=1);
$player->setDead(false);
$player->deletePlottedCourse();

$account->log(LOG_TYPE_TRADER_COMBAT, 'Player sees death screen', $player->getSectorID());
forward(create_container('skeleton.php', 'death.php'));
