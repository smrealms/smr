<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$num_creds = $account->get_credits();
if ($num_creds < 2) {
	
	$PHP_OUTPUT.=create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');
	return;
	
}

//gal map buy
if (isset($var['process'])) {
	
	$gal_id = $_REQUEST['gal_id'];
	if ($gal_id == 0) {
		
		create_error('You must select a galaxy to buy the map of!');
		return;
		
	}
	//take money
	$account->set_credits($num_creds - 2);
	//now give maps
	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	//get start sector
	$db->query('SELECT * FROM sector WHERE galaxy_id = '.$gal_id.' AND game_id = '.$player->getGameID().' ORDER BY sector_id LIMIT 1');
	$db->next_record();
	$low = $db->f('sector_id');
	//get end sector
	$db->query('SELECT * FROM sector WHERE galaxy_id = '.$gal_id.' AND game_id = '.$player->getGameID().' ORDER BY sector_id DESC LIMIT 1');
	$db->next_record();
	$high = $db->f('sector_id');

	// Have they already got this map? (Are there any unexplored sectors?
	$db->query('SELECT * FROM player_visited_sector WHERE sector_id >= '.$low.' AND sector_id <= '.$high.' AND account_id = '.$account_id.' AND game_id = '.$game_id.' LIMIT 1');
	if(!$db->next_record()) {
		create_error('You already have maps of this galaxy!');
		return;
	}
	
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE sector_id >= '.$low.' AND sector_id <= '.$high.' AND account_id = '.$account_id.' AND game_id = '.$game_id);
	$db->query('DELETE FROM player_visited_port WHERE sector_id >= '.$low.' AND sector_id <= '.$high.' AND account_id = '.$account_id.' AND game_id = '.$game_id);
	//start section
	
	require_once(get_file_loc('SmrPort.class.inc'));
	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' AND sector_id <= '.$high.' AND sector_id >= '.$low.' ORDER BY sector_id');
	while ($db->next_record())
	{
		SmrPort::getPort($game_id,$db->f('sector_id'))->addCachePort($account_id);
	}

	//offer another drink and such
	$PHP_OUTPUT.=('<div align=center>Galaxy Info has been added.  Enjoy!</div><br />');
	include(get_file_loc('bar_opening.php'));
	
} else {
	
	//find what gal they want
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bar_main.php';
	$container['script'] = 'bar_galmap_buy.php';
	$container['process'] = 'yes';
	$PHP_OUTPUT.=('<div align=center>What galaxy do you want info on?<br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<select type=select name=gal_id>');
	$PHP_OUTPUT.=('<option value=0>[Select a galaxy]</option>');
	$db->query('SELECT galaxy_id FROM sector WHERE game_id = '.$player->getGameID().' GROUP BY galaxy_id ORDER BY galaxy_id ASC');
	$db2 = new SMR_DB();
	while ($db->next_record()) {
		
		$gal_id = $db->f('galaxy_id');
		$db2->query('SELECT * FROM galaxy WHERE galaxy_id = '.$gal_id);
		if ($db2->next_record()) $PHP_OUTPUT.=('<option value='.$gal_id.'>' . $db2->f('galaxy_name') . '</option>');
		
	}
	$PHP_OUTPUT.=('</select><br />');
	$PHP_OUTPUT.=create_submit('Buy the map');
	$PHP_OUTPUT.=('</form></div>');
	
}

?>
