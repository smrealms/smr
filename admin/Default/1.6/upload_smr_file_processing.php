<?php declare(strict_types=1);

if ($_FILES['smr_file']['error'] == UPLOAD_ERR_OK) {
	$ini_str = file_get_contents($_FILES['smr_file']['tmp_name']);
} else {
	create_error('Failed to upload SMR file!');
}

// We only care about the sections after [Metadata], and the earlier
// sections have invalid INI key characters (e.g. Creonti "Big Daddy", Salvene
// Supply & Plunder). For this reason, we simply remove the offending sections
// instead of trying to encode all the special characters: ?{}|&~![()^"
//
// NOTE: these special characters are allowed in the ini-values, but only if
// we use the "raw" scanner. We need this because of the "Location=" values.
$ini_substr = strstr($ini_str, "[Metadata]");
if ($ini_substr === false) {
	create_error('Could not find [Metadata] section in SMR file');
}
$data = parse_ini_string($ini_substr, true, INI_SCANNER_RAW);

$version = $data['Metadata']['FileVersion'];
if ($version !== SMR_FILE_VERSION) {
	create_error('Uploaded v' . $version . ' is incompatible with server expecting v' . SMR_FILE_VERSION);
}

// Create the galaxies
foreach ($data['Galaxies'] as $galID => $details) {
	list($width, $height, $type, $name, $maxForceTime) = explode(',', $details);
	$galaxy = SmrGalaxy::createGalaxy($var['game_id'], $galID);
	$galaxy->setWidth($width);
	$galaxy->setHeight($height);
	$galaxy->setGalaxyType($type);
	$galaxy->setName($name);
	$galaxy->setMaxForceTime($maxForceTime);
}
// Workaround for SmrGalaxy::getStartSector depending on all other galaxies
SmrGalaxy::saveGalaxies();
foreach (SmrGalaxy::getGameGalaxies($var['game_id'], true) as $galaxy) {
	$galaxy->generateSectors();
}

// Populate the sectors
foreach ($data as $key => $vals) {
	if (!preg_match('/^Sector=(\d+)$/', $key, $matches)) {
		continue;
	}

	$sectorID = $matches[1];
	$editSector = SmrSector::getSector($var['game_id'], $sectorID);

	// Sector connections (we assume link sectors are correct)
	foreach (['Up', 'Down', 'Left', 'Right'] as $dir) {
		if (isset($vals[$dir])) {
			$editSector->enableLink($dir);
		} else {
			$editSector->disableLink($dir);
		}
	}

	// Ports
	if (isset($vals['Port Level'])) {
		$port = $editSector->createPort();
		$port->setRaceID($vals['Port Race']);
		$port->setLevel($vals['Port Level']);
		$port->setCreditsToDefault();
		// SMR file indicates the port action Buys/Sells,
		// but SmrPort::addPortGood uses the player action.
		if (isset($vals['Buys'])) {
			foreach (explode(',', $vals['Buys']) as $goodID) {
				$port->addPortGood($goodID, 'Sell');
			}
		}
		if (isset($vals['Sells'])) {
			foreach (explode(',', $vals['Sells']) as $goodID) {
				$port->addPortGood($goodID, 'Buy');
			}
		}
	}

	// Locations
	$allLocs = SmrLocation::getAllLocations();
	if (isset($vals['Locations'])) {
		$locNames = explode(',', $vals['Locations']);
		foreach ($locNames as $locName) {
			// Since we only know the location name, we must search for it
			$found = false;
			foreach ($allLocs as $loc) {
				if ($locName == inify($loc->getName())) {
					$editSector->addLocation($loc);
					$found = true;
					break;
				}
			}
			if (!$found) {
				create_error('Could not find location named: ' . $locName);
			}
		}
	}

	// Warps
	if (isset($vals['Warp'])) {
		$editSector->setWarp(SmrSector::getSector($var['game_id'], $vals['Warp']));
	}

	// Planets
	if (isset($vals['Planet'])) {
		$editSector->createPlanet($vals['Planet']);
	}
}

// Save so that sector links and ports persist
// (otherwise they are overwritten somewhere while forwarding)
SmrSector::saveSectors();
SmrPort::savePorts();

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
forward($container);
