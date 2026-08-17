<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Combat\Results\TraderFullCombatResults;
use Smr\DummyShip;
use Smr\Page\AccountPage;
use Smr\Template;

class CombatSimulator extends AccountPage {

	/**
	 * @param array<\Smr\Player> $attackers
	 * @param array<\Smr\Player> $defenders
	 */
	public function __construct(
		private readonly ?TraderFullCombatResults $results = null,
		private readonly array $attackers = [],
		private readonly array $defenders = [],
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Combat Simulator';

		$duplicates = false;

		$attackers = $this->attackers;
		for ($i = count($attackers) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$attackers[$i] = null;
		}

		$defenders = $this->defenders;
		for ($i = count($defenders) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$defenders[$i] = null;
		}

		$template->pageRenderer = fn() => CombatSimulatorRenderer::render(
			template: $template,
			EditDummysLink: new EditDummies()->href(),
			DummyNames: DummyShip::getDummyNames(),
			Attackers: $attackers,
			Defenders: $defenders,
			Duplicates: $duplicates,
			CombatSimPage: new CombatSimulatorProcessor(),
			TraderCombatResults: $this->results,
		);
	}

}
