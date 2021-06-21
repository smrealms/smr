<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Edit Dummys');

$template->assign('CombatSimLink', Page::create('skeleton.php', 'combat_simulator.php')->href());
$template->assign('ShipTypes', SmrShipType::getAll());
$template->assign('Weapons', SmrWeaponType::getAllWeaponTypes());

$template->assign('SelectDummysLink', Page::create('skeleton.php', 'edit_dummys.php')->href());
$template->assign('EditDummysLink', Page::create('edit_dummys_processing.php')->href());

$name = Request::get('dummy_name', 'New Dummy');
$dummyPlayer = DummyPlayer::getCachedDummyPlayer($name);
$dummyShip = $dummyPlayer->getShip();

$template->assign('DummyPlayer', $dummyPlayer);
$template->assign('DummyShip', $dummyShip);
$template->assign('ShipWeapons', $dummyShip->getWeapons());
$template->assign('Levels', Globals::getLevelRequirements());

$template->assign('DummyNames', DummyPlayer::getDummyPlayerNames());
