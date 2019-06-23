<?php

// We can release the sector lock now because we know that the following
// code is read-only. This will help reduce sector lag and possible abuse.
release_lock();

if (isset($var['AdminCreateGameID']) && $var['AdminCreateGameID'] !== false)
	$gameID = $var['AdminCreateGameID'];
else
	$gameID = $player->getGameID();

if (isset($var['AdminCreateGameID']) && $var['AdminCreateGameID'] !== false)
	$adminCreate = true;
else
	$adminCreate = false;

// NOTE: If the format of this file is changed in an incompatible way,
// make sure to update the SMR_FILE_VERSION!

$file = '; SMR Sectors File v' . SMR_FILE_VERSION . '
; Created on ' . date(DEFAULT_DATE_FULL_SHORT) . '
[Races]
; Name = ID' . EOL;
foreach (Globals::getRaces() as $race) {
	$file .= inify($race['Race Name']) . '=' . $race['Race ID'] . EOL;
}

$file .= '[Goods]
; ID = Name, BasePrice' . EOL;
foreach (Globals::getGoods() as $good) {
	$file .= $good['ID'] . '=' . inify($good['Name']) . ',' . $good['BasePrice'] . EOL;
}

$file .= '[Weapons]
; Weapon = Race,Cost,Shield,Armour,Accuracy,Power level,EMP (%),Align Restriction,Attack Restriction
; Align: 0=none, 1=good, 2=evil, 3=newbie
; Attack: 0=none, 1=raid' . EOL;
foreach (SmrWeapon::getAllWeapons(Globals::getGameType($gameID)) as $weapon) {
	$file .= inify($weapon->getName()) . '=' . inify($weapon->getRaceName()) . ',' . $weapon->getCost() . ',' . $weapon->getShieldDamage() . ',' . $weapon->getArmourDamage() . ',' . $weapon->getBaseAccuracy() . ',' . $weapon->getPowerLevel() . ',' . $weapon->getEmpDamage() . ',' . $weapon->getBuyerRestriction() . ',' . ($weapon->isRaidWeapon() ? '1' : '0') . EOL;
}

$file .= '[ShipEquipment]
; Name = Cost' . EOL;
$hardwares = Globals::getHardwareTypes();
foreach ($hardwares as $hardware) {
	$file .= inify($hardware['Name']) . '=' . $hardware['Cost'] . EOL;
}

$file .= '[Ships]
; Name = Race,Cost,TPH,Hardpoints,Power,+Equipment (Optional),+Restrictions(Optional)
; Restrictions:Align(Integer)' . EOL;
foreach (AbstractSmrShip::getAllBaseShips(Globals::getGameType($gameID)) as $ship) {
	$file .= inify($ship['Name']) . '=' . Globals::getRaceName($ship['RaceID']) . ',' . $ship['Cost'] . ',' . $ship['Speed'] . ',' . $ship['Hardpoint'] . ',' . $ship['MaxPower'];
	if ($ship['MaxHardware'] > 0) {
		$shipEquip = ',ShipEquipment=';
		foreach ($ship['MaxHardware'] as $hardwareID => $maxHardware) {
			$shipEquip .= $hardwares[$hardwareID]['Name'] . '=' . $maxHardware . ';';
		}
		$file .= substr($shipEquip, 0, -1);
		$file .= ',Restrictions=' . $ship['AlignRestriction'];
	}
	$file .= EOL;
}

