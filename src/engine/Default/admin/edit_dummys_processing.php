<?php declare(strict_types=1);

$name = Smr\Request::get('dummy_name');
$dummyShip = DummyShip::getCachedDummyShip($name);
$dummyPlayer = $dummyShip->getPlayer();
$dummyPlayer->setPlayerName($name);
$dummyPlayer->setExperience(Smr\Request::getInt('exp'));

$dummyShip->setTypeID(Smr\Request::getInt('ship_type_id'));
$dummyShip->setHardwareToMax();
$dummyShip->removeAllWeapons();
foreach (Smr\Request::getIntArray('weapons', []) as $weaponTypeID) {
	if ($weaponTypeID != 0) {
		$dummyShip->addWeapon(SmrWeapon::getWeapon($weaponTypeID));
	}
}
$dummyShip->cacheDummyShip();

Page::create('admin/edit_dummys.php')->go();
