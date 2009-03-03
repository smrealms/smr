<?php

$template->assign('PageTopic','BOUNTIES');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_trader_menue();

$PHP_OUTPUT.= 'Bounties awaiting collection.<br /><br />';

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><th>Federal</th><th>Underground</th></tr>';

$bounties['HQ'] = array();
$bounties['UG'] = array();
$ids=array();

$db->query('SELECT amount,account_id,type FROM bounty WHERE claimer_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id);

while($db->nextRecord()) {
	$bounties[$db->getField('type')][] = array($db->getField('account_id'),$db->getField('amount'));
	$ids[] = $db->getField('account_id');
}

if(count($ids)) {
	$db->query('SELECT account_id,player_name,player_id,alignment FROM player WHERE account_id IN (' . implode(',',$ids) . ') AND game_id=' . SmrSession::$game_id . ' LIMIT ' . count($ids));

	while($db->nextRecord()) {
		$players[$db->getField('account_id')] = get_colored_text($db->getField('alignment'),stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')');
	}
}

$PHP_OUTPUT.= '<tr><td style="width:50%" class="top">';

if(count($bounties['HQ']) > 0) {
	foreach($bounties['HQ'] as $bounty) {
		$PHP_OUTPUT.= $players[$bounty[0]];
		$PHP_OUTPUT.= ' : <span class="yellow">';
		$PHP_OUTPUT.= number_format($bounty[1]);
		$PHP_OUTPUT.= '</span>';
		$PHP_OUTPUT.= '<br />';
	}
}
else {
	$PHP_OUTPUT.= 'None';
}

$PHP_OUTPUT.= '</td><td style="width:50%" class="top">';

if(count($bounties['UG']) > 0) {
	foreach($bounties['UG'] as $bounty) {
		$PHP_OUTPUT.= $players[$bounty[0]];
		$PHP_OUTPUT.= ' : <span class="yellow">';
		$PHP_OUTPUT.= number_format($bounty[1]);
		$PHP_OUTPUT.= '</span>';
		$PHP_OUTPUT.= '<br />';
	}
}
else {
	$PHP_OUTPUT.= 'None';
}

$PHP_OUTPUT.= '</td></tr></table>';
?>