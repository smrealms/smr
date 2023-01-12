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

	public string $file = 'admin/edit_dummys.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Edit Dummys');

		$template->assign('CombatSimLink', (new CombatSimulator())->href());
		$template->assign('ShipTypes', ShipType::getAll());
		$template->assign('Weapons', WeaponType::getAllWeaponTypes());

		$template->assign('SelectDummysLink', (new self())->href());
		$template->assign('EditDummysLink', (new EditDummiesProcessor())->href());

		$name = Request::get('dummy_name', 'New Dummy');
		$dummyShip = DummyShip::getCachedDummyShip($name);

		$template->assign('DummyPlayer', $dummyShip->getPlayer());
		$template->assign('DummyShip', $dummyShip);
		$template->assign('ShipWeapons', $dummyShip->getWeapons());
		$template->assign('Levels', PlayerLevel::getAll());

		$template->assign('DummyNames', DummyShip::getDummyNames());
	}

}
