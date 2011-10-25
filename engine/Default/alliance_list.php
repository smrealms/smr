<?php

$template->assign('PageTopic','List Of Alliances');

$PHP_OUTPUT.= '<div align="center">';

if (!$player->hasAlliance()) {
	$container = create_container('skeleton.php','alliance_create.php');
	$PHP_OUTPUT.=create_button($container,'Create your own alliance!');
	$PHP_OUTPUT.= '<br /><br />';
}


$container = create_container('skeleton.php','alliance_list.php');

$varSequence = isset($var['sequence']) ? $var['sequence'] : 'ASC';

// get list of alliances
//$db->query('SELECT 
//count(player_name) as alliance_member_count,
//sum(player_cache.experience) as alliance_xp,
//floor(avg(player_cache.experience)) as alliance_avg,
//alliance.alliance_name as alliance_name,
//player.alliance_id as alliance_id 
//FROM player, player_cache, alliance 
//WHERE player.alliance_id = alliance.alliance_id 
//AND alliance.leader_id > 0
//AND player.game_id = ' . $player->getGameID() . '
//AND alliance.game_id = ' . $player->getGameID()
// . 'AND player_cache.game_id = ' . $player->getGameID()
// . 'AND player_cache.account_id = player.account_id
//GROUP BY alliance.alliance_id 
//ORDER BY ' . $var['order'] . ' ' . $varSequence
//);
$db->query('SELECT 
count(account_id) as alliance_member_count,
sum(experience) as alliance_xp,
floor(avg(experience)) as alliance_avg,
alliance_name,
alliance_id 
FROM player
JOIN alliance USING (game_id, alliance_id)
WHERE leader_id > 0
AND game_id = ' . $player->getGameID() . '
GROUP BY alliance_id 
ORDER BY ' . $var['order'] . ' ' . $varSequence
);
//echo 'SELECT 
//count(player_name) as alliance_member_count,
//sum(player.experience) as alliance_xp,
//floor(avg(player.experience)) as alliance_avg,
//alliance.alliance_name as alliance_name,
//player.alliance_id as alliance_id 
//FROM player, alliance 
//WHERE player.alliance_id = alliance.alliance_id 
//AND alliance.leader_id > 0
//AND player.game_id = ' . $player->getGameID() . '
//AND alliance.game_id = ' . $player->getGameID() .'
//GROUP BY alliance.alliance_id 
//ORDER BY ' . $var['order'] . ' ' . $varSequence;

if ($varSequence == 'DESC')
	$container['sequence'] = 'ASC';
else
	$container['sequence'] = 'DESC';

// do we have any alliances?
if ($db->getNumRows() > 0)
{
	$PHP_OUTPUT.= '<table class="standard inset"><tr><th>';
	$container['order'] = 'alliance_name';
	$PHP_OUTPUT.=create_header_link($container,'Alliance Name');
	$PHP_OUTPUT.= '</th><th class="shrink">';
	$container['order'] = 'alliance_xp';
	$PHP_OUTPUT.=create_header_link($container,'Total Experience');
	$PHP_OUTPUT.= '</th><th class="shrink">';
	$container['order'] = 'alliance_avg';
	$PHP_OUTPUT.=create_header_link($container, 'Average Experience');
	$PHP_OUTPUT.= '</th><th class="shrink">';
	$container['order'] = 'alliance_member_count';
	$PHP_OUTPUT.=create_header_link($container, 'Members');
	$PHP_OUTPUT.= '</th>';
	$PHP_OUTPUT.= '</tr>';


	while ($db->nextRecord()) {
		if ($db->getField('alliance_id') != $player->getAllianceID())
			$container['body'] = 'alliance_roster.php';
		else
			$container['body'] = 'alliance_mod.php';
		$container['alliance_id'] = $db->getField('alliance_id');

		$PHP_OUTPUT.= '<tr><td>';
		$PHP_OUTPUT.=create_link($container, $db->getField('alliance_name'));
		$PHP_OUTPUT.= '</td>';
		$PHP_OUTPUT.= '<td class="right">' .  $db->getField('alliance_xp') . '</td>';
		$PHP_OUTPUT.= '<td class="right">' . $db->getField('alliance_avg') . '</td>';
		$PHP_OUTPUT.= '<td class="right">' . $db->getField('alliance_member_count') . '</td></tr>';

	}
	$PHP_OUTPUT.= '</table><br />Click column table to reorder!';

}
else
	$PHP_OUTPUT.= 'Currently there are no alliances.';

$PHP_OUTPUT.= '</div>';
?>