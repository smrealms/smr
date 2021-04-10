<?php declare(strict_types=1);

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

$db = Smr\Database::getInstance();
$db->query('SELECT count(*) FROM bounty WHERE claimer_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
$db->requireRecord();
$template->assign('BountiesClaimable', $db->getInt('count(*)'));

// Ship
$container['body'] = 'configure_hardware.php';
$template->assign('HardwareHREF', $container->href());

$hardware = [];
if ($ship->canHaveScanner()) {
	$hardware[] = 'Scanner';
}
if ($ship->canHaveIllusion()) {
	$hardware[] = 'Illusion Generator';
}
if ($ship->canHaveCloak()) {
	$hardware[] = 'Cloaking Device';
}
if ($ship->canHaveJump()) {
	$hardware[] = 'Jump Drive';
}
if ($ship->canHaveDCS()) {
	$hardware[] = 'Drone Scrambler';
}
if (empty($hardware)) {
	$hardware[] = 'none';
}
$template->assign('Hardware', $hardware);

$db->query('SELECT level_name,requirement FROM level WHERE requirement>' . $db->escapeNumber($player->getExperience()) . ' ORDER BY requirement ASC LIMIT 1');
if (!$db->nextRecord()) {
	$db->query('SELECT level_name,requirement FROM level ORDER BY requirement DESC LIMIT 1');
	$db->requireRecord();
}
$template->assign('NextLevelName', $db->getField('level_name'));

$container['body'] = 'rankings_view.php';
$template->assign('UserRankingsHREF', $container->href());

$container = Page::create('note_delete_processing.php');
$template->assign('NoteDeleteHREF', $container->href());

$notes = [];
$db->query('SELECT * FROM player_has_notes WHERE ' . $player->getSQL() . ' ORDER BY note_id DESC');
while ($db->nextRecord()) {
	$notes[$db->getInt('note_id')] = $db->getField('note');
}
$template->assign('Notes', $notes);

$container = Page::create('note_add_processing.php');
$template->assign('NoteAddHREF', $container->href());
