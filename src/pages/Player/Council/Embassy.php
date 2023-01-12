<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class Embassy extends PlayerPage {

	use ReusableTrait;

	public string $file = 'council_embassy.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();

		if (!$player->isPresident()) {
			create_error('Only the president can view the embassy.');
		}

		$template->assign('PageTopic', 'Ruling Council Of ' . $player->getRaceName());

		Menu::council($player->getRaceID());

		$voteRaces = [];
		foreach (Race::getPlayableIDs() as $raceID) {
			if ($raceID == $player->getRaceID()) {
				continue;
			}
			$dbResult = $db->read('SELECT 1 FROM race_has_voting
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
						AND race_id_2 = ' . $db->escapeNumber($raceID));
			if ($dbResult->hasRecord()) {
				continue;
			}
			$voteRaces[$raceID] = (new EmbassyProcessor($raceID))->href();
		}
		$template->assign('VoteRaceHrefs', $voteRaces);
	}

}
