<?php declare(strict_types=1);

use Smr\Epoch;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$container = Page::create('alliance_remove_member_processing.php');
$template->assign('BanishHREF', $container->href());

$members = [];
foreach ($alliance->getMembers() as $alliancePlayer) {
	// You can't remove yourself from the alliance
	if ($alliancePlayer->equals($player)) {
		continue;
	}
	// get the amount of time since last_active
	$lastActive = $alliancePlayer->getLastCPLAction();
	$diff = 864000 + max(-864000, $lastActive - Epoch::time());
	$lastActiveDate = get_colored_text_range($diff, 864000, date($account->getDateTimeFormat(), $lastActive));

	$members[] = [
		'sort_order' => $lastActive,
		'last_active' => $lastActiveDate,
		'display_name' => $alliancePlayer->getDisplayName(),
		'account_id' => $alliancePlayer->getAccountID(),
	];
}
Sorter::sortByNumElement($members, 'sort_order', true);
$template->assign('Members', $members);
