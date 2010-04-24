<?php

print_topic('LIST OF ALLIANCES');

echo '<div align="center">';

if ($player->alliance_id == 0) {
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_create.php';
	print_button($container,'Create your own alliance!');
	echo '<br><br>';
}


$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_list.php';

// get list of alliances
$db->query('SELECT 
count(player_name) as alliance_member_count,
sum(player_cache.experience) as alliance_xp,
floor(avg(player_cache.experience)) as alliance_avg,
alliance.alliance_name as alliance_name,
player.alliance_id as alliance_id 
FROM player, player_cache, alliance 
WHERE player.alliance_id = alliance.alliance_id 
AND alliance.leader_id > 0
AND player.game_id = ' . SmrSession::$game_id . '
AND alliance.game_id = ' . SmrSession::$game_id . '
AND player_cache.game_id = ' . SmrSession::$game_id . '
AND player_cache.account_id = player.account_id
GROUP BY alliance.alliance_id 
ORDER BY ' . $var['order'] . ' ' . $var['sequence']
);

if ($var['sequence'] == 'DESC')
	$container['sequence'] = '';
else
	$container['sequence'] = 'DESC';

// do we have any alliances?
if ($db->nf() > 0) {
	echo '<table cellspacing="0" cellpadding="0" class="standard inset"><tr><th>';
	$container['order'] = 'alliance_name';
	print_header_link($container,'Alliance Name');
	echo '</th><th class="shrink">';
	$container['order'] = 'alliance_xp';
	print_header_link($container,'Total Experience');
	echo '</th><th class="shrink">';
	$container['order'] = 'alliance_avg';
	print_header_link($container, 'Average Experience');
	echo '</th><th class="shrink">';
	$container['order'] = 'alliance_member_count';
	print_header_link($container, 'Members');
	echo '</th>';
	echo '</tr>';


	while ($db->next_record()) {
		if ($db->f('alliance_id') != $player->alliance_id)
			$container['body'] = 'alliance_roster.php';
		else
			$container['body'] = 'alliance_mod.php';
		$container['alliance_id'] = $db->f('alliance_id');

		echo '<tr><td>';
		print_link($container, stripslashes($db->f('alliance_name')));
		echo '</td>';
		echo '<td class="right">' .  $db->f('alliance_xp') . '</td>';
		echo '<td class="right">' . $db->f('alliance_avg') . '</td>';
		echo '<td class="right">' . $db->f('alliance_member_count') . '</td></tr>';

	}
	echo '</table><br>Click column table to reorder!';

}

echo '</div>';
?>