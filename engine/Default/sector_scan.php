<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());
$template->assign('PageTopic','SECTOR SCAN');

// initialize vars
$friendly_forces = 0;
$enemy_forces = 0;
$friendly_vessel = 0;
$enemy_vessel = 0;

// get our rank
$rank_id = $account->get_rank();

// iterate over all forces in the target sector
require_once(get_file_loc('SmrForce.class.inc'));
$allForces =& SmrForce::getSectorForces($player->getGameID(), $var['target_sector']);
foreach ($allForces as $forces)
{

//	// we may skip forces if this is a protected gal.
//	if ($sector->is_protected_gal()) {
//
//		$forces_account =& SmrAccount::getAccount($forces->getOwnerID());
//
//		// if one is vet and the other is newbie we skip it
//		if (different_level($rank_id, $forces_account->get_rank(), $account->veteran, $forces_account->veteran))
//			continue;
//
//	}

	// decide if it's a friendly or enemy stack
	$forces_owner	=& $forces->getOwner();

	if ($player->getAllianceID() == 0 && $forces_owner->getAccountID() == $player->getAccountID() || $player->getAllianceID() != 0 && $player->getAllianceID() == $forces_owner->getAllianceID())
		$friendly_forces += $forces->getMines() * 3 + $forces->getCDs() * 2 + $forces->getSDs();
	else
		$enemy_forces += $forces->getMines() * 3 + $forces->getCDs() * 2 + $forces->getSDs();

}

$last_active = TIME - 259200;
$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND ' .
									  'sector_id = ' . $var['target_sector'] . ' AND ' .
									  'last_cpl_action > '.$last_active.' AND ' .
									  'land_on_planet = \'FALSE\' AND ' .
									  'account_id NOT IN (' . implode(',', $HIDDEN_PLAYERS) . ')');
while ($db->nextRecord())
{

//	// we may skip player if this is a protected gal.
//	if ($sector->is_protected_gal())
//	{
//
//		$curr_account =& SmrAccount::getAccount($db->getField('account_id'));
//
//		// if one is vet and the other is newbie we skip it
//		if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
//			continue;
//	}

	$curr_player	=& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());
	$curr_ship		=& $curr_player->getShip();

	// he's a friend if he's in our alliance (and we are not in a 0 alliance
	if ($player->traderMAPAlliance($curr_player))
		$friendly_vessel += $curr_ship->getAttackRating();
	else
		$enemy_vessel += $curr_ship->getDefenseRating() * 10;

}

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>&nbsp;</th>');
$PHP_OUTPUT.=('<th align="center">Scan Results</th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Friendly vessels</td>');
$PHP_OUTPUT.=('<td align="center">'.$friendly_vessel.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Enemy vessels</td>');
$PHP_OUTPUT.=('<td align="center">'.$enemy_vessel.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Friendly forces</td>');
$PHP_OUTPUT.=('<td align="center">'.$friendly_forces.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Enemy forces</td>');
$PHP_OUTPUT.=('<td align="center">'.$enemy_forces.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

$target_sector =& SmrSector::getSector(SmrSession::$game_id, $var['target_sector']);

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Planet</td>');
$PHP_OUTPUT.=('<td>');
if ($target_sector->hasPlanet()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Port</td>');
$PHP_OUTPUT.=('<td>');
if ($target_sector->hasPort()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Location</td>');
$PHP_OUTPUT.=('<td>');
if ($target_sector->hasLocation()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p><br />');

// is it a warp or a normal move?
if ($sector->getLinkWarp() == $var['target_sector'])
	$turns = 5;
else
	$turns = 1;

$container = array();
$container['url']			= 'sector_move_processing.php';
$container['target_page']	= 'current_sector.php';
transfer('target_sector');

$PHP_OUTPUT.= '<a href="'.$target_sector->getScanSectorHREF().'" class="submitStyle">Rescan ' . $target_sector->getSectorID() . '</a>&nbsp;';
$PHP_OUTPUT.= '<a href="'.$target_sector->getCurrentSectorHREF().'" class="submitStyle">Enter ' . $target_sector->getSectorID() . ' ('.$turns.')</a>';
$PHP_OUTPUT.=('</form></p>');

?>