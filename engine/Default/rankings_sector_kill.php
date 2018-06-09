<?php
require_once(get_file_loc('SmrSector.class.inc'));
$template->assign('PageTopic','Sector Death Rankings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(3,0);

$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT 10');

$rank = 1;
$topTen = [];
while ($db->nextRecord()) {
	// get current player
	$curr_sector = SmrSector::getSector($player->getGameID(), $db->getField('sector_id'));
	$topTen[$rank++] = $curr_sector;
}
$template->assign('TopTen', $topTen);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Show' && is_numeric($_REQUEST['min_rank']) && is_numeric($_REQUEST['max_rank'])) {
	$min_rank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	$max_rank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
}
else {
	$min_rank = 1;
	$max_rank = 10;
}

if ($min_rank < 0) {
	$min_rank = 1;
	$max_rank = 10;
}

$db->query('SELECT max(sector_id) FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord())
	$total_sector = $db->getField('max(sector_id)');

if ($max_rank > $total_sector)
	$max_rank = $total_sector;

$template->assign('MinRank', $min_rank);
$template->assign('MaxRank', $max_rank);

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'rankings_sector_kill.php';
$container['min_rank']	= $min_rank;
$container['max_rank']	= $max_rank;
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY battles DESC, sector_id LIMIT ' . ($min_rank - 1) . ', ' . ($max_rank - $min_rank + 1));

$rank = $min_rank;
$topCustom = [];
while ($db->nextRecord()) {
	// get current player
	$curr_sector = SmrSector::getSector($player->getGameID(), $db->getField('sector_id'));
	$topCustom[$rank++] = $curr_sector;
}
$template->assign('TopCustom', $topCustom);
