<?php

$template->assign('PageTopic','Deleting A Game');

$container = create_container('skeleton.php', 'game_delete_confirm.php');
$template->assign('ConfirmHREF', SmrSession::getNewHREF($container));

$db->query('SELECT game_id, game_name FROM game ORDER BY game_id DESC');
$games = [];
while ($db->nextRecord()) {
	$name = $db->getField('game_name');
	$game_id = $db->getInt('game_id');
	$games[] = [
		'game_id' => $game_id,
		'display' => '('.$game_id.') '.$name,
	];
}
$template->assign('Games', $games);
