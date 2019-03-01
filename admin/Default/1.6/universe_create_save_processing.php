<?php

$submit = isset($var['submit']) ? $var['submit'] : (isset($_REQUEST['submit'])?$_REQUEST['submit']:'');
unset($var['submit']);

if ($submit=='Create Galaxies') {
	for ($i=1;$i<=$var['num_gals'];$i++) {
		$galaxy = SmrGalaxy::createGalaxy($var['game_id'],$i);
		$galaxy->setName($_POST['gal' . $i]);
		$galaxy->setWidth($_POST['width' . $i]);
		$galaxy->setHeight($_POST['height' . $i]);
		$galaxy->setGalaxyType($_POST['type' . $i]);
		$galaxy->setMaxForceTime($_POST['forces' . $i] * 3600);
	}
	SmrGalaxy::saveGalaxies();
	$galaxies = SmrGalaxy::getGameGalaxies($var['game_id'],true);
	foreach ($galaxies as $galaxy) {
		$galaxy->generateSectors();
	}
	SmrSector::saveSectors();
	$var['message'] = '<span class="green">Success</span> : Succesfully created galaxies.';
}
else if ($submit=='Redo Connections') {
	$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
	if(!$galaxy->setConnectivity($_REQUEST['connect']))
		$var['message'] = '<span class="red">Error</span> : Regenerating connections failed.';
	else
		$var['message'] = '<span class="green">Success</span> : Regenerated connections.';
	SmrSector::saveSectors();
}
elseif ($submit == 'Toggle Link') {
	$linkSector = SmrSector::getSector($var['game_id'],$var['sector_id']);
	$linkSector->toggleLink($var['dir']);
	SmrSector::saveSectors();
}
elseif ($submit == 'Modify Sector') {
	if(!empty($_POST['sector_edit'])) {
		$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
		if($galaxy->contains($_POST['sector_edit'])) {
			$var['sector_id'] = $_POST['sector_edit'];
			$var['body'] = '1.6/universe_create_sector_details.php';
		}
		else
			$var['message'] = '<span class="red">Error</span> : That sector does not exist in this galaxy.';
	}
}
elseif ($submit == 'Create Locations') {
	$galSectors = SmrSector::getGalaxySectors($var['game_id'],$var['gal_on']);
	foreach ($galSectors as $galSector) {
		$galSector->removeAllLocations();
	}
	foreach (SmrLocation::getAllLocations() as $location) {
		if (isset($_POST['loc' . $location->getTypeID()])) {
			for ($i=0;$i<$_POST['loc' . $location->getTypeID()];$i++) {
				$randSector = $galSectors[array_rand($galSectors)]; //get random sector from start of gal to end of gal
				//4 per sector max locs and no locations inside fed
				
				while (!checkSectorAllowedForLoc($randSector, $location)) {
					$randSector = $galSectors[array_rand($galSectors)]; //get valid sector
				}
				
				addLocationToSector($location, $randSector);
			}
		}
	}
	$var['message'] = '<span class="green">Success</span> : Succesfully added locations.';
}
elseif ($submit == 'Create Warps') {
	//get all warp info from all gals, some need to be removed, some need to be added
	$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
	$galSectors = $galaxy->getSectors();
	//get totals
	foreach ($galSectors as $galSector) {
		if($galSector->hasWarp()) {
			$galSector->removeWarp();
		}
	}
	//iterate over all the galaxies
	$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
	foreach ($galaxies as $eachGalaxy) {
		//do we have a warp to this gal?
		if (isset($_POST['warp' . $eachGalaxy->getGalaxyID()])) {
			// Sanity check the number
			$numWarps = $_POST['warp' . $eachGalaxy->getGalaxyID()];
			if ($numWarps > 10) {
				create_error('Specify no more than 10 warps between two galaxies!');
			}
			//iterate for each warp to this gal
			for ($i=1; $i<=$numWarps; $i++) {
				$galSector = $galSectors[array_rand($galSectors)];
				//only 1 warp per sector
				while ($galSector->hasWarp() || $galSector->offersFederalProtection()) {
					$galSector = $galSectors[array_rand($galSectors)];
				}
				//get other side
				$otherSectors = $eachGalaxy->getSectors();
				$otherSector = $otherSectors[array_rand($otherSectors)];
				//make sure it does not go to itself
				while ($otherSector->hasWarp() || $otherSector->offersFederalProtection() || $otherSector->equals($galSector)) {
					$otherSector = $otherSectors[array_rand($otherSectors)];
				}
				$galSector->setWarp($otherSector);
			}
		}
	}
	SmrSector::saveSectors();
	$var['message'] = '<span class="green">Success</span> : Succesfully added warps.';
}
elseif ($submit == 'Create Planets') {
	$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
	$galSectors = $galaxy->getSectors();
	foreach ($galSectors as $galSector) {
		if($galSector->hasPlanet()) {
			$galSector->removePlanet();
		}
	}
//	$numberOfNpcPlanets = $_POST['NPC'];

	foreach (array_keys(SmrPlanetType::PLANET_TYPES) as $planetTypeID) {
		$numberOfPlanets = $_POST['type' . $planetTypeID];
		for ($i=1;$i<=$numberOfPlanets;$i++) {
			$galSector = $galSectors[array_rand($galSectors)];
			while ($galSector->hasPlanet()) $galSector = $galSectors[array_rand($galSectors)]; //1 per sector
			$galSector->createPlanet($planetTypeID);
		}
	}
	$var['message'] = '<span class="green">Success</span> : Succesfully added planets.';
}
elseif ($submit == 'Create Ports') {
	$totalPorts=0;
	for ($i=1; $i<=SmrPort::MAX_LEVEL; $i++) {
		$totalPorts+=$_REQUEST['port' . $i];
	}

	$totalRaceDist=0;
	$numRacePorts = array();
	foreach (Globals::getRaces() as $race) {
		$racePercent = $_REQUEST['race' . $race['Race ID']];
		if (!empty($racePercent)) {
			$totalRaceDist += $racePercent;
			$numRacePorts[$race['Race ID']] = ceil($racePercent / 100 * $totalPorts);
		}
	}
	$assignedPorts = array_sum($numRacePorts);
	if ($totalRaceDist == 100 || $totalPorts == 0) {
		$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
		$galSectors = $galaxy->getSectors();
		foreach ($galSectors as $galSector) {
			if($galSector->hasPort()) {
				$galSector->removePort();
			}
		}
		//get race for all ports
		while ($totalPorts > $assignedPorts) {
			//this adds extra ports until we reach the requested #
			$numRacePorts[array_rand($numRacePorts)]++;
			$assignedPorts++;
		}
		//iterate through levels 1-9 port
		for ($i=1; $i<=SmrPort::MAX_LEVEL; $i++) {
			//iterate once for each port of this level
			for ($j=0;$j<$_REQUEST['port' . $i];$j++) {
				//get a sector for this port
				$galSector = $galSectors[array_rand($galSectors)];
				//check if this sector is valid, if not then get a new one
				while ($galSector->hasPort() || $galSector->offersFederalProtection()) $galSector = $galSectors[array_rand($galSectors)];

				$raceID = array_rand($numRacePorts);
				$numRacePorts[$raceID]--;
				if($numRacePorts[$raceID]==0)
					unset($numRacePorts[$raceID]);
					
				$port = $galSector->createPort();
				$port->setRaceID($raceID);
				$port->upgradeToLevel($i);
				$port->setCreditsToDefault();
			}
		}
		SmrPort::savePorts();
		$var['message'] = '<span class="green">Success</span> : Succesfully added ports.';
	}
	else {
		$var['message'] = '<span class="red">Error: Your port race distribution must equal 100!</span>';
	}
}
elseif ($submit == 'Edit Sector') {
	$editSector = SmrSector::getSector($var['game_id'],$var['sector_id']);

	//update planet
	if ($_POST['plan_type'] != '0') {
		if (!$editSector->hasPlanet()) {
			$editSector->createPlanet($_POST['plan_type']);
		}
		else {
			$type = $editSector->getPlanet()->getTypeID();
			if ($_POST['plan_type'] != $type) {
				$editSector->getPlanet()->setTypeID($_POST['plan_type']);
			}
		}
	}
	
//	elseif ($_POST['plan_type'] == 'NPC') {
//		$GAL_PLANETS[$this_sec]['Inhabitable'] = 1;
//		$GAL_PLANETS[$this_sec]['Owner'] = 0;
//		$GAL_PLANETS[$this_sec]['Owner Type'] = 'NPC';
//	}
	else {
		$editSector->removePlanet();
	}
	//update port
	if ($_POST['port_level'] > 0) {
		if(!$editSector->hasPort()) {
			$port = $editSector->createPort();
		}
		else {
			$port = $editSector->getPort();
		}
		if ($port->getLevel()!=$_POST['port_level']) {
			$port->upgradeToLevel($_POST['port_level']);
			$port->setCreditsToDefault();
		}
		$port->setRaceID($_POST['port_race']);
		$port->update();
	} else $editSector->removePort();
	//update locations
	
	$locationsToAdd = array();
	$locationsToKeep = array();
	for($x=0;$x<UNI_GEN_LOCATION_SLOTS;$x++) {
		if ($_POST['loc_type'.$x] != 0) {
			$locationToAdd = SmrLocation::getLocation($_POST['loc_type'.$x]);
			if($editSector->hasLocation($locationToAdd->getTypeID()))
				$locationsToKeep[] = $locationToAdd;
			else
				$locationsToAdd[] = $locationToAdd;
		}
	}
	$editSector->removeAllLocations();
	foreach($locationsToKeep as $locationToAdd) {
		$editSector->addLocation($locationToAdd);
	}
	foreach($locationsToAdd as $locationToAdd) {
		addLocationToSector($locationToAdd,$editSector);
	}
	if ($_POST['warp'] > 0) {
		$warp = SmrSector::getSector($var['game_id'], $_POST['warp']);
		if ($editSector->equals($warp)) {
			create_error('We do not allow any sector to warp to itself!');
		}
		// Removing warps first may do extra work, but is logically simpler
		$warp->removeWarp();
		$editSector->removeWarp();
		$editSector->setWarp($warp);
	} else {
		$editSector->removeWarp();
	}
	$var['message'] = '<span class="green">Success</span> : Succesfully edited sector.';
	SmrSector::saveSectors();
}

