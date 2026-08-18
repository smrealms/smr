<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Epoch;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceSetOp extends PlayerPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Player $player, Template $template): void {
		$account = $player->getAccount();
		$alliance = $player->getAlliance();

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		// Print any error messages that may have been created

		// get the op from db
		$db = Database::getInstance();
		$dbResult = $db->select('alliance_has_op', $alliance->SQLID, ['time']);

		if ($dbResult->hasRecord()) {
			// An op is already scheduled, so get the time
			$time = $dbResult->record()->getInt('time');
			$opDate = date($account->getDateTimeFormat(), $time);
			$opCountdown = format_time($time - Epoch::time());

			// Add a cancel button
			$cancel = true;
		} else {
			$opDate = null;
			$opCountdown = null;
			$cancel = false;
		}

		$template->pageRenderer = fn() => AllianceSetOpRenderer::render(
			Message: $this->message,
			OpDate: $opDate,
			OpCountdown: $opCountdown,
			OpProcessingHREF: new AllianceSetOpProcessor($cancel)->href(),
			FlagshipID: $alliance->getFlagshipID(),
			AlliancePlayers: $alliance->getMembers(includeNpc: false),
			FlagshipHREF: new AllianceSetFlagshipProcessor()->href(),
		);
	}

}
