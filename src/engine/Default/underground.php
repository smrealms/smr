<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if ($player->getAlignment() >= ALIGNMENT_GOOD) {
	create_error('You are not allowed to come in here!');
}

if (!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}
$location = SmrLocation::getLocation($var['LocationID']);
if (!$location->isUG()) {
	create_error('There is no underground here.');
}

$template->assign('PageTopic', $location->getName());

Menu::headquarters();

require_once(get_file_loc('gov.inc.php'));
$template->assign('AllBounties', getBounties('UG'));
$template->assign('MyBounties', $player->getClaimableBounties('UG'));

if ($player->getAlignment() < ALIGNMENT_GOOD && $player->getAlignment() >= ALIGNMENT_EVIL) {
	$container = Page::create('government_processing.php');
	$container->addVar('LocationID');
	$template->assign('JoinHREF', $container->href());
}
