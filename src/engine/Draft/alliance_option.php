<?php declare(strict_types=1);

// It is not strictly necessary to define this here, except to indicate
// to phpcs that we're not implicitly creating this array.
$links = [];

require(ENGINE . 'Default/alliance_option.php');

$player = Smr\Session::getInstance()->getPlayer();

if ($player->isDraftLeader()) {
	$container = Page::create('alliance_pick.php');
	$links[] = [
		'link' => create_link($container, 'Pick Members'),
		'text' => 'Draft players into your alliance.',
	];

	// Reset Links with the added Draft option
	$template = Smr\Template::getInstance();
	$template->unassign('Links');
	$template->assign('Links', $links);
}
