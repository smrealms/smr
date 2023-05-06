<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Exception;
use Smr\Account;
use Smr\Galaxy;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\Port;
use Smr\Sector;
use Smr\TransactionType;

class UploadSmrFileProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(Account $account): never {
		if ($_FILES['smr_file']['error'] !== UPLOAD_ERR_OK) {
			create_error('Failed to upload SMR file!');
		}

		$ini_str = file_get_contents($_FILES['smr_file']['tmp_name']);
		if ($ini_str === false) {
			throw new Exception('Failed to read temporary file');
		}

		// We only care about the sections after [Metadata], and the earlier
		// sections have invalid INI key characters (e.g. Creonti "Big Daddy", Salvene
		// Supply & Plunder). For this reason, we simply remove the offending sections
		// instead of trying to encode all the special characters: ?{}|&~![()^"
		//
		// NOTE: these special characters are allowed in the ini-values, but only if
		// we use the "raw" scanner. We need this because of the "Location=" values.
		$ini_substr = strstr($ini_str, '[Metadata]');
		if ($ini_substr === false) {
			create_error('Could not find [Metadata] section in SMR file');
		}
		$data = parse_ini_string($ini_substr, true, INI_SCANNER_RAW);
		if ($data === false) {
			create_error('Failed to parse SMR file. Please check the file for errors.');
		}

		$version = $data['Metadata']['FileVersion'];
		if ($version !== SMR_FILE_VERSION) {
			create_error('Uploaded v' . $version . ' is incompatible with server expecting v' . SMR_FILE_VERSION);
		}

		// Create the galaxies
		foreach ($data['Galaxies'] as $galID => $details) {
			[$width, $height, $type, $name, $maxForceTime] = explode(',', $details);
			$galaxy = Galaxy::createGalaxy($this->gameID, $galID);
			$galaxy->setWidth(str2int($width));
			$galaxy->setHeight(str2int($height));
			$galaxy->setGalaxyType($type);
			$galaxy->setName($name);
			$galaxy->setMaxForceTime(str2int($maxForceTime));
		}
		// Workaround for Galaxy::getStartSector depending on all other galaxies
		Galaxy::saveGalaxies();
		foreach (Galaxy::getGameGalaxies($this->gameID, true) as $galaxy) {
			$galaxy->generateSectors();
		}

		// Populate the sectors
		foreach ($data as $key => $vals) {
			if (!preg_match('/^Sector=(\d+)$/', $key, $matches)) {
				continue;
			}

			$sectorID = str2int($matches[1]);
			$editSector = Sector::getSector($this->gameID, $sectorID);

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
				$port->setRaceID(str2int($vals['Port Race']));
				$port->setLevel(str2int($vals['Port Level']));
				$port->setCreditsToDefault();
				// SMR file indicates the port action Buys/Sells,
				// but Port::addPortGood uses the player action.
				if (isset($vals['Buys'])) {
					foreach (explode(',', $vals['Buys']) as $goodID) {
						$port->addPortGood(str2int($goodID), TransactionType::Sell);
					}
				}
				if (isset($vals['Sells'])) {
					foreach (explode(',', $vals['Sells']) as $goodID) {
						$port->addPortGood(str2int($goodID), TransactionType::Buy);
					}
				}
			}

			// Locations
			$allLocs = Location::getAllLocations($this->gameID);
			if (isset($vals['Locations'])) {
				$locNames = explode(',', $vals['Locations']);
				foreach ($locNames as $locName) {
					// Since we only know the location name, we must search for it
					$found = false;
					foreach ($allLocs as $loc) {
						if ($locName === inify($loc->getName())) {
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
				$editSector->setWarp(Sector::getSector($this->gameID, str2int($vals['Warp'])));
			}

			// Planets
			if (isset($vals['Planet'])) {
				$editSector->createPlanet(str2int($vals['Planet']));
			}
		}

		// Save so that sector links and ports persist
		// (otherwise they are overwritten somewhere while forwarding)
		Sector::saveSectors();
		Port::savePorts();

		$container = new EditGalaxy($this->gameID);
		$container->go();
	}

}
