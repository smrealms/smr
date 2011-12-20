<?php
$sector =& $player->getSector();

$template->assign('PageTopic','Military Payment Center');

require_once(get_file_loc('menu.inc'));
if ($sector->hasHQ())
	create_hq_menu();
else
	create_ug_menu();

if ($player->hasMilitaryPayment()) {
	$PHP_OUTPUT.=('For your military help you have been paid <span class="creds">'.number_format($player->getMilitaryPayment()).'</span> credits');

	$player->increaseHOF($player->getMilitaryPayment(),array('Military Payment','Money','Claimed'), HOF_PUBLIC);

	// add to our cash
	$player->increaseCredits($player->getMilitaryPayment());
	$player->setMilitaryPayment(0);
	$player->update();

} else
	$PHP_OUTPUT.=('You have done nothing worthy of military payment');

?>