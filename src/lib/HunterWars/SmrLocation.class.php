<?php declare(strict_types=1);

class SmrLocation extends AbstractSmrLocation {

	public function getShipsSold() : array {
		if (!isset($this->shipsSold)) {
			// Generate the full ship list from the base class
			parent::getShipsSold();
			// Remove ships that are not allowed in Hunter Wars
			unset($this->shipsSold[SHIP_TYPE_PLANETARY_SUPER_FREIGHTER]);
			foreach ($this->shipsSold as $shipID => $ship) {
				if ($ship->getClassID() === Smr\ShipClass::RAIDER) {
					unset($this->shipsSold[$shipID]);
				}
			}
		}
		return $this->shipsSold;
	}

}
