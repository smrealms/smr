<?php

//first check if there is a bar here
if (!$sector->hasBar()) {
	create_error('So two guys walk into this bar...');
}

//get script to include
if (!isset($var['script'])) {
	SmrSession::updateVar('script', 'bar_opening.php');
}
$script = $var['script'];
//if ($script == 'bar_gambling_bet.php') {
//	create_error('Blackjack is currently outlawed, you will have to come back later.');
//}
//get bar name
$db->query('SELECT location_name FROM location_type JOIN location USING(location_type_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND location_type_id > 800 AND location_type_id < 900');

//next welcome them
if ($db->nextRecord()) {
	$template->assign('PageTopic','Welcome to ' . $db->getField('location_name') . '.');
}
//in case for some reason there isn't a bar name found...should never happen but who knows
else {
	$template->assign('PageTopic','Welcome to this bar');
}

require_once(get_file_loc('menu.inc'));
global $BAR_SCRIPTS_USED; // HACKY
if(!is_array($BAR_SCRIPTS_USED)||!in_array($script,$BAR_SCRIPTS_USED)) {
	create_bar_menu();
	$BAR_SCRIPTS_USED[] = $script;
}
//get rid of drinks older than 30 mins
$db->query('DELETE FROM player_has_drinks WHERE time < ' . $db->escapeNumber(TIME - 1800));

//include bar part
require_once(get_file_loc($script));

$template->assign('IncludeScript',$script);
