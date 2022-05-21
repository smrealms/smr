<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$gameID = $var['game_id'];
$allianceID = $var['alliance_id'];

$alliance = SmrAlliance::getAlliance($allianceID, $gameID);
$template->assign('Alliance', $alliance);

$template->assign('PageTopic', 'Alliance Roster: ' . $alliance->getAllianceDisplayName(false, true));

// Offer a back button
$container = Page::create('skeleton.php', 'game_stats.php', ['game_id' => $gameID]);
$template->assign('BackHREF', $container->href());

$players = [];
foreach ($alliance->getMembers() as $player) {
	$players[] = [
		'leader' => $player->isAllianceLeader() ? '*' : '',
		'bold' => $player->getAccountID() == $account->getAccountID() ? 'class="bold"' : '',
		'player_name' => $player->getDisplayName(),
		'experience' => number_format($player->getExperience()),
		'alignment' => number_format($player->getAlignment()),
		'race' => $player->getRaceName(),
		'kills' => $player->getKills(),
		'deaths' => $player->getDeaths(),
		'bounty' => $player->getCurrentBountyAmount('UG') + $player->getCurrentBountyAmount('HQ'),
	];
}
$template->assign('Players', $players);
