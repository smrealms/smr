<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

//first check if there is a bar here
if (!$sector->has_bar()) create_error('So two guys walk into this bar...');


//get script to include
if (isset($var['script'])) $script = $var['script'];
else $script = 'bar_opening.php';
//if ($script == 'bar_gambling_bet.php') create_error('Blackjack is currently outlawed, you will have to come back later.');
//get bar name
$db->query('SELECT location_name FROM location_type NATURAL JOIN location WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' AND location_type.location_type_id > 800 AND location_type.location_type_id < 900');

//next welcome them
if ($db->nextRecord()) $template->assign('PageTopic','Welcome to ' . $db->getField('location_name') . '.');
//in case for some reason there isn't a bar name found...should never happen but who knows
else $template->assign('PageTopic','Welcome to this bar');

//include menu (not menue ;) )
require_once(get_file_loc('menue.inc'));
global $BAR_SCRIPTS_USED; // HACKY
if(!is_array($BAR_SCRIPTS_USED)||!in_array($script,$BAR_SCRIPTS_USED))
{
	create_bar_menue();
	$BAR_SCRIPTS_USED[] = $script;
}
//get rid of drinks older than 30 mins
$time = TIME - 1800;
$db->query('DELETE FROM player_has_drinks WHERE time < '.$time);

//include bar part
require_once(get_file_loc($script));

switch($script)
{
	case 'bar_opening.php':
		$template->assign('IncludeScript',$script);
	break;
}

?>