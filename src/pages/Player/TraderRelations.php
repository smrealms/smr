<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Race;
use Smr\Template;

class TraderRelations extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_relations.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Trader Relations');

		Menu::trader();

		$politicalRelations = [];
		$personalRelations = [];

		$raceRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
		foreach (Race::getAllNames() as $raceID => $raceName) {
			$politicalRelations[$raceName] = $raceRelations[$raceID];
			$personalRelations[$raceName] = $player->getPersonalRelation($raceID);
		}
		$template->assign('PoliticalRelations', $politicalRelations);
		$template->assign('PersonalRelations', $personalRelations);
	}

}
