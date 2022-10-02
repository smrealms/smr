<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$container = Page::create('alliance_set_op_processing.php');

// Print any error messages that may have been created
if (!empty($var['message'])) {
	$template->assign('Message', $var['message']);
}

// get the op from db
$db = Database::getInstance();
$dbResult = $db->read('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND  game_id=' . $db->escapeNumber($player->getGameID()));

if ($dbResult->hasRecord()) {
	// An op is already scheduled, so get the time
	$time = $dbResult->record()->getInt('time');
	$template->assign('OpDate', date($account->getDateTimeFormat(), $time));
	$template->assign('OpCountdown', format_time($time - Epoch::time()));

	// Add a cancel button
	$container['cancel'] = true;
}

$template->assign('OpProcessingHREF', $container->href());


// Stuff for designating a flagship
$template->assign('FlagshipID', $alliance->getFlagshipID());
$template->assign('AlliancePlayers', $alliance->getMembers());

$container = Page::create('alliance_set_flagship_processing.php');
$template->assign('FlagshipHREF', $container->href());
