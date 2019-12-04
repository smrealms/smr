<?php declare(strict_types=1);
if ($var['toggle'] == 'WeaponHiding') {
	$player->setDisplayWeapons(!$player->isDisplayWeapons());
	// If this is called by ajax, we don't want to do any forwarding
	if (USING_AJAX) {
		exit;
	}
} else if ($var['toggle'] == 'AJAX') {
	$account->setUseAJAX(!$account->isUseAJAX());
}

$body = $var['referrer'] ?? 'current_sector.php';
forward(create_container('skeleton.php', $body));
