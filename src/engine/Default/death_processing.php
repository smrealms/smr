<?php declare(strict_types=1);
$player->setDead(false);
$player->deletePlottedCourse();

$player->log(LOG_TYPE_TRADER_COMBAT, 'Player sees death screen');
Page::create('skeleton.php', 'death.php')->go();
