<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;
use Smr\Treaty;

class AllianceTreaties extends PlayerPage {

	public string $file = 'alliance_treaties.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->assign('PageTopic', 'Alliance Treaties');
		Menu::alliance($alliance->getAllianceID());

		$alliances = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($player->getAllianceID()) . ' ORDER BY alliance_name');
		foreach ($dbResult->records() as $dbRecord) {
			$allianceID = $dbRecord->getInt('alliance_id');
			$alliance = Alliance::getAlliance($allianceID, $player->getGameID(), false, $dbRecord);
			$alliances[$allianceID] = $alliance->getAllianceDisplayName();
		}
		$template->assign('Alliances', $alliances);

		$template->assign('Message', $this->message);

		$offers = [];
		$dbResult = $db->read('SELECT * FROM alliance_treaties WHERE alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND official = \'FALSE\'');
		foreach ($dbResult->records() as $dbRecord) {
			$offerTerms = [];
			foreach (array_keys(Treaty::TYPES) as $term) {
				if ($dbRecord->getBoolean($term)) {
					$offerTerms[] = $term;
				}
			}
			$otherAllianceID = $dbRecord->getInt('alliance_id_1');
			$container = new AllianceTreatiesProcessor($otherAllianceID, true, $dbRecord->getBoolean('aa_access'));
			$acceptHREF = $container->href();
			$container = new AllianceTreatiesProcessor($otherAllianceID, false);
			$rejectHREF = $container->href();

			$offers[] = [
				'Alliance' => Alliance::getAlliance($otherAllianceID, $player->getGameID()),
				'Terms' => $offerTerms,
				'AcceptHREF' => $acceptHREF,
				'RejectHREF' => $rejectHREF,
			];
		}
		$template->assign('Offers', $offers);

		$container = new AllianceTreatiesConfirm();
		$template->assign('SendOfferHREF', $container->href());
	}

}
