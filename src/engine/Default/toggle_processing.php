<?php declare(strict_types=1);

$session = Smr\Session::getInstance();

if ($var['toggle'] == 'WeaponHiding') {
	$player = $session->getPlayer();
	$player->setDisplayWeapons(!$player->isDisplayWeapons());
	// If this is called by ajax, we don't want to do any forwarding
	if (USING_AJAX) {
		exit;
	}
} elseif ($var['toggle'] == 'AJAX') {
	$account = $session->getAccount();
	$account->setUseAJAX(!$account->isUseAJAX());
	$account->update();
}

$body = $var['referrer'] ?? 'current_sector.php';
Page::create('skeleton.php', $body)->go();