$container = $var;
$container['url'] = 'skeleton.php';
forward($container);


function checkSectorAllowedForLoc(SmrSector $sector, SmrLocation $location) {
	if (!$location->isHQ()) {
		return (sizeof($sector->getLocations()) < 4 && !$sector->offersFederalProtection() );
	}
	else {
		//HQs are here
		//find a sector where there are no locations yet
		return !$sector->hasLocation();
	}
}

function addLocationToSector(SmrLocation $location, SmrSector $sector) {
	if($sector->hasLocation($location->getTypeID()))
		return false;

	$sector->addLocation($location); //insert the location
	if ($location->isHQ()) {
		//only playable races have extra locations to add
		//Racial/Fed
		foreach ($location->getLinkedLocations() as $linkedLocation) {
			$sector->addLocation($linkedLocation);
			if($linkedLocation->isFed())
				$fedBeacon = $linkedLocation;
		}
			
		//add Beacons to all surrounding areas (up to 2 sectors out)
		if (!$sector->offersFederalProtection())
			$sector->addLocation($fedBeacon); //add beacon to this sector
		$visitedSectors = array();
		$links = array('Up','Right','Down','Left');
		$fedSectors = array($sector);
		$tempFedSectors = array();
		for($i=0;$i<DEFAULT_FED_RADIUS;$i++) {
			foreach($fedSectors as $fedSector) {
				foreach($links as $link) {
					if ($fedSector->hasLink($link) && !isset($visitedSectors[$fedSector->getLink($link)])) {
						$linkSector = $sector->getLinkSector($link);
						if (!$linkSector->offersFederalProtection())
							$linkSector->addLocation($fedBeacon); //add beacon to this sector
						$tempFedSectors[] = $linkSector;
						$visitedSectors[$fedSector->getLink($link)] = true;
					}
				}
			}
			$fedSectors = $tempFedSectors;
			$tempFedSectors = array();
		}
	}
	return true;
}
