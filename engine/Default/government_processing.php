<?php

if ($_REQUEST['action'] == 'Become a deputy') {
	$player->setAlignment(150);
	$player->update();
}
elseif ($_REQUEST['action'] == 'Become a gang member') {
	$player->setAlignment(-150);
	$player->update();
}

forward(create_container('skeleton.php', 'current_sector.php'));

?>