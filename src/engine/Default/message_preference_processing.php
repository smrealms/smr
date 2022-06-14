<?php declare(strict_types=1);

use Smr\ScoutMessageGroupType;

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (Smr\Request::has('ignore_globals')) {
	$player->setIgnoreGlobals(Smr\Request::get('ignore_globals') == 'Yes');
} elseif (Smr\Request::has('group_scouts')) {
	$groupType = ScoutMessageGroupType::from(Smr\Request::get('group_scouts'));
	$player->setScoutMessageGroupType($groupType);
}

$container = Page::create('message_view.php');
$container->addVar('folder_id');
$container->go();
