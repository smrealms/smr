<?php declare(strict_types=1);

use Smr\Request;

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

		Page::create('admin/edit_dummys.php')->go();
