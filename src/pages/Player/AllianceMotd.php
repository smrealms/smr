<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AllianceMotd extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_mod.php';

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance = Alliance::getAlliance($this->allianceID, $player->getGameID());
		$template->assign('Alliance', $alliance);

		Globals::canAccessPage('AllianceMOTD', $player, ['AllianceID' => $alliance->getAllianceID()]);

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		// Check to see if an alliance op is scheduled
		// Display it for 1 hour past start time (late arrivals, etc.)
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND time > ' . $db->escapeNumber(Epoch::time() - 3600));
		if ($dbResult->hasRecord()) {
			$template->assign('OpTime', $dbResult->record()->getInt('time'));

			// Has player responded yet?
			$dbResult2 = $db->read('SELECT response FROM alliance_has_op_response WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND ' . $player->getSQL());

			$response = $dbResult2->hasRecord() ? $dbResult2->record()->getString('response') : null;
			$responseHREF = (new AllianceOpResponseProcessor($this->allianceID))->href();
			$template->assign('OpResponseHREF', $responseHREF);

			$responseInputs = [];
			foreach (['Yes', 'Maybe', 'No'] as $option) {
				$style = strtoupper($option) == $response ? 'style="background: green"' : '';
				$responseInputs[$option] = $style;
			}
			$template->assign('ResponseInputs', $responseInputs);
		}

		// Does the player have edit permission?
		$role_id = $player->getAllianceRole($alliance->getAllianceID());
		$dbResult = $db->read('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
		$dbRecord = $dbResult->record();
		if ($dbRecord->getBoolean('change_mod') || $dbRecord->getBoolean('change_pass')) {
			$container = new AllianceGovernance($alliance->getAllianceID());
			$template->assign('EditHREF', $container->href());
		}

		$template->assign('DiscordServer', $alliance->getDiscordServer());
	}

}
