<?
$gameInfo =& Globals::getGameInfo($player->getGameID());
if($gameInfo['StartDate']+86400*3 > TIME)
	create_error('You cannot buy maps within the first 3 days');

if ($account->getTotalSmrCredits() < 2)
	create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');

//gal map buy
if (isset($var['process']))
{
	$gal_id = $_REQUEST['gal_id'];
	if ($gal_id == 0)
		create_error('You must select a galaxy to buy the map of!');

	$player->increaseHOF(1,array('Bar','Maps Bought'));
	//take money
	$account->decreaseTotalSmrCredits(2);
	//now give maps
	$account_id = $player->getAccountID();
	$game_id = $player->getGameID();
	//get start sector
	$galaxy =& SmrGalaxy::getGalaxy($player->getGameID(),$gal_id);
	$low = $galaxy->getStartSector();
	//get end sector
	$high = $galaxy->getEndSector();

	// Have they already got this map? (Are there any unexplored sectors?
	$db->query('SELECT * FROM player_visited_sector WHERE sector_id >= '.$low.' AND sector_id <= '.$high.' AND account_id = '.$account_id.' AND game_id = '.$game_id.' LIMIT 1');
	if(!$db->nextRecord())
		create_error('You already have maps of this galaxy!');
	
	// delete all entries from the player_visited_sector/port table
	$db->query('DELETE FROM player_visited_sector WHERE sector_id >= '.$low.' AND sector_id <= '.$high.' AND account_id = '.$account_id.' AND game_id = '.$game_id);
	//start section
	
	require_once(get_file_loc('SmrPort.class.inc'));
	// add port infos
	$db->query('SELECT sector_id FROM port WHERE game_id = '.$game_id.' AND sector_id <= '.$high.' AND sector_id >= '.$low.' ORDER BY sector_id');
	while ($db->nextRecord())
	{
		SmrPort::getPort($game_id,$db->getField('sector_id'))->addCachePort($account_id);
	}

	//offer another drink and such
	$PHP_OUTPUT.=('<div align=center>Galaxy Info has been added.  Enjoy!</div><br />');
	include(get_file_loc('bar_opening.php'));
}
else
{
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
	$gameGalaxies =& SmrGalaxy::getGameGalaxies($player->getGameID());
	foreach ($gameGalaxies as &$galaxy)
	{	
		$PHP_OUTPUT.=('<option value='.$galaxy->getGalaxyID().'>' . $galaxy->getName() . '</option>');
	} unset($galaxy);
	$PHP_OUTPUT.=('</select><br />');
	$PHP_OUTPUT.=create_submit('Buy the map');
	$PHP_OUTPUT.=('</form></div>');
}

?>