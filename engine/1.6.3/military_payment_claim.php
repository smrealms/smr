<?php
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID());

$template->assign('PageTopic','Military Payment Center');

include(get_file_loc('menue.inc'));
if ($sector->has_hq())
	$PHP_OUTPUT.=create_hq_menue();
else
	$PHP_OUTPUT.=create_ug_menue();

if ($player->hasMilitaryPayment())
{
	$PHP_OUTPUT.=('For your military help you have been paid <font color=yellow>'.number_format($player->getMilitaryPayment()).'</font> credits');

	$player->increaseHOF($player->getMilitaryPayment(),array('Military Payment','Money','Claimed'));

	// add to our cash
	$player->increaseCredits($player->getMilitaryPayment());
	$player->setMilitaryPayment(0);
	$player->update();

} else
	$PHP_OUTPUT.=('You have done nothing worthy of military payment');

?>