<?php declare(strict_types=1);

use Smr\BountyType;
use Smr\Database;

$template = Smr\Template::getInstance();
$db = Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

$gameID = $var['game_id'];
$allianceID = $var['alliance_id'];

$alliance = SmrAlliance::getAlliance($allianceID, $gameID);
$template->assign('Alliance', $alliance);

$template->assign('PageTopic', 'Alliance Roster: ' . $alliance->getAllianceDisplayName(false, true));

// Offer a back button
$container = Page::create('game_stats.php', ['game_id' => $gameID]);
$template->assign('BackHREF', $container->href());

$players = [];
foreach ($alliance->getMembers() as $player) {
	$players[] = [
		'leader' => $player->isAllianceLeader() ? '*' : '',
		'bold' => $player->getAccountID() == $account->getAccountID() ? 'class="bold"' : '',
		'player_name' => $player->getDisplayName(),
		'experience' => $player->getExperience(),
		'alignment' => $player->getAlignment(),
		'race' => $player->getRaceName(),
		'kills' => $player->getKills(),
		'deaths' => $player->getDeaths(),
		'bounty' => $player->getCurrentBountyAmount(BountyType::UG) + $player->getCurrentBountyAmount(BountyType::HQ),
	];
}
$template->assign('Players', $players);
