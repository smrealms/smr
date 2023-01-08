<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\BountyType;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;
use SmrAlliance;

class PreviousGameAllianceDetail extends AccountPage {

	public string $file = 'previous_game_alliance_detail.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $allianceID
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$gameID = $this->gameID;
		$allianceID = $this->allianceID;

		$alliance = SmrAlliance::getAlliance($allianceID, $gameID);
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
