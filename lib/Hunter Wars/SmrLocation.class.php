<?php declare(strict_types=1);

class SmrLocation extends AbstractSmrLocation {

	public function &getShipsSold() {
		if (!isset($this->shipsSold)) {
			$this->shipsSold = array();
			$this->db->query('SELECT * FROM location_sells_ships
			                  JOIN ship_type USING (ship_type_id)
			                  WHERE ' . $this->SQL . '
			                    AND ship_class_id != ' . $this->db->escapeNumber(SmrShip::SHIP_CLASS_RAIDER) . '
			                    AND ship_type_id != ' . $this->db->escapeNumber(SHIP_TYPE_PLANETARY_SUPER_FREIGHTER));
			while ($this->db->nextRecord()) {
				$shipTypeID = $this->db->getInt('ship_type_id');
				$this->shipsSold[$shipTypeID] = AbstractSmrShip::getBaseShip(Globals::getGameType(SmrSession::getGameID()), $shipTypeID);
			}
		}
		return $this->shipsSold;
	}

}
