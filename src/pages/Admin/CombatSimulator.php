<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\DummyShip;
use Smr\Page\AccountPage;
use Smr\Template;

class CombatSimulator extends AccountPage {

	public string $file = 'admin/combat_simulator.php';

	/**
	 * @param ?array<mixed> $results
	 * @param array<\Smr\AbstractPlayer> $attackers
	 * @param array<\Smr\AbstractPlayer> $defenders
	 */
	public function __construct(
		private readonly ?array $results = null,
		private readonly array $attackers = [],
		private readonly array $defenders = []
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Combat Simulator');

		$template->assign('EditDummysLink', (new EditDummies())->href());
		$template->assign('DummyNames', DummyShip::getDummyNames());

		$duplicates = false;

		$attackers = $this->attackers;
		for ($i = count($attackers) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$attackers[$i] = null;
		}
		$template->assign('Attackers', $attackers);

		$defenders = $this->defenders;
		for ($i = count($defenders) + 1; $i <= MAXIMUM_PVP_FLEET_SIZE; ++$i) {
			$defenders[$i] = null;
		}
		$template->assign('Defenders', $defenders);

		$template->assign('Duplicates', $duplicates);

		$template->assign('CombatSimHREF', (new CombatSimulatorProcessor())->href());

		$template->assign('TraderCombatResults', $this->results);
	}

}
