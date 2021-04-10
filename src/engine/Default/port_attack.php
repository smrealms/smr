<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

if (isset($var['results'])) {
	$template->assign('FullPortCombatResults', $var['results']);
	$template->assign('AlreadyDestroyed', false);
} else {
	$template->assign('AlreadyDestroyed', true);
}
$template->assign('MinimalDisplay', false);

if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
$template->assign('Port', $sector->getPort());
