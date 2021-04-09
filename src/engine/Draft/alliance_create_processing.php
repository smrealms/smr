<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if ($player->isDraftLeader()) {
	require_once(ENGINE . 'Default/alliance_create_processing.php');
} else {
	create_error('You cannot create an alliance in a draft game.');
}
