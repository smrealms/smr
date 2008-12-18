<?

include(get_file_loc('council.inc'));
include(ENGINE . 'global/menue.inc');

$race_id = $var['race_id'];
if (empty($race_id))
	$race_id = $player->getRaceID();

$db->query('SELECT * FROM race ' .
		   'WHERE race_id = '.$race_id);
if ($db->next_record())
	$smarty->assign('PageTopic','RULING COUNCIL OF ' . $db->f('race_name'));

$president = getPresident($race_id);

$PHP_OUTPUT.=create_council_menue($race_id, $president);

// check for relations here
modifyRelations($race_id);

checkPacts($race_id);

$PHP_OUTPUT.=('<div align="center" style="font-weight:bold;">President</div>');

if ($president->getAccountID() > 0) {

	$PHP_OUTPUT.=('<p><table border="0" class="standard" cellspacing="0" align="center" width="75%">');
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
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_send_message.php';
	$container['race_id'] = $president->getRaceID();
	$PHP_OUTPUT.=create_link($container, $president->getColoredRaceName($president->getRaceID()));
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td>');
	if ($president->hasAlliance()) {

		$container = array();
		$container['url'] 			= 'skeleton.php';
		$container['body'] 			= 'alliance_roster.php';
		$container['alliance_id']	= $president->getAllianceID();
		$PHP_OUTPUT.=create_link($container, $president->getAllianceName());
	} else
		$PHP_OUTPUT.=('(none)');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td align="right">'.$president->getExperience().'</td>');

	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table></p>');

} else
	$PHP_OUTPUT.=('<div align="center">This council doesn\'t have a president!</div>');

$PHP_OUTPUT.=('<br><br><div align="center" style="font-weight:bold;">Member</div>');

$db->query('SELECT * FROM player ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'race_id = '.$race_id.' ' .
		   'ORDER by experience DESC ' .
		   'LIMIT 20');
		   
if ($db->nf() > 0) {
	
	$list = '(0';
	while ($db->next_record()) $list .= ',' . $db->f('account_id');
	$list .= ')';
	
}
$db->query('SELECT * FROM player WHERE account_id IN '.$list.' AND game_id = '.$player->getGameID().' ORDER BY experience DESC');

if ($db->nf() > 0)
{

	$PHP_OUTPUT.=('<p><table border="0" class="standard" cellspacing="0" align="center" width="85%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('<th>Name</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('</tr>');

	$count = 0;
	while ($db->next_record()) {

		$council =& SmrPlayer::getPlayer($db->f('account_id'), $player->getGameID());
		$count++;

		$PHP_OUTPUT.=('<tr>');

		$PHP_OUTPUT.=('<td align="center"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>'.$count.'.</td>');

		$PHP_OUTPUT.=('<td valign="middle"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>'.$council->getLevelName().' ');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $council->getPlayerID();
		$PHP_OUTPUT.=create_link($container, $council->getDisplayName());
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="center"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_send_message.php';
		$container['race_id'] = $council->getRaceID();
		$PHP_OUTPUT.=create_link($container, $council->getColoredRaceName($council->getRaceID()));
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>');
		if ($council->hasAlliance()) {

			$container = array();
			$container['url'] 			= 'skeleton.php';
			$container['body'] 			= 'alliance_roster.php';
			$container['alliance_id']	= $council->getAllianceID();
			$PHP_OUTPUT.=create_link($container, $council->getAllianceName());
		} else
			$PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('<td align="right"');
		if ($council->getAccountID() == $player->getAccountID())
			$PHP_OUTPUT.=(' style="font-weight:bold;"');
		$PHP_OUTPUT.=('>'.$council->getExperience().'</td>');

		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table></p>');


} else
	$PHP_OUTPUT.=('<div align="center">This council doesn\'t have any members!</div>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<b>View Council</b><br>');
$db->query('SELECT * FROM race WHERE race_id > 1');
while($db->next_record()) {

	$race_id	= $db->f('race_id');
	$race_name	= $db->f('race_name');

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'council_list.php';
	$container['race_id']	= $race_id;

	$PHP_OUTPUT.=create_link($container, '<span style="font-size:75%;">'.$race_name.'</span>');
	$PHP_OUTPUT.=('<br>');

}

?>