<?php

$template->assign('PageTopic','Bounties');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$PHP_OUTPUT.= 'Bounties awaiting collection.<br /><br />';

$PHP_OUTPUT.= '<table class="standard fullwidth"><tr><th>Federal</th><th>Underground</th></tr><tr>';

$db->query('SELECT * FROM bounty WHERE claimer_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND type=\'HQ\'');
doBountyList($PHP_OUTPUT,$db,$player);
$db->query('SELECT * FROM bounty WHERE claimer_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND type=\'UG\'');
doBountyList($PHP_OUTPUT,$db,$player);
$PHP_OUTPUT.= '</tr></table>';

function doBountyList(&$PHP_OUTPUT,&$db,&$player) {
	$PHP_OUTPUT.='<td style="width:50%" class="top">';
	$any=false;
	while($db->nextRecord()) {
		$any=true;
		$bountyPlayer =& SmrPlayer::getPlayer($db->getInt('account_id'),$player->getGameID());
		$PHP_OUTPUT.= $bountyPlayer->getLinkedDisplayName()
						.' : <span class="creds">'.number_format($db->getInt('amount')).'</span> credits and'
						. ' <span class="yellow">'.number_format($db->getInt('smr_credits')). '</span> SMR credits<br />';
	}
	if(!$any)
		$PHP_OUTPUT.='None';
	$PHP_OUTPUT.='</td>';
}
?>