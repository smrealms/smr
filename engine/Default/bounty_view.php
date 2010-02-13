<?php

$bounties = 0;
$id = $var['id'];
$curr_player =& SmrPlayer::getPlayer($id, $player->getGameID());
$template->assign('PageTopic','Viewing '.$curr_player->getPlayerName());

if($curr_player->hasBounties())
{
	$bounties = $curr_player->getBounties();
	$hasBounty = false;
	foreach($bounties as $bounty)
	{
		 if($bounty['Type'] == 'HQ')
		 {
		 	$PHP_OUTPUT.=('The <span class="green">Federal Government</span> is offering a bounty on '.$curr_player->getPlayerName().' worth <span class="creds">'.$bounty['Amount'].'</span> credits and <span class="yellow">'.$bounty['SmrCredits'].'</span> SMR credits.<br />');
		 	if ($bounty['Claimer'] != 0)
		 	{
			 	$claiming_player =& SmrPlayer::getPlayer($bounty['Claimer'], $player->getGameID());
			 	$PHP_OUTPUT.=('This bounty can be claimed by '.$claiming_player->getPlayerName().'<br />');
			 	$hasBounty = true;
		 	}
		 }
	}
	if($hasBounty)
		$PHP_OUTPUT.=('<br /><br /><br />');
	foreach($bounties as $bounty)
	{
		 if($bounty['Type'] == 'UG')
		 {
			$PHP_OUTPUT.=('The <span class="red">Underground</span> is offering a bounty on '.$curr_player->getPlayerName().' worth <span class="creds">'.$bounty['Amount'].'</span> credits and <span class="yellow">'.$bounty['SmrCredits'].'</span> SMR credits.<br />');
		 	if ($bounty['Claimer'] != 0)
		 	{
			 	$claiming_player =& SmrPlayer::getPlayer($bounty['Claimer'], $player->getGameID());
			 	$PHP_OUTPUT.=('This bounty can be claimed by '.$claiming_player->getPlayerName().'<br />');
		 	}
		 }
	}
}
else
	$PHP_OUTPUT.=('This player has no bounties<br />');
?>