<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceMotd extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player, Template $template): void {
		$alliance = Alliance::getAlliance($this->allianceID, $player->getGameID());

		Globals::canAccessPage('AllianceMOTD', $player, ['AllianceID' => $alliance->getAllianceID()]);

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		// Check to see if an alliance op is scheduled
		// Display it for 1 hour past start time (late arrivals, etc.)
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT time FROM alliance_has_op WHERE ' . Alliance::SQL . ' AND time > :expire_time', [
			...$alliance->SQLID,
			'expire_time' => $db->escapeNumber(Epoch::time() - 3600),
		]);
		$responseInputs = [];
		if ($dbResult->hasRecord()) {
			$opTime = $dbResult->record()->getInt('time');

			// Has player responded yet?
			$dbResult2 = $db->select(
				'alliance_has_op_response',
				[
					'alliance_id' => $this->allianceID,
					...$player->SQLID,
				],
				['response'],
			);

			$response = $dbResult2->hasRecord() ? $dbResult2->record()->getString('response') : null;
			$responseHREF = new AllianceOpResponseProcessor($this->allianceID)->href();

			foreach (['Yes', 'Maybe', 'No'] as $option) {
				$fields = strtoupper($option) === $response ? ['style' => 'background: green'] : [];
				$responseInputs[$option] = $fields;
			}
		} else {
			$opTime = null;
			$responseHREF = null;
		}

		// Does the player have edit permission?
		$role_id = $player->getAllianceRole($alliance->getAllianceID());
		$dbResult = $db->select('alliance_has_roles', [
			...$alliance->SQLID,
			'role_id' => $role_id,
		]);
		$dbRecord = $dbResult->record();
		if ($dbRecord->getBoolean('change_mod') || $dbRecord->getBoolean('change_pass')) {
			$container = new AllianceGovernance($alliance->getAllianceID());
			$editHREF = $container->href();
		} else {
			$editHREF = null;
		}

		$template->pageRenderer = fn() => AllianceMotdRenderer::render(
			Alliance: $alliance,
			OpTime: $opTime,
			OpResponseHREF: $responseHREF,
			ResponseInputs: $responseInputs,
			EditHREF: $editHREF,
			DiscordServer: $alliance->getDiscordServer(),
			ThisAccount: $player->getAccount(),
		);
	}

}
