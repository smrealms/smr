<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Edit Dummys');

$template->assign('CombatSimLink', Page::create('admin/combat_simulator.php')->href());
$template->assign('ShipTypes', SmrShipType::getAll());
$template->assign('Weapons', SmrWeaponType::getAllWeaponTypes());

$template->assign('SelectDummysLink', Page::create('admin/edit_dummys.php')->href());
$template->assign('EditDummysLink', Page::create('admin/edit_dummys_processing.php')->href());

$name = Smr\Request::get('dummy_name', 'New Dummy');
$dummyShip = DummyShip::getCachedDummyShip($name);

$template->assign('DummyPlayer', $dummyShip->getPlayer());
$template->assign('DummyShip', $dummyShip);
$template->assign('ShipWeapons', $dummyShip->getWeapons());
$template->assign('Levels', Globals::getLevelRequirements());

$template->assign('DummyNames', DummyShip::getDummyNames());
