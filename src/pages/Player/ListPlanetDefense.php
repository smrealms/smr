<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetList;
use Smr\Player;
use Smr\Template;

class ListPlanetDefense extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_list.php';

	public function __construct(
		private readonly int $allianceID,
	) {}

	public function build(Player $player, Template $template): void {
		Menu::planetList($this->allianceID, 0);
		PlanetList::common($this->allianceID, true);
	}

}
