<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\Database;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class Main extends PlanetPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
	) {}

	protected function buildPlanetPage(Player $player, Template $template): void {
		$planet = $player->getSectorPlanet();

		$db = Database::getInstance();
		$ticker = getDisplayTickers($template, $player, $db);

		// Cloaked ships are visible on planets
		$template->pageRenderer = fn() => MainRenderer::render(
			ErrorMsg: $this->errorMessage,
			Msg: $this->message,
			LaunchLink: new LaunchProcessor()->href(),
			VisiblePlayers: $planet->getOtherTraders($player),
			SectorPlayersLabel: 'Ships',
			ThisPlanet: $player->getSectorPlanet(),
			ThisAccount: $player->getAccount(),
			ThisPlayer: $player,
			Ticker: $ticker,
		);
	}

}
