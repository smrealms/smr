<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use DummyShip;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrWeapon;

class EditDummiesProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$name = Request::get('dummy_name');
		$dummyShip = DummyShip::getCachedDummyShip($name);
		$dummyPlayer = $dummyShip->getPlayer();
		$dummyPlayer->setPlayerName($name);
		$dummyPlayer->setExperience(Request::getInt('exp'));

		$dummyShip->setTypeID(Request::getInt('ship_type_id'));
		$dummyShip->setHardwareToMax();
		$dummyShip->removeAllWeapons();
		foreach (Request::getIntArray('weapons', []) as $weaponTypeID) {
			if ($weaponTypeID != 0) {
				$dummyShip->addWeapon(SmrWeapon::getWeapon($weaponTypeID));
			}
		}
		$dummyShip->cacheDummyShip();

		(new EditDummies())->go();
	}

}
