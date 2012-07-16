<?php

if($account->isVeteran()) {
	create_error('You cannot join a newbie game, shooo!');
}

require_once(ENGINE . 'Default/game_join.php');

?>