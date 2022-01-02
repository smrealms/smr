<?php declare(strict_types=1);

$name = Smr\Request::get('dummy_name');
$dummyPlayer = DummyPlayer::getCachedDummyPlayer($name);
$dummyPlayer->setPlayerName($name);
$dummyPlayer->setExperience(Smr\Request::getInt('exp'));
$dummyPlayer->setShipTypeID(Smr\Request::getInt('ship_type_id'));
$dummyShip = $dummyPlayer->getShip();
$dummyShip->removeAllWeapons();
foreach (Smr\Request::getIntArray('weapons', []) as $weaponTypeID) {
	if ($weaponTypeID != 0) {
		$dummyShip->addWeapon(SmrWeapon::getWeapon($weaponTypeID));
	}
}
$dummyPlayer->cacheDummyPlayer();

Page::create('skeleton.php', 'admin/edit_dummys.php')->go();
