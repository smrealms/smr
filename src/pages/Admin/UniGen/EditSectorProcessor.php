<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Location;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;
use Smr\TradeGood;
use Smr\TransactionType;

class EditSectorProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $sectorID,
		private readonly EditSector $returnTo,
	) {}

	public function build(Account $account): never {
		$editSector = Sector::getSector($this->gameID, $this->sectorID);

		//update planet
		$planetTypeID = Request::getInt('plan_type');
		if ($planetTypeID === 0) {
			$editSector->removePlanet();
		} elseif (!$editSector->hasPlanet()) {
			$editSector->createPlanet($planetTypeID);
		} else {
			$editSector->getPlanet()->setTypeID($planetTypeID);
		}

		//update port
		$portLevel = Request::getInt('port_level');
		if ($portLevel > 0) {
			if (!$editSector->hasPort()) {
				$port = $editSector->createPort();
			} else {
				$port = $editSector->getPort();
			}
			$port->setRaceID(Request::getInt('port_race'));
			if ($port->getLevel() !== $portLevel) {
				$port->upgradeToLevel($portLevel);
				$port->setCreditsToDefault();
			} elseif (Request::has('select_goods')) {
				// Only set the goods manually if the level hasn't changed
				$goodTransactions = [];
				foreach (TradeGood::getAllIDs() as $goodID) {
					$trans = Request::get('good' . $goodID);
					if ($trans !== 'None') {
						$goodTransactions[$goodID] = TransactionType::from($trans);
					}
				}
				if (!$port->setPortGoods($goodTransactions)) {
					create_error('Invalid goods specified for this port level!');
				}
			}
			$port->update();
		} else {
			$editSector->removePort();
		}

		//update locations
		$locationsToAdd = [];
		for ($x = 0; $x < UNI_GEN_LOCATION_SLOTS; $x++) {
			if (Request::getInt('loc_type' . $x) !== 0) {
				$locationTypeID = Request::getInt('loc_type' . $x);
				$locationsToAdd[] = Location::getLocation($this->gameID, $locationTypeID);
			}
		}
		$editSector->removeAllLocations();
		foreach ($locationsToAdd as $locationToAdd) {
			$editSector->addLocation($locationToAdd);
			if (Request::has('add_linked_locs')) {
				$editSector->addLinkedLocations($locationToAdd);
			}
		}

		// update warp
		$warpSectorID = Request::getInt('warp');
		if ($warpSectorID > 0) {
			$warp = Sector::getSector($this->gameID, $warpSectorID);
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
		Sector::saveSectors();

		$this->returnTo->message = '<span class="green">Success</span> : edited sector.';
		$this->returnTo->go();
	}

}
