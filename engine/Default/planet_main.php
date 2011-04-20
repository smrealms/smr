<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
create_planet_menue();

//echo the dump cargo message or other message.
if (isset($var['errorMsg']))
	$template->assign('ErrorMsg',$var['errorMsg']);
if (isset($var['msg']))
	$template->assign('Msg',$var['msg']);

$template->assignByRef('ThisPlanet',$planet);

doTickerAssigns($template, $player, $db);

$db->query('SELECT * FROM player WHERE sector_id = '.$player->getSectorID().' AND ' .
									'game_id = '.SmrSession::$game_id.' AND ' .
									'account_id != '.SmrSession::$account_id.' AND ' .
									'land_on_planet = \'TRUE\' ' .
								'ORDER BY last_cpl_action DESC');
if($db->getNumRows() > 0 )
{
	$planetPlayers = array();
	while ($db->nextRecord())
	{
		$planetPlayerAccountID = $db->getField('account_id');
		$planetPlayers[$planetPlayerAccountID]['Player'] =& SmrPlayer::getPlayer($planetPlayerAccountID, SmrSession::$game_id);
	
		if ($planet->getOwnerID() == $player->getAccountID())
		{
			$container = array();
			$container['url']			= 'planet_kick_processing.php';
			$container['account_id']	= $planetPlayerAccountID;
			$planetPlayers[$planetPlayerAccountID]['KickFormLink'] = SmrSession::get_new_href($container);
		}
	
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $planetPlayers[$planetPlayerAccountID]['Player']->getPlayerID();
		$planetPlayers[$planetPlayerAccountID]['SearchLink'] = SmrSession::get_new_href($container);
	}
	$template->assignByRef('PlanetPlayers',$planetPlayers);
}

$template->assign('LaunchFormLink',SmrSession::get_new_href(create_container('planet_launch_processing.php', '')));
?>