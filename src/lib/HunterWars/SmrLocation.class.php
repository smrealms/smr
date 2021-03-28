<?php declare(strict_types=1);

class SmrLocation extends AbstractSmrLocation {

	public function getShipsSold() : array {
		if (!isset($this->shipsSold)) {
			$this->shipsSold = array();
			$this->db->query('SELECT * FROM location_sells_ships
			                  JOIN ship_type USING (ship_type_id)
			                  WHERE ' . $this->SQL . '
			                    AND ship_class_id != ' . $this->db->escapeNumber(Smr\ShipClass::RAIDER) . '
			                    AND ship_type_id != ' . $this->db->escapeNumber(SHIP_TYPE_PLANETARY_SUPER_FREIGHTER));
			while ($this->db->nextRecord()) {
				$shipTypeID = $this->db->getInt('ship_type_id');
				$this->shipsSold[$shipTypeID] = SmrShipType::get($shipTypeID, $this->db);
			}
		}
		return $this->shipsSold;
	}

}
