<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Planet;
use Smr\PlanetMenuOption;
use Smr\Template;

abstract class PlanetPage extends PlayerPage {

	abstract protected function buildPlanetPage(AbstractPlayer $player, Template $template): void;

	/**
	 * Common code for all the planet display pages
	 */
	public function build(AbstractPlayer $player, Template $template): void {
		if (!$player->isLandedOnPlanet()) {
			// If not on planet, they must have been kicked by another player
			create_error('You have been ejected from the planet!');
		}

		$planet = $player->getSectorPlanet();
		$template->assign('ThisPlanet', $planet);
		$template->assign('PageTopic', 'Planet : ' . $planet->getDisplayName() . ' [Sector #' . $player->getSectorID() . ']');

		$this->addMenu($template, $planet);

		$this->buildPlanetPage($player, $template);
	}

	protected function addMenu(Template $template, Planet $planet): void {
		$menuItems = [];
		foreach (PlanetMenuOption::cases() as $option) {
			// All planets must at least have the "Planet Main" link
			if ($option != PlanetMenuOption::MAIN && !$planet->hasMenuOption($option)) {
				continue;
			}
			$container = match ($option) {
				PlanetMenuOption::MAIN => new Main(),
				PlanetMenuOption::CONSTRUCTION => new Construction(),
				PlanetMenuOption::DEFENSE => new Defense(),
				PlanetMenuOption::OWNERSHIP => new Ownership(),
				PlanetMenuOption::STOCKPILE => new Stockpile(),
				PlanetMenuOption::FINANCE => new Financial(),
			};
			// Bold this link if this is the page we're currently on
			$text = $option->value;
			if ($this instanceof $container) {
				$text = '<b>' . $text . '</b>';
			}
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $text,
			];
		}
		$template->assign('MenuItems', $menuItems);
	}

}
