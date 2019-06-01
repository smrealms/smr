<?php

$template->assign('PageTopic', 'Military Payment Center');

require_once(get_file_loc('menu_hq.inc'));
if ($sector->hasHQ())
	create_hq_menu();
else
	create_ug_menu();

// We can only claim the payment once, so to prevent clobbering the message
// upon AJAX refresh, we store it as a session variable when we first get it.
if (!isset($var['ClaimText'])) {
	if ($player->hasMilitaryPayment()) {
		$payment = $player->getMilitaryPayment();
		$player->increaseHOF($payment, array('Military Payment', 'Money', 'Claimed'), HOF_PUBLIC);

		// add to our cash
		$player->increaseCredits($payment);
		$player->setMilitaryPayment(0);
		$player->update();

		$claimText = ('For your military activity you have been paid <span class="creds">' . number_format($payment) . '</span> credits.');
	} else {
		$claimText = ('You have done nothing worthy of military payment.');
	}

	SmrSession::updateVar('ClaimText', $claimText);
}

$template->assign('ClaimText', $var['ClaimText']);
