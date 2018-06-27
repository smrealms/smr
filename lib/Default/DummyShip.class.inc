<?php
require_once('AbstractSmrShip.class.inc');

class DummyShip extends AbstractSmrShip {
	protected static $CACHED_DUMMY_SHIPS;
	
	public function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		
		$this->weapons = array();
		$this->hardware = array();
		$this->oldHardware = array();
		$this->cargo = array();
		
		$this->regenerate($player);
		$this->doFullUNO();
		
		$this->cargo_left = $this->getCargoHolds();
	}
	protected function doFullUNO() {
		foreach($this->getMaxHardware() as $hardwareTypeID => $max) {
			$this->hardware[$hardwareTypeID] = $max;
			$this->oldHardware[$hardwareTypeID] = $max;
		}
	}
	
	public function regenerate(AbstractSmrPlayer $player) {
		$this->player = $player;
		$this->regenerateBaseShip();
		$this->doFullUNO();
//		for($i=0;$i<$this->getHardpoints();++$i) {
//			if(!isset($this->weapons[$i]))
//				$this->weapons[$i] = SmrWeapon::getWeapon(1);
//		}
		$this->checkForExcessWeapons();
	}
	
	function decloak() {
	}
	
	function enableCloak() {
	}
	
	function setIllusion($ship_id, $attack, $defense) {
	}
	
	function disableIllusion() {
	}
	
	public function getIllusionShip() {
		if(!isset($this->illusionShip)) {
			$this->illusionShip=false;
		}
		return $this->illusionShip;
	}
	
	public function cacheDummyShip() {
		$cache = serialize($this);
		$db = new SmrMySqlDatabase();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyShip\', '.$db->escapeString($this->getPlayer()->getPlayerName()).', '.$db->escapeString($cache).')');
		unserialize($cache);
	}
	
	public static function &getCachedDummyShip(AbstractSmrPlayer $player) {
		if(!isset(self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT info FROM cached_dummys
						WHERE type = \'DummyShip\'
						AND id = ' . $db->escapeString($player->getPlayerName()) . ' LIMIT 1');
			if($db->nextRecord()) {
				$return = unserialize($db->getField('info'));
				$return->regenerate($player);
				self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()] =& $return;
			}
			else {
				self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()] = new DummyShip($player);
			}
		}
		return self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()];
	}
	
	public static function &getDummyShipNames() {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT id FROM cached_dummys
					WHERE type = \'DummyShip\'');
		$dummyNames = array();
		while($db->nextRecord()) {
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}
	
	
	
	public function __sleep() {
		return array('gameID','weapons');
	}
	
	public function __wakeup() {
	}
}
