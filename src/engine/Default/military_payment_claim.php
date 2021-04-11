<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Military Payment Center');

Menu::headquarters();

// We can only claim the payment once, so to prevent clobbering the message
// upon AJAX refresh, we store it as a session variable when we first get it.
if (!isset($var['ClaimText'])) {
	$player = $session->getPlayer();
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

	$session->updateVar('ClaimText', $claimText);
}

$template->assign('ClaimText', $var['ClaimText']);
