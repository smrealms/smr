<?php

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menu.inc'));

if (!isset($var['race_id']))
	SmrSession::updateVar('race_id',$player->getRaceID());
$race_id = $var['race_id'];

$template->assign('PageTopic','Ruling Council Of ' . Globals::getRaceName($race_id));


create_council_menu($race_id);

// check for relations here
modifyRelations($race_id);

checkPacts($race_id);

$PHP_OUTPUT.=('<div align="center" class="bold">President</div>');

$president =& Council::getPresident($player->getGameID(),$race_id);
if (is_object($president)) {

	$PHP_OUTPUT.=('<p><table class="standard" align="center" width="75%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Name</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('<tr>');

	$PHP_OUTPUT.=('<td valign="top">President ');
	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'trader_search_result.php';
	$container['player_id']	= $president->getPlayerID();
	$PHP_OUTPUT.=create_link($container, $president->getDisplayName());
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td align="center">');
	$container = create_container('skeleton.php','council_send_message.php');
	$container['race_id'] = $president->getRaceID();
	$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($president->getRaceID()));
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td>');
	if ($president->hasAlliance()) {
		$PHP_OUTPUT.=create_link($president->getAllianceRosterHREF(), $president->getAllianceName());
	} else
		$PHP_OUTPUT.=('(none)');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td align="right">'.$president->getExperience().'</td>');

	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table></p>');

} else
	$PHP_OUTPUT.=('<div align="center">This council doesn\'t have a president!</div>');

$PHP_OUTPUT.=('<br /><br /><div align="center" class="bold">Member</div>');

$councilMembers = Council::getRaceCouncil($player->getGameID(), $race_id);
if(count($councilMembers) > 0) {
	$PHP_OUTPUT.=('<p><table class="standard" align="center" width="85%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('<th>Name</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('</tr>');

	foreach($councilMembers as $count => $accountID) {
		$councilPlayer =& SmrPlayer::getPlayer($accountID, $player->getGameID());

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center"');
		if ($councilPlayer->getAccountID() == $player->getAccountID()) {
			$PHP_OUTPUT.=(' class="bold"');
		}
		$PHP_OUTPUT.=('>'.$count.'.</td>');

		$PHP_OUTPUT.=('<td valign="middle"');
		if ($councilPlayer->getAccountID() == $player->getAccountID()) {
			$PHP_OUTPUT.=(' class="bold"');
		}
		$PHP_OUTPUT.=('>'.$councilPlayer->getLevelName().' ');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $councilPlayer->getPlayerID();
		$PHP_OUTPUT.=create_link($container, $councilPlayer->getDisplayName());
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="center"');
		if ($councilPlayer->getAccountID() == $player->getAccountID()) {
			$PHP_OUTPUT.=(' class="bold"');
		}
		$PHP_OUTPUT.=('>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_send_message.php';
		$container['race_id'] = $councilPlayer->getRaceID();
		$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($councilPlayer->getRaceID()));
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td');
		if ($councilPlayer->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>');
		if ($councilPlayer->hasAlliance()) {
			$PHP_OUTPUT.=create_link($councilPlayer->getAllianceRosterHREF(), $councilPlayer->getAllianceName());
		}
		else {
			$PHP_OUTPUT.=('(none)');
		}
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="right"');
		if ($councilPlayer->getAccountID() == $player->getAccountID()) {
			$PHP_OUTPUT.=(' class="bold"');
		}
		$PHP_OUTPUT.=('>'.$councilPlayer->getExperience().'</td>');

		$PHP_OUTPUT.=('</tr>');
	}
	$PHP_OUTPUT.=('</table></p>');
}
else {
	$PHP_OUTPUT.=('<div align="center">This council doesn\'t have any members!</div>');
}
$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<b>View Council</b><br />');
$races =& Globals::getRaces();
foreach($races as $raceID => $raceInfo) {
	if($raceID == RACE_NEUTRAL)
		continue;

	$container = create_container('skeleton.php','council_list.php');
	$container['race_id']	= $raceID;

	$PHP_OUTPUT.=create_link($container, '<span style="font-size:75%;">'.$raceInfo['Race Name'].'</span>');
	$PHP_OUTPUT.=('<br />');
}

?>