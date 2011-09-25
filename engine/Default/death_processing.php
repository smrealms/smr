<?php
$player->setDead(false);
$player->deletePlottedCourse();

$account->log(8, 'Player sees death screen', $player->getSectorID());
forward(create_container('skeleton.php','death.php'));
?>