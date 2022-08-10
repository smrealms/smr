<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceSetOp extends PlayerPage {

	public string $file = 'alliance_set_op.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$account = $player->getAccount();
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		// Print any error messages that may have been created
		if ($this->message !== null) {
			$template->assign('Message', $this->message);
		}

		// get the op from db
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND  game_id=' . $db->escapeNumber($player->getGameID()));

		if ($dbResult->hasRecord()) {
			// An op is already scheduled, so get the time
			$time = $dbResult->record()->getInt('time');
			$template->assign('OpDate', date($account->getDateTimeFormat(), $time));
			$template->assign('OpCountdown', format_time($time - Epoch::time()));

			// Add a cancel button
			$cancel = true;
		} else {
			$cancel = false;
		}
		$container = new AllianceSetOpProcessor($cancel);
		$template->assign('OpProcessingHREF', $container->href());

		// Stuff for designating a flagship
		$template->assign('FlagshipID', $alliance->getFlagshipID());
		$template->assign('AlliancePlayers', $alliance->getMembers());

		$container = new AllianceSetFlagshipProcessor();
		$template->assign('FlagshipHREF', $container->href());
	}

}