$file .= '[Locations]
; Name = +Sells' . EOL;
foreach (SmrLocation::getAllLocations() as $location) {
	$file .= inify($location->getName()) . '=';
	$locSells = '';
	if ($location->isWeaponSold()) {
		$locSells .= 'Weapons=';
		foreach ($location->getWeaponsSold() as $locWeapon) {
			$locSells .= $locWeapon->getName() . ';';
		}
		$locSells = substr($locSells, 0, -1) . ',';
	}
	if ($location->isHardwareSold()) {
		$locSells .= 'ShipEquipment=';
		foreach ($location->getHardwareSold() as $locHardware) {
			$locSells .= $locHardware['Name'] . ';';
		}
		$locSells = substr($locSells, 0, -1) . ',';
	}
	if ($location->isShipSold()) {
		$locSells .= 'Ships=';
		foreach ($location->getShipsSold() as $locShip) {
			$locSells .= $locShip['Name'] . ';';
		}
		$locSells = substr($locSells, 0, -1) . ',';
	}
	if ($location->isBank()) {
		$locSells .= 'Bank=,';
	}
	if ($location->isBar()) {
		$locSells .= 'Bar=,';
	}
	if ($location->isHQ()) {
		$locSells .= 'HQ=,';
	}
	if ($location->isUG()) {
		$locSells .= 'UG=,';
	}
	if ($location->isFed()) {
		$locSells .= 'Fed=,';
	}
	if ($locSells != '')
		$file .= substr($locSells, 0, -1);
	$file .= EOL;
}

// Everything below here must be valid INI syntax (safe to parse)
$file .= '[Metadata]
FileVersion=' . SMR_FILE_VERSION . '
[Game]
Name='.inify(SmrGame::getGame($gameID)->getName()) . '
[Galaxies]
';
$galaxies = SmrGalaxy::getGameGalaxies($gameID);
foreach ($galaxies as $galaxy) {
	$file .= $galaxy->getGalaxyID() . '=' . $galaxy->getWidth() . ',' . $galaxy->getHeight() . ',' . $galaxy->getGalaxyType() . ',' . inify($galaxy->getName()) . ',' . $galaxy->getMaxForceTime() . EOL;
}


foreach ($galaxies as $galaxy) {
	foreach ($galaxy->getSectors() as $sector) {
		$file .= '[Sector=' . $sector->getSectorID() . ']' . EOL;
		
		if (!$sector->isVisited($player) && $adminCreate === false)
			continue;
		
		foreach ($sector->getLinks() as $linkName => $link) {
			$file .= $linkName . '=' . $link . EOL;
		}
		if ($sector->hasWarp())
			$file .= 'Warp=' . $sector->getWarp() . EOL;
		if (($adminCreate !== false && $sector->hasPort()) || is_object($player) && $sector->hasCachedPort($player)) {
			if ($adminCreate !== false)
				$port = $sector->getPort();
			else
				$port = $sector->getCachedPort($player);
			$file .= 'Port Level=' . $port->getLevel() . EOL;
			$file .= 'Port Race=' . $port->getRaceID() . EOL;
			if (!empty($port->getSoldGoodIDs())) {
				$file .= 'Buys=' . join(',', $port->getSoldGoodIDs()) . EOL;
			}
			
			if (!empty($port->getBoughtGoodIDs())) {
				$file .= 'Sells=' . join(',', $port->getBoughtGoodIDs()) . EOL;
			}
		}
		if ($sector->hasPlanet()) {
			$planetType = $sector->getPlanet()->getTypeID();
			$file .= 'Planet=' . $planetType . EOL;
		}
		if ($sector->hasLocation()) {
			$locationsString = 'Locations=';
			foreach ($sector->getLocations() as $location) {
				$locationsString .= inify($location->getName()) . ',';
			}
			$file .= substr($locationsString, 0, -1) . EOL;
		}
		if ($adminCreate === false && $sector->hasFriendlyForces($player)) {
			$forcesString = 'FriendlyForces=';
			foreach ($sector->getFriendlyForces($player) as $forces) {
				$forcesString .= inify($forces->getOwner()->getPlayerName()) . '=' . inify(Globals::getHardwareName(HARDWARE_MINE)) . '=' . $forces->getMines() . ';' . inify(Globals::getHardwareName(HARDWARE_COMBAT)) . '=' . $forces->getCDs() . ';' . inify(Globals::getHardwareName(HARDWARE_SCOUT)) . '=' . $forces->getSDs() . ',';
			}
			$file .= substr($forcesString, 0, -1) . EOL;
		}
	}
	SmrPort::clearCache();
	SmrForce::clearCache();
	SmrPlanet::clearCache();
	SmrSector::clearCache();
}

$size = strlen($file);

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="' . SmrGame::getGame($gameID)->getName() . '.smr"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $size);

echo $file;

exit;
