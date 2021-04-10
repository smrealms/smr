<?php declare(strict_types=1);

$template->assign('PageTopic', 'Deleting A Game');

$container = Page::create('skeleton.php', 'game_delete_confirm.php');
$template->assign('ConfirmHREF', $container->href());

$db = Smr\Database::getInstance();
$db->query('SELECT game_id, game_name FROM game ORDER BY game_id DESC');
$games = [];
while ($db->nextRecord()) {
	$name = $db->getField('game_name');
	$game_id = $db->getInt('game_id');
	$games[] = [
		'game_id' => $game_id,
		'display' => '(' . $game_id . ') ' . $name,
	];
}
$template->assign('Games', $games);
