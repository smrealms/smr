<?php declare(strict_types=1);
$template->assign('PageTopic', 'Edit Dummys');

$template->assign('CombatSimLink', Page::create('skeleton.php', 'combat_simulator.php')->href());
$template->assign('BaseShips', AbstractSmrShip::getAllBaseShips());
$template->assign('Weapons', SmrWeaponType::getAllWeaponTypes());

$template->assign('EditDummysLink', Page::create('skeleton.php', 'edit_dummys.php')->href());

$dummyPlayer = DummyPlayer::getCachedDummyPlayer($_REQUEST['dummy_name']);
$dummyShip = $dummyPlayer->getShip();

if (isset($_REQUEST['save_dummy'])) {
	$dummyPlayer->setPlayerName($_REQUEST['dummy_name']);
	$dummyPlayer->setExperience($_REQUEST['level']);
	$dummyPlayer->setShipTypeID($_REQUEST['ship_id']);
	$dummyShip->regenerate($dummyPlayer);
	if (isset($_REQUEST['weapons']) && is_array($_REQUEST['weapons'])) {
		$dummyShip->removeAllWeapons();
		foreach ($_REQUEST['weapons'] as $weaponTypeID) {
			if ($weaponTypeID != 0) {
				$dummyShip->addWeapon(SmrWeapon::getWeapon($weaponTypeID));
			}
		}
	}
	$dummyPlayer->cacheDummyPlayer();
}


$template->assign('DummyPlayer', $dummyPlayer);
$template->assign('DummyShip', $dummyShip);
$template->assign('ShipWeapons', $dummyShip->getWeapons());
$template->assign('Levels', Globals::getLevelRequirements());



$template->assign('DummyNames', DummyPlayer::getDummyPlayerNames());
