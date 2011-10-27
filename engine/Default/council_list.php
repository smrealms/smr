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
if (is_object($president))
{

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
	if ($president->hasAlliance())
	{
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

$db->query('SELECT account_id FROM player ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'race_id = '.$race_id.' ' .
		   'ORDER by experience DESC ' .
		   'LIMIT ' . MAX_COUNCIL_MEMBERS);
		   
if ($db->getNumRows() > 0)
{

	$PHP_OUTPUT.=('<p><table class="standard" align="center" width="85%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('<th>Name</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('</tr>');

	$count = 0;
	while ($db->nextRecord()) {

		$council =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());
		$count++;

		$PHP_OUTPUT.=('<tr>');

		$PHP_OUTPUT.=('<td align="center"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>'.$count.'.</td>');

		$PHP_OUTPUT.=('<td valign="middle"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>'.$council->getLevelName().' ');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $council->getPlayerID();
		$PHP_OUTPUT.=create_link($container, $council->getDisplayName());
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="center"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_send_message.php';
		$container['race_id'] = $council->getRaceID();
		$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($council->getRaceID()));
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>');
		if ($council->hasAlliance())
		{
			$PHP_OUTPUT.=create_link($council->getAllianceRosterHREF(), $council->getAllianceName());
		} else
			$PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="right"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>'.$council->getExperience().'</td>');

		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table></p>');


} else
	$PHP_OUTPUT.=('<div align="center">This council doesn\'t have any members!</div>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<b>View Council</b><br />');
$races =& Globals::getRaces();
foreach($races as $raceID => $raceInfo)
{
	if($raceID == RACE_NEUTRAL)
		continue;

	$container = create_container('skeleton.php','council_list.php');
	$container['race_id']	= $raceID;

	$PHP_OUTPUT.=create_link($container, '<span style="font-size:75%;">'.$raceInfo['Race Name'].'</span>');
	$PHP_OUTPUT.=('<br />');

}

?>