<?php declare(strict_types=1);

namespace Smr;

class SectorsFile {

	public static function create(int $gameID, ?AbstractPlayer $player, bool $adminCreate = false): never {
		// NOTE: If the format of this file is changed in an incompatible way,
		// make sure to update the SMR_FILE_VERSION!

		$file = ';SMR1.6 Sectors File v' . SMR_FILE_VERSION . '
		; Created on ' . date(DEFAULT_DATE_TIME_FORMAT) . '
		[Races]
		; Name = ID' . EOL;
		foreach (Race::getAllNames() as $raceID => $raceName) {
			$file .= inify($raceName) . '=' . $raceID . EOL;
		}

		$file .= '[Goods]
		; ID = Name, BasePrice' . EOL;
		foreach (TradeGood::getAll() as $goodID => $good) {
			$file .= $goodID . '=' . inify($good->name) . ',' . $good->basePrice . EOL;
		}

		$file .= '[Weapons]
		; Weapon = Race,Cost,Shield,Armour,Accuracy,Power level,Restriction
		; Restriction: 0=none, 1=good, 2=evil, 3=newbie, 4=port, 5=planet' . EOL;
		foreach (WeaponType::getAllWeaponTypes() as $weapon) {
			$file .= inify($weapon->getName()) . '=' . inify($weapon->getRaceName()) . ',' . $weapon->getCost() . ',' . $weapon->getShieldDamage() . ',' . $weapon->getArmourDamage() . ',' . $weapon->getAccuracy() . ',' . $weapon->getPowerLevel() . ',' . $weapon->getBuyerRestriction()->value . EOL;
		}

		$file .= '[ShipEquipment]
		; Name = Cost' . EOL;
		foreach (HardwareType::getAll() as $hardware) {
			$file .= inify($hardware->name) . '=' . $hardware->cost . EOL;
		}

		$file .= '[Ships]
		; Name = Race,Cost,TPH,Hardpoints,Power,Class,+Equipment (Optional),+Restrictions(Optional)
		; Restrictions:Align(Integer)' . EOL;
		foreach (ShipType::getAll() as $ship) {
			$file .= inify($ship->getName()) . '=' . inify($ship->getRaceName()) . ',' . $ship->getCost() . ',' . $ship->getSpeed() . ',' . $ship->getHardpoints() . ',' . $ship->getMaxPower() . ',' . $ship->getClass()->name;
			$shipEquip = [];
			foreach ($ship->getAllMaxHardware() as $hardwareID => $maxHardware) {
				$shipEquip[] = HardwareType::get($hardwareID)->name . '=' . $maxHardware;
			}
			if (count($shipEquip) > 0) {
				$file .= ',ShipEquipment=' . implode(';', $shipEquip);
			}
			$file .= ',Restrictions=' . $ship->getRestriction()->value;
			$file .= EOL;
		}

		$file .= '[Locations]
		; Name = +Sells' . EOL;
		foreach (Location::getAllLocations($gameID) as $location) {
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
					$locSells .= $locHardware->name . ';';
				}
				$locSells = substr($locSells, 0, -1) . ',';
			}
			if ($location->isShipSold()) {
				$locSells .= 'Ships=';
				foreach ($location->getShipsSold() as $locShip) {
					$locSells .= $locShip->getName() . ';';
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
			if ($locSells !== '') {
				$file .= substr($locSells, 0, -1);
			}
			$file .= EOL;
		}

		// Everything below here must be valid INI syntax (safe to parse)
		$game = Game::getGame($gameID);
		$file .= '[Metadata]
		FileVersion=' . SMR_FILE_VERSION . '
		[Game]
		Name=' . inify($game->getName()) . '
		[Galaxies]
		';
		$galaxies = $game->getGalaxies();
		foreach ($galaxies as $galaxy) {
			$file .= $galaxy->getGalaxyID() . '=' . $galaxy->getWidth() . ',' . $galaxy->getHeight() . ',' . $galaxy->getGalaxyType() . ',' . inify($galaxy->getName()) . ',' . $galaxy->getMaxForceTime() . EOL;
		}

		foreach ($galaxies as $galaxy) {
			// Efficiently construct the caches before proceeding
			$galaxy->getLocations();
			$galaxy->getPlanets();
			$galaxy->getForces();

			foreach ($galaxy->getSectors() as $sector) {
				$file .= '[Sector=' . $sector->getSectorID() . ']' . EOL;

				if (!$sector->isVisited($player) && $adminCreate === false) {
					continue;
				}

				foreach ($sector->getLinks() as $linkName => $link) {
					$file .= $linkName . '=' . $link . EOL;
				}
				if ($sector->hasWarp()) {
					$file .= 'Warp=' . $sector->getWarp() . EOL;
				}
				$port = null;
				if ($adminCreate && $sector->hasPort()) {
					$port = $sector->getPort();
				} elseif ($sector->hasCachedPort($player)) {
					$port = $sector->getCachedPort($player);
				}
				if ($port !== null) {
					$file .= 'Port Level=' . $port->getLevel() . EOL;
					$file .= 'Port Race=' . $port->getRaceID() . EOL;
					if (count($port->getSellGoodIDs()) > 0) {
						$file .= 'Buys=' . implode(',', $port->getSellGoodIDs()) . EOL;
					}
					if (count($port->getBuyGoodIDs()) > 0) {
						$file .= 'Sells=' . implode(',', $port->getBuyGoodIDs()) . EOL;
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
						$forcesString .= inify($forces->getOwner()->getPlayerName()) . '=' . inify(HardwareType::get(HARDWARE_MINE)->name) . '=' . $forces->getMines() . ';' . inify(HardwareType::get(HARDWARE_COMBAT)->name) . '=' . $forces->getCDs() . ';' . inify(HardwareType::get(HARDWARE_SCOUT)->name) . '=' . $forces->getSDs() . ',';
					}
					$file .= substr($forcesString, 0, -1) . EOL;
				}
			}
			Port::clearCache();
			Force::clearCache();
			Planet::clearCache();
			Sector::clearCache();
		}

		$size = strlen($file);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="' . $game->getName() . '.smr"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $size);

		echo $file;

		exit;
	}

}
