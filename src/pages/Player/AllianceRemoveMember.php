<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AllianceRemoveMember extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_remove_member.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$account = $player->getAccount();
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$container = new AllianceRemoveMemberProcessor();
		$template->assign('BanishHREF', $container->href());

		// Get alliance members sorted by most active first
		$alliancePlayers = $alliance->getMembers();
		uasort($alliancePlayers, fn($a, $b) => $b->getLastCPLAction() <=> $a->getLastCPLAction());

		$members = [];
		foreach ($alliancePlayers as $alliancePlayer) {
			// You can't remove yourself from the alliance
			if ($alliancePlayer->equals($player)) {
				continue;
			}
			// get the amount of time since last_active
			$lastActive = $alliancePlayer->getLastCPLAction();
			$diff = 864000 + max(-864000, $lastActive - Epoch::time());
			$lastActiveDate = get_colored_text_range($diff, 864000, date($account->getDateTimeFormat(), $lastActive));

			$members[] = [
				'last_active' => $lastActiveDate,
				'display_name' => $alliancePlayer->getDisplayName(),
				'account_id' => $alliancePlayer->getAccountID(),
			];
		}
		$template->assign('Members', $members);
	}

}
