<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\HardwareType;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class TraderStatus extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Trader Status';

		Menu::trader();

		$shipType = $player->getShip()->getType();
		$hardwareChecks = [
			HARDWARE_SCANNER => $shipType->canHaveScanner(),
			HARDWARE_ILLUSION => $shipType->canHaveIllusion(),
			HARDWARE_CLOAK => $shipType->canHaveCloak(),
			HARDWARE_JUMP => $shipType->canHaveJump(),
			HARDWARE_DCS => $shipType->canHaveDCS(),
		];
		$hardware = [];
		foreach ($hardwareChecks as $hardwareTypeID => $shipTypeCanHave) {
			if ($shipTypeCanHave) {
				$hardware[] = HardwareType::get($hardwareTypeID)->name;
			}
		}
		if (count($hardware) === 0) {
			$hardware[] = 'none';
		}

		$notes = [];
		$db = Database::getInstance();
		$dbResult = $db->select('player_has_notes', $player->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			$note = $dbRecord->getObject('note', true);
			$notes[$dbRecord->getInt('note_id')] = htmlentities($note);
		}
		$notes = array_reverse($notes, true); // display newest first

		$template->pageRenderer = fn() => TraderStatusRenderer::render(
			LeaveNewbieHREF: $player->hasNewbieTurns() ? new NewbieLeave()->href() : null,
			RelationsHREF: new TraderRelations()->href(),
			SavingsHREF: new TraderSavings()->href(),
			BountiesHREF: new TraderBounties()->href(),
			BountiesClaimable: count($player->getClaimableBounties()),
			HardwareHREF: new HardwareConfigure()->href(),
			Hardware: $hardware,
			NextLevel: $player->getLevel()->next(),
			UserRankingsHREF: new UserRankingView()->href(),
			NoteDeleteHREF: new TraderNoteDeleteProcessor()->href(),
			Notes: $notes,
			NoteAddHREF: new TraderNoteAddProcessor()->href(),
			ThisAccount: $player->getAccount(),
			ThisPlayer: $player,
			ThisShip: $player->getShip(),
		);
	}

}
