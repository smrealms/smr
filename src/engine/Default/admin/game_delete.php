<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Deleting A Game');

$container = Page::create('admin/game_delete_confirm.php');
$template->assign('ConfirmHREF', $container->href());

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id, game_name FROM game ORDER BY game_id DESC');
$games = [];
foreach ($dbResult->records() as $dbRecord) {
	$name = $dbRecord->getString('game_name');
	$game_id = $dbRecord->getInt('game_id');
	$games[] = [
		'game_id' => $game_id,
		'display' => '(' . $game_id . ') ' . $name,
	];
}
$template->assign('Games', $games);
