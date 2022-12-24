<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Main extends PlayerPage {

	use ReusableTrait;

	public string $file = 'planet_main.php';

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$planet = $player->getSectorPlanet();

		//echo the dump cargo message or other message.
		$template->assign('ErrorMsg', $this->errorMessage);
		if ($this->message !== null) {
			$template->assign('Msg', bbifyMessage($this->message));
		}

		$db = Database::getInstance();
		doTickerAssigns($template, $player, $db);

		$template->assign('LaunchLink', (new LaunchProcessor())->href());

		// Cloaked ships are visible on planets
		$template->assign('VisiblePlayers', $planet->getOtherTraders($player));
		$template->assign('SectorPlayersLabel', 'Ships');
	}

}