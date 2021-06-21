<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Trader Status');

Menu::trader();

if ($player->hasNewbieTurns()) {
	$container = Page::create('skeleton.php', 'leave_newbie.php');
	$template->assign('LeaveNewbieHREF', $container->href());
}

$container = Page::create('skeleton.php');
$container['body'] = 'trader_relations.php';
$template->assign('RelationsHREF', $container->href());

$container['body'] = 'trader_savings.php';
$template->assign('SavingsHREF', $container->href());

// Bounties
$container['body'] = 'trader_bounties.php';
$template->assign('BountiesHREF', $container->href());

$template->assign('BountiesClaimable', count($player->getClaimableBounties()));

// Ship
$container['body'] = 'configure_hardware.php';
$template->assign('HardwareHREF', $container->href());

$hardware = [];
$shipType = $player->getShip()->getType();
if ($shipType->canHaveScanner()) {
	$hardware[] = Globals::getHardwareTypes(HARDWARE_SCANNER)['Name'];
}
if ($shipType->canHaveIllusion()) {
	$hardware[] = Globals::getHardwareTypes(HARDWARE_ILLUSION)['Name'];
}
if ($shipType->canHaveCloak()) {
	$hardware[] = Globals::getHardwareTypes(HARDWARE_CLOAK)['Name'];
}
if ($shipType->canHaveJump()) {
	$hardware[] = Globals::getHardwareTypes(HARDWARE_JUMP)['Name'];
}
if ($shipType->canHaveDCS()) {
	$hardware[] = Globals::getHardwareTypes(HARDWARE_DCS)['Name'];
}
if (empty($hardware)) {
	$hardware[] = 'none';
}
$template->assign('Hardware', $hardware);

$template->assign('NextLevelName', $player->getNextLevel()['Name']);

$container['body'] = 'rankings_view.php';
$template->assign('UserRankingsHREF', $container->href());

$container = Page::create('note_delete_processing.php');
$template->assign('NoteDeleteHREF', $container->href());

$notes = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM player_has_notes WHERE ' . $player->getSQL() . ' ORDER BY note_id DESC');
foreach ($dbResult->records() as $dbRecord) {
	$notes[$dbRecord->getInt('note_id')] = $dbRecord->getField('note');
}
$template->assign('Notes', $notes);

$container = Page::create('note_add_processing.php');
$template->assign('NoteAddHREF', $container->href());
