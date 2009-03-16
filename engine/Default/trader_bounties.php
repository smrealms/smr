<?php

$template->assign('PageTopic','BOUNTIES');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_trader_menue();

$PHP_OUTPUT.= 'Bounties awaiting collection.<br /><br />';

$PHP_OUTPUT.= '<table class="standard fullwidth"><tr><th>Federal</th><th>Underground</th></tr><tr>';

$db->query('SELECT * FROM bounty WHERE claimer_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() .' AND type=\'HQ\'');
doBountyList(&$PHP_OUTPUT,&$db);
$db->query('SELECT * FROM bounty WHERE claimer_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() .' AND type=\'UG\'');
doBountyList(&$PHP_OUTPUT,&$db);
$PHP_OUTPUT.= '</tr></table>';

function doBountyList(&$PHP_OUTPUT,&$db)
{
	$PHP_OUTPUT.='<td style="width:50%" class="top">';
	$any=false;
	while($db->nextRecord())
	{
		$any=true;
		$bountyPlayer =& SmrPlayer::getPlayer($player->getGameID());
		$PHP_OUTPUT.= $bountyPlayer->getDisplayName()
						.' : <span class="yellow">'.number_format($db->getField('amount')).'</span> credits and'
						. '<span class="yellow">'.number_format($db->getField('smr_credits')). '</span> SMR credits<br />';
	}
	if(!$any)
		$PHP_OUTPUT.='None';
	$PHP_OUTPUT.='</td>';
}
?>