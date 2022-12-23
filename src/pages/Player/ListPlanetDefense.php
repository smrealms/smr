<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetList;
use Smr\Template;

class ListPlanetDefense extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_list.php';

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		Menu::planetList($this->allianceID, 0);
		PlanetList::common($this->allianceID, true);
	}

}
