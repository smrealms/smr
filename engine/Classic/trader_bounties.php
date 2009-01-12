<?php

print_topic("BOUNTIES");

include(get_file_loc('menue.inc'));
print_trader_menue();

echo 'Bounties awaiting collection.<br><br>';

echo '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><th>Federal</th><th>Underground</th></tr>';

$bounties['HQ'] = array();
$bounties['UG'] = array();
$ids=array();

$db->query('SELECT amount,account_id,type FROM bounty WHERE claimer_id=' . $session->account_id . ' AND game_id=' . $session->game_id);

while($db->next_record()) {
	$bounties[$db->f('type')][] = array($db->f('account_id'),$db->f('amount'));
	$ids[] = $db->f('account_id');
}

if(count($ids)) {
	$db->query('SELECT account_id,player_name,player_id,alignment FROM player WHERE account_id IN (' . implode(',',$ids) . ') AND game_id=' . $session->game_id . ' LIMIT ' . count($ids));

	while($db->next_record()) {
		$players[$db->f('account_id')] = get_colored_text($db->f('alignment'),stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')');
	}
}

echo '<tr><td style="width:50%" class="top">';

if(count($bounties['HQ']) > 0) {
	foreach($bounties['HQ'] as $bounty) {
		echo $players[$bounty[0]];
		echo ' : <span class="yellow">';
		echo number_format($bounty[1]);
		echo '</span>';
		echo '<br>';
	}
}
else {
	echo 'None';
}

echo '</td><td style="width:50%" class="top">';

if(count($bounties['UG']) > 0) {
	foreach($bounties['UG'] as $bounty) {
		echo $players[$bounty[0]];
		echo ' : <span class="yellow">';
		echo number_format($bounty[1]);
		echo '</span>';
		echo '<br>';
	}
}
else {
	echo 'None';
}

echo '</td></tr></table>';
?>