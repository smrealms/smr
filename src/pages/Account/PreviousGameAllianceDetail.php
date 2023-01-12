<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Alliance;
use Smr\BountyType;
use Smr\Page\AccountPage;
use Smr\Template;

class PreviousGameAllianceDetail extends AccountPage {

	public string $file = 'previous_game_alliance_detail.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $allianceID
	) {}

	public function build(Account $account, Template $template): void {
		$gameID = $this->gameID;
		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $gameID);
		$template->assign('Alliance', $alliance);

		$template->assign('PageTopic', 'Alliance Roster: ' . $alliance->getAllianceDisplayName(false, true));

		// Offer a back button
		$container = new GameStats($gameID);
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
				'bounty' => $player->getActiveBounty(BountyType::UG)->getCredits() + $player->getActiveBounty(BountyType::HQ)->getCredits(),
			];
		}
		$template->assign('Players', $players);
	}

}
