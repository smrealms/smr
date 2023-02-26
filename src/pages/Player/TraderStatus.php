<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\HardwareType;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class TraderStatus extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_status.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Trader Status');

		Menu::trader();

		if ($player->hasNewbieTurns()) {
			$container = new NewbieLeave();
			$template->assign('LeaveNewbieHREF', $container->href());
		}

		$container = new TraderRelations();
		$template->assign('RelationsHREF', $container->href());

		$container = new TraderSavings();
		$template->assign('SavingsHREF', $container->href());

		// Bounties
		$container = new TraderBounties();
		$template->assign('BountiesHREF', $container->href());

		$template->assign('BountiesClaimable', count($player->getClaimableBounties()));

		// Ship
		$container = new HardwareConfigure();
		$template->assign('HardwareHREF', $container->href());

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
		if (empty($hardware)) {
			$hardware[] = 'none';
		}
		$template->assign('Hardware', $hardware);

		$template->assign('NextLevel', $player->getLevel()->next());

		$container = new UserRankingView();
		$template->assign('UserRankingsHREF', $container->href());

		$container = new TraderNoteDeleteProcessor();
		$template->assign('NoteDeleteHREF', $container->href());

		$notes = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player_has_notes WHERE ' . $player->getSQL() . ' ORDER BY note_id DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$note = $dbRecord->getObject('note', true);
			$notes[$dbRecord->getInt('note_id')] = htmlentities($note);
		}
		$template->assign('Notes', $notes);

		$container = new TraderNoteAddProcessor();
		$template->assign('NoteAddHREF', $container->href());
	}

}
