<?php declare(strict_types=1);

use Smr\Request;
use Smr\ScoutMessageGroupType;

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (Request::has('ignore_globals')) {
	$player->setIgnoreGlobals(Request::get('ignore_globals') == 'Yes');
} elseif (Request::has('group_scouts')) {
	$groupType = ScoutMessageGroupType::from(Request::get('group_scouts'));
	$player->setScoutMessageGroupType($groupType);
}

$container = Page::create('message_view.php');
$container->addVar('folder_id');
$container->go();
