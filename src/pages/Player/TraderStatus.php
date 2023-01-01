<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Exception;
use Globals;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class TraderStatus extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_status.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
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

		$container = new UserRankingView();
		$template->assign('UserRankingsHREF', $container->href());

		$container = new TraderNoteDeleteProcessor();
		$template->assign('NoteDeleteHREF', $container->href());

		$notes = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM player_has_notes WHERE ' . $player->getSQL() . ' ORDER BY note_id DESC');
		foreach ($dbResult->records() as $dbRecord) {
			$note = gzuncompress($dbRecord->getString('note'));
			if ($note === false) {
				throw new Exception('Failed to gzuncompress note!');
			}
			$notes[$dbRecord->getInt('note_id')] = $note;
		}
		$template->assign('Notes', $notes);

		$container = new TraderNoteAddProcessor();
		$template->assign('NoteAddHREF', $container->href());
	}

}
