<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\DummyShip;
use Smr\Page\AccountPage;
use Smr\PlayerLevel;
use Smr\Request;
use Smr\ShipType;
use Smr\Template;
use Smr\WeaponType;

class EditDummies extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Edit Dummys';

		$name = Request::get('dummy_name', 'New Dummy');
		$dummyShip = DummyShip::getCachedDummyShip($name);

		$template->pageRenderer = fn() => EditDummiesRenderer::render(
			CombatSimLink: (new CombatSimulator())->href(),
			ShipTypes: ShipType::getAll(),
			Weapons: WeaponType::getAllWeaponTypes(),
			SelectDummysLink: (new self())->href(),
			EditDummysLink: (new EditDummiesProcessor())->href(),
			DummyPlayer: $dummyShip->getPlayer(),
			DummyShip: $dummyShip,
			Levels: PlayerLevel::getAll(),
			DummyNames: DummyShip::getDummyNames(),
		);
	}

}
