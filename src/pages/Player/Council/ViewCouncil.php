<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use AbstractSmrPlayer;
use Menu;
use Smr\CouncilVoting;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class ViewCouncil extends PlayerPage {

	use ReusableTrait;

	public string $file = 'council_list.php';

	public function __construct(
		private readonly int $raceID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$raceID = $this->raceID;

		$template->assign('PageTopic', 'Ruling Council Of ' . Race::getName($raceID));
		$template->assign('RaceID', $raceID);

		Menu::council($raceID);

		// check for relations here
		CouncilVoting::modifyRelations($raceID, $player->getGameID());
		CouncilVoting::checkPacts($raceID, $player->getGameID());
	}

}
