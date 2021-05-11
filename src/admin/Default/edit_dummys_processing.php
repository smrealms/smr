<?php declare(strict_types=1);

$name = Request::get('dummy_name');
$dummyPlayer = DummyPlayer::getCachedDummyPlayer($name);
$dummyPlayer->setPlayerName($name);
$dummyPlayer->setExperience(Request::getInt('exp'));
$dummyPlayer->setShipTypeID(Request::getInt('ship_id'));
$dummyShip = $dummyPlayer->getShip();
$dummyShip->removeAllWeapons();
foreach (Request::getIntArray('weapons', []) as $weaponTypeID) {
	if ($weaponTypeID != 0) {
		$dummyShip->addWeapon(SmrWeapon::getWeapon($weaponTypeID));
	}
}
$dummyPlayer->cacheDummyPlayer();

Page::create('skeleton.php', 'edit_dummys.php')->go();
