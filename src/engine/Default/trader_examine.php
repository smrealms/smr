<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// Get the player we're attacking
$targetPlayer = SmrPlayer::getPlayer($var['target'], $player->getGameID());

if ($targetPlayer->isDead()) {
	$container = Page::create('skeleton.php', 'current_sector.php');
	$container['msg'] = '<span class="red bold">ERROR:</span> Target already dead.';
	$container->go();
}


$template->assign('PageTopic', 'Examine Ship');
$template->assign('TargetPlayer', $targetPlayer);
