<?php declare(strict_types=1);

class SmrSector {

	protected static array $CACHE_SECTORS = [];
	protected static array $CACHE_GALAXY_SECTORS = [];
	protected static array $CACHE_LOCATION_SECTORS = [];

	protected Smr\Database $db;
	protected string $SQL;

	protected int $gameID;
	protected int $sectorID;
	protected int $battles;
	protected int $galaxyID;
	protected array $visited = [];
	protected array $links = [];
	protected int $warp;

	protected bool $hasChanged = false;
	protected bool $isNew = false;

	/**
	 * Maps the SmrSector link direction names to database columns.
	 */
	protected const LINK_DIR_MAPPING = [
		'Up' => 'link_up',
		'Down' => 'link_down',
		'Left' => 'link_left',
		'Right' => 'link_right',
	];

	/**
	 * Constructs the sector to determine if it exists.
	 * Returns a boolean value.
	 */
	public static function sectorExists(int $gameID, int $sectorID): bool {
		try {
			self::getSector($gameID, $sectorID);
			return true;
		} catch (Smr\Exceptions\SectorNotFound) {
			return false;
		}
	}

	public static function getGalaxySectors(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID])) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id=' . $db->escapeNumber($galaxyID) . ' ORDER BY sector_id ASC');
			$sectors = [];
			foreach ($dbResult->records() as $dbRecord) {
				$sectorID = $dbRecord->getInt('sector_id');
				$sectors[$sectorID] = self::getSector($gameID, $sectorID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID] = $sectors;
		}
		return self::$CACHE_GALAXY_SECTORS[$gameID][$galaxyID];
	}

	public static function getLocationSectors(int $gameID, int $locationTypeID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID])) {
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT * FROM location JOIN sector USING (game_id, sector_id) WHERE location_type_id = ' . $db->escapeNumber($locationTypeID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY sector_id ASC');
			$sectors = [];
			foreach ($dbResult->records() as $dbRecord) {
				$sectorID = $dbRecord->getInt('sector_id');
				$sectors[$sectorID] = self::getSector($gameID, $sectorID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID] = $sectors;
		}
		return self::$CACHE_LOCATION_SECTORS[$gameID][$locationTypeID];
	}

	public static function getSector(int $gameID, int $sectorID, bool $forceUpdate = false, Smr\DatabaseRecord $dbRecord = null): self {
		if (!isset(self::$CACHE_SECTORS[$gameID][$sectorID]) || $forceUpdate) {
			self::$CACHE_SECTORS[$gameID][$sectorID] = new self($gameID, $sectorID, false, $dbRecord);
		}
		return self::$CACHE_SECTORS[$gameID][$sectorID];
	}

	public static function clearCache(): void {
		self::$CACHE_LOCATION_SECTORS = [];
		self::$CACHE_GALAXY_SECTORS = [];
		self::$CACHE_SECTORS = [];
	}

	public static function saveSectors(): void {
		foreach (self::$CACHE_SECTORS as $gameSectors) {
			foreach ($gameSectors as $sector) {
				$sector->update();
			}
		}
	}

	public static function createSector(int $gameID, int $sectorID): self {
		if (!isset(self::$CACHE_SECTORS[$gameID][$sectorID])) {
			$s = new self($gameID, $sectorID, true);
			self::$CACHE_SECTORS[$gameID][$sectorID] = $s;
		}
		return self::$CACHE_SECTORS[$gameID][$sectorID];
	}

	protected function __construct(int $gameID, int $sectorID, bool $create = false, Smr\DatabaseRecord $dbRecord = null) {
		$this->db = Smr\Database::getInstance();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . ' AND sector_id = ' . $this->db->escapeNumber($sectorID);

		// Do we already have a database record for this sector?
		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM sector WHERE ' . $this->SQL . ' LIMIT 1');
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		$sectorExists = $dbRecord !== null;

		$this->gameID = $gameID;
		$this->sectorID = $sectorID;

		if ($sectorExists) {
			$this->galaxyID = $dbRecord->getInt('galaxy_id');
			$this->battles = $dbRecord->getInt('battles');

			foreach (self::LINK_DIR_MAPPING as $dir => $dbColumn) {
				$link = $dbRecord->getInt($dbColumn);
				if ($link !== 0) {
					$this->links[$dir] = $link;
				}
			}
			$this->warp = $dbRecord->getInt('warp');
		} elseif ($create) {
			$this->battles = 0;
			$this->warp = 0;
			$this->isNew = true;
		} else {
			throw new Smr\Exceptions\SectorNotFound('No sector ' . $sectorID . ' in game ' . $gameID);
		}
	}

	public function update(): void {
		if ($this->isNew) {
			$this->db->insert('sector', [
				'sector_id' => $this->db->escapeNumber($this->getSectorID()),
				'game_id' => $this->db->escapeNumber($this->getGameID()),
				'galaxy_id' => $this->db->escapeNumber($this->getGalaxyID()),
				'link_up' => $this->db->escapeNumber($this->getLinkUp()),
				'link_down' => $this->db->escapeNumber($this->getLinkDown()),
				'link_left' => $this->db->escapeNumber($this->getLinkLeft()),
				'link_right' => $this->db->escapeNumber($this->getLinkRight()),
				'warp' => $this->db->escapeNumber($this->getWarp()),
			]);
		} elseif ($this->hasChanged) {
			$this->db->write('UPDATE sector SET battles = ' . $this->db->escapeNumber($this->getBattles()) .
									', galaxy_id=' . $this->db->escapeNumber($this->getGalaxyID()) .
									', link_up=' . $this->db->escapeNumber($this->getLinkUp()) .
									', link_right=' . $this->db->escapeNumber($this->getLinkRight()) .
									', link_down=' . $this->db->escapeNumber($this->getLinkDown()) .
									', link_left=' . $this->db->escapeNumber($this->getLinkLeft()) .
									', warp=' . $this->db->escapeNumber($this->getWarp()) .
								' WHERE ' . $this->SQL . ' LIMIT 1');
		}
		$this->isNew = false;
		$this->hasChanged = false;
	}

	public function markVisited(AbstractSmrPlayer $player): void {
		if ($this->hasPort()) {
			$this->getPort()->addCachePort($player->getAccountID());
		}

		//now delete the entry from visited
		if (!$this->isVisited($player)) {
			$this->db->write('DELETE FROM player_visited_sector WHERE ' . $this->SQL . '
								 AND account_id = ' . $this->db->escapeNumber($player->getAccountID()) . ' LIMIT 1');
		}
		$this->visited[$player->getAccountID()] = true;
	}

	public function offersFederalProtection(): bool {
		foreach ($this->getLocations() as $location) {
			if ($location->isFed()) {
				return true;
			}
		}
		return false;
	}

	public function getFedRaceIDs(): array {
		$raceIDs = [];
		foreach ($this->getLocations() as $location) {
			if ($location->isFed()) {
				$raceIDs[$location->getRaceID()] = $location->getRaceID();
			}
		}
		return $raceIDs;
	}

	public function enteringSector(AbstractSmrPlayer $player, int $movementType): void {
		// send scout messages to user
		$message = 'Your forces have spotted ' . $player->getBBLink() . ' ';
		$message .= match ($movementType) {
			MOVEMENT_JUMP => 'jumping into',
			MOVEMENT_WARP => 'warping into',
			MOVEMENT_WALK => 'entering',
		};
		$message .= ' sector ' . Globals::getSectorBBLink($this->getSectorID());

		$forces = $this->getForces();
		foreach ($forces as $force) {
			$force->ping($message, $player);
		}
	}

	public function leavingSector(AbstractSmrPlayer $player, int $movementType): void {
		// send scout messages to user
		$message = 'Your forces have spotted ' . $player->getBBLink() . ' ';
		$message .= match ($movementType) {
			MOVEMENT_JUMP => 'jumping from',
			MOVEMENT_WARP => 'warping from',
			MOVEMENT_WALK => 'leaving',
		};
		$message .= ' sector ' . Globals::getSectorBBLink($this->getSectorID());

		// iterate over all scout drones in sector
		foreach ($this->getForces() as $force) {
			$force->ping($message, $player);
		}
		$this->db->write('UPDATE sector_has_forces SET refresher = 0 WHERE ' . $this->SQL . '
								AND refresher = ' . $this->db->escapeNumber($player->getAccountID()));
	}

	public function diedHere(AbstractSmrPlayer $player): void {
		// iterate over all scout drones in sector
		foreach ($this->getForces() as $force) {
			// send scout messages to user
			$message = 'Your forces have spotted that ' . $player->getBBLink() . ' has been <span class="red">DESTROYED</span> in sector ' . Globals::getSectorBBLink($this->sectorID);
			$force->ping($message, $player);
		}
	}

	public function getSQL(): string {
		return $this->SQL;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getGalaxyID(): int {
		return $this->galaxyID;
	}

	public function setGalaxyID(int $galaxyID): void {
		if (isset($this->galaxyID) && $this->galaxyID == $galaxyID) {
			return;
		}
		$this->galaxyID = $galaxyID;
		$this->hasChanged = true;
	}

	/**
	 * Returns the number of linked sectors (excluding warps)
	 */
	public function getNumberOfLinks(): int {
		return count($this->links);
	}

	/**
	 * Returns the number of linked sectors (including warps)
	 */
	public function getNumberOfConnections(): int {
		$links = $this->getNumberOfLinks();
		if ($this->hasWarp()) {
			$links++;
		}
		return $links;
	}

	public function getGalaxy(): SmrGalaxy {
		return SmrGalaxy::getGalaxy($this->getGameID(), $this->getGalaxyID());
	}

	public function getNeighbourID(string $dir): int {
		if ($this->hasLink($dir)) {
			return $this->getLink($dir);
		}
		$galaxy = $this->getGalaxy();
		$neighbour = $this->getSectorID();
		switch ($dir) {
			case 'Up':
				$neighbour -= $galaxy->getWidth();
				if ($neighbour < $galaxy->getStartSector()) {
					$neighbour += $galaxy->getSize();
				}
				break;
			case 'Down':
				$neighbour += $galaxy->getWidth();
				if ($neighbour > $galaxy->getEndSector()) {
					$neighbour -= $galaxy->getSize();
				}
				break;
			case 'Left':
				$neighbour -= 1;
				if ((1 + $neighbour - $galaxy->getStartSector()) % $galaxy->getWidth() == 0) {
					$neighbour += $galaxy->getWidth();
				}
				break;
			case 'Right':
				$neighbour += 1;
				if (($neighbour - $galaxy->getStartSector()) % $galaxy->getWidth() == 0) {
					$neighbour -= $galaxy->getWidth();
				}
				break;
			default:
				throw new Exception($dir . ': is not a valid direction');
		}
		return $neighbour;
	}

	public function getSectorDirection(int $sectorID): string {
		if ($sectorID == $this->getSectorID()) {
			return 'Current';
		}
		$dir = array_search($sectorID, $this->getLinks());
		if ($dir !== false) {
			return $dir;
		}
		if ($sectorID == $this->getWarp()) {
			return 'Warp';
		}
		return 'None';
	}

	public function getNeighbourSector(string $dir): self {
		return self::getSector($this->getGameID(), $this->getNeighbourID($dir));
	}

	public function getLinks(): array {
		return $this->links;
	}

	public function isLinked(int $sectorID): bool {
		return in_array($sectorID, $this->links) || $sectorID == $this->getWarp();
	}

	public function getLink(string $name): int {
		return $this->links[$name] ?? 0;
	}

	public function hasLink(string $name): bool {
		return $this->getLink($name) != 0;
	}

	public function getLinkSector(string $name): self|false {
		if ($this->hasLink($name)) {
			return self::getSector($this->getGameID(), $this->getLink($name));
		}
		return false;
	}

	/**
	 * Cannot be used for Warps
	 */
	public function setLink(string $name, int $linkID): void {
		if ($this->getLink($name) == $linkID) {
			return;
		}
		if ($linkID == $this->sectorID) {
			throw new Exception('Sector must not link to itself!');
		}
		if ($linkID == 0) {
			unset($this->links[$name]);
		} else {
			$this->links[$name] = $linkID;
		}
		$this->hasChanged = true;
	}

	/**
	 * Cannot be used for Warps
	 */
	public function setLinkSector(string $dir, SmrSector $linkSector): void {
		$this->setLink($dir, $linkSector->getSectorID());
		$linkSector->setLink(self::oppositeDir($dir), $this->getSectorID());
	}

	/**
	 * Cannot be used for Warps
	 */
	public function enableLink(string $dir): void {
		$linkSector = $this->getNeighbourSector($dir);
		// Handle single width/height galaxies (don't link sector to itself)
		if (!$this->equals($linkSector)) {
			$this->setLinkSector($dir, $linkSector);
		}
	}

	/**
	 * Cannot be used for Warps
	 */
	public function disableLink(string $dir): void {
		$this->setLink($dir, 0);
		$this->getNeighbourSector($dir)->setLink(self::oppositeDir($dir), 0);
	}

	/**
	 * Cannot be used for Warps
	 */
	public function toggleLink(string $dir): void {
		if ($this->hasLink($dir)) {
			$this->disableLink($dir);
		} else {
			$this->enableLink($dir);
		}
	}

	public static function oppositeDir(string $dir): string {
		return match ($dir) {
			'Up' => 'Down',
			'Down' => 'Up',
			'Left' => 'Right',
			'Right' => 'Left',
			'Warp' => 'Warp',
		};
	}

	public function getLinkUp(): int {
		return $this->getLink('Up');
	}

	public function setLinkUp(int $linkID): void {
		$this->setLink('Up', $linkID);
	}

	public function hasLinkUp(): bool {
		return $this->hasLink('Up');
	}

	public function getLinkDown(): int {
		return $this->getLink('Down');
	}

	public function setLinkDown(int $linkID): void {
		$this->setLink('Down', $linkID);
	}

	public function hasLinkDown(): bool {
		return $this->hasLink('Down');
	}

	public function getLinkLeft(): int {
		return $this->getLink('Left');
	}

	public function hasLinkLeft(): bool {
		return $this->hasLink('Left');
	}

	public function setLinkLeft(int $linkID): void {
		$this->setLink('Left', $linkID);
	}

	public function getLinkRight(): int {
		return $this->getLink('Right');
	}

	public function hasLinkRight(): bool {
		return $this->hasLink('Right');
	}

	public function setLinkRight(int $linkID): void {
		$this->setLink('Right', $linkID);
	}

	/**
	 * Returns the warp sector if the sector has a warp; returns 0 otherwise.
	 */
	public function getWarp(): int {
		return $this->warp;
	}

	public function getWarpSector(): self {
		return self::getSector($this->getGameID(), $this->getWarp());
	}

	public function hasWarp(): bool {
		return $this->getWarp() != 0;
	}

	/**
	 * Set the warp sector for both $this and $warp to ensure
	 * a consistent 2-way warp.
	 */
	public function setWarp(SmrSector $warp): void {
		if ($this->getWarp() == $warp->getSectorID() &&
		    $warp->getWarp() == $this->getSectorID()) {
			// Warps are already set correctly!
			return;
		}

		if ($this->equals($warp)) {
			throw new Exception('Sector must not warp to itself!');
		}

		// Can only have 1 warp per sector
		foreach ([[$warp, $this], [$this, $warp]] as [$A, $B]) {
			if ($A->hasWarp() && $A->getWarp() != $B->getSectorID()) {
				throw new Exception('Sector ' . $A->getSectorID() . ' already has a warp (to ' . $A->getWarp() . ')!');
			}
		}

		$this->warp = $warp->getSectorID();
		$this->hasChanged = true;

		if ($warp->getWarp() != $this->getSectorID()) {
			// Set the other side if needed
			$warp->setWarp($this);
		}
	}

	/**
	 * Remove the warp sector for both sides of the warp.
	 */
	public function removeWarp(): void {
		if (!$this->hasWarp()) {
			return;
		}

		$warp = $this->getWarpSector();
		if ($warp->hasWarp() && $warp->getWarp() != $this->getSectorID()) {
			throw new Exception('Warp sectors do not match');
		}

		$this->warp = 0;
		$this->hasChanged = true;

		if ($warp->hasWarp()) {
			$warp->removeWarp();
		}
	}

	public function hasPort(): bool {
		return $this->getPort()->exists();
	}

	public function getPort(): SmrPort {
		return SmrPort::getPort($this->getGameID(), $this->getSectorID());
	}

	public function createPort(): SmrPort {
		return SmrPort::createPort($this->getGameID(), $this->getSectorID());
	}

	public function removePort(): void {
		SmrPort::removePort($this->getGameID(), $this->getSectorID());
	}

	public function hasCachedPort(AbstractSmrPlayer $player = null): bool {
		return $this->getCachedPort($player) !== false;
	}

	public function getCachedPort(AbstractSmrPlayer $player = null): SmrPort|false {
		if ($player === null) {
			return false;
		}
		return SmrPort::getCachedPort($this->getGameID(), $this->getSectorID(), $player->getAccountID());
	}

	public function hasAnyLocationsWithAction(): bool {
		$locations = SmrLocation::getSectorLocations($this->getGameID(), $this->getSectorID());
		$hasAction = false;
		foreach ($locations as $location) {
			if ($location->hasAction()) {
				$hasAction = true;
			}
		}
		return $hasAction;
	}

	public function hasLocation(int $locationTypeID = null): bool {
		$locations = $this->getLocations();
		if (count($locations) == 0) {
			return false;
		}
		if ($locationTypeID === null) {
			return true;
		}
		foreach ($locations as $location) {
			if ($location->getTypeID() == $locationTypeID) {
				return true;
			}
		}
		return false;
	}

	public function getLocations(): array {
		return SmrLocation::getSectorLocations($this->getGameID(), $this->getSectorID());
	}

	public function addLocation(SmrLocation $location): void {
		SmrLocation::addSectorLocation($this->getGameID(), $this->getSectorID(), $location);
	}

	public function removeAllLocations(): void {
		SmrLocation::removeSectorLocations($this->getGameID(), $this->getSectorID());
	}

	public function hasPlanet(): bool {
		return $this->getPlanet()->exists();
	}

	public function getPlanet(): SmrPlanet {
		return SmrPlanet::getPlanet($this->getGameID(), $this->getSectorID());
	}

	public function createPlanet(int $type = 1): SmrPlanet {
		return SmrPlanet::createPlanet($this->getGameID(), $this->getSectorID(), $type);
	}

	public function removePlanet(): void {
		SmrPlanet::removePlanet($this->getGameID(), $this->getSectorID());
	}

	/**
	 * Removes ports, planets, locations, and warps from this sector.
	 * NOTE: This should only be used by the universe generator!
	 */
	public function removeAllFixtures(): void {
		if ($this->hasPort()) {
			$this->removePort();
		}
		if ($this->hasPlanet()) {
			$this->removePlanet();
		}
		if ($this->hasLocation()) {
			$this->removeAllLocations();
		}
		if ($this->hasWarp()) {
			$this->removeWarp();
		}
	}

	public function hasForces(): bool {
		return count($this->getForces()) > 0;
	}

	public function hasEnemyForces(AbstractSmrPlayer $player = null): bool {
		if ($player == null || !$this->hasForces()) {
			return false;
		}
		foreach ($this->getForces() as $force) {
			if (!$player->forceNAPAlliance($force->getOwner())) {
				return true;
			}
		}
		return false;
	}

	public function getEnemyForces(AbstractSmrPlayer $player): array {
		$enemyForces = [];
		foreach ($this->getForces() as $force) {
			if (!$player->forceNAPAlliance($force->getOwner())) {
				$enemyForces[] = $force;
			}
		}
		return $enemyForces;
	}

	/**
	 * Returns true if any forces in this sector belong to $player.
	 */
	public function hasPlayerForces(AbstractSmrPlayer $player): bool {
		foreach ($this->getForces() as $force) {
			if ($player->getAccountID() == $force->getOwnerID()) {
				return true;
			}
		}
		return false;
	}

	public function hasFriendlyForces(AbstractSmrPlayer $player = null): bool {
		if ($player == null || !$this->hasForces()) {
			return false;
		}
		foreach ($this->getForces() as $force) {
			if ($player->forceNAPAlliance($force->getOwner())) {
				return true;
			}
		}
		return false;
	}

	public function getFriendlyForces(AbstractSmrPlayer $player): array {
		$friendlyForces = [];
		foreach ($this->getForces() as $force) {
			if ($player->forceNAPAlliance($force->getOwner())) {
				$friendlyForces[] = $force;
			}
		}
		return $friendlyForces;
	}

	public function getForces(): array {
		return SmrForce::getSectorForces($this->getGameID(), $this->getSectorID());
	}

	public function getPlayers(): array {
		return SmrPlayer::getSectorPlayers($this->getGameID(), $this->getSectorID());
	}

	public function hasPlayers(): bool {
		return count($this->getPlayers()) > 0;
	}

	public function getOtherTraders(AbstractSmrPlayer $player): array {
		$players = SmrPlayer::getSectorPlayers($this->getGameID(), $this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractSmrPlayer $player): bool {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractSmrPlayer $player = null): bool {
		if ($player == null || !$this->hasOtherTraders($player)) {
			return false;
		}
		$otherPlayers = $this->getOtherTraders($player);
		foreach ($otherPlayers as $otherPlayer) {
			if (!$player->traderNAPAlliance($otherPlayer)
				&& !$otherPlayer->hasNewbieTurns()
				&& !$otherPlayer->hasFederalProtection()) {
				return true;
			}
		}
		return false;
	}

	public function hasFriendlyTraders(AbstractSmrPlayer $player = null): bool {
		if ($player == null || !$this->hasOtherTraders($player)) {
			return false;
		}
		$otherPlayers = $this->getOtherTraders($player);
		foreach ($otherPlayers as $otherPlayer) {
			if ($player->traderNAPAlliance($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is the $player's alliance flagship in this sector?
	 */
	public function hasAllianceFlagship(AbstractSmrPlayer $player = null): bool {
		if ($player === null || !$player->hasAlliance() || !$player->getAlliance()->hasFlagship()) {
			return false;
		}
		$flagshipID = $player->getAlliance()->getFlagshipID();
		foreach ($this->getPlayers() as $sectorPlayer) {
			if ($sectorPlayer->getAccountID() == $flagshipID) {
				return true;
			}
		}
		return false;
	}

	public function hasProtectedTraders(AbstractSmrPlayer $player = null): bool {
		if ($player == null || !$this->hasOtherTraders($player)) {
			return false;
		}
		$otherPlayers = $this->getOtherTraders($player);
		foreach ($otherPlayers as $otherPlayer) {
			if (!$player->traderNAPAlliance($otherPlayer)
				&& ($otherPlayer->hasNewbieTurns() || $otherPlayer->hasFederalProtection())) {
				return true;
			}
		}
		return false;
	}

	public function getFightingTradersAgainstForces(AbstractSmrPlayer $attackingPlayer, bool $bump): array {
		// Whether bumping or attacking, only the current player fires at forces
		return [$attackingPlayer];
	}

	public function getFightingTradersAgainstPort(AbstractSmrPlayer $attackingPlayer, SmrPort $defendingPort, bool $allEligible = false): array {
		$fightingPlayers = [];
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(), $this->getSectorID(), [$attackingPlayer->getAllianceID()]);
		foreach ($alliancePlayers as $accountID => $player) {
			if ($player->canFight()) {
				if ($attackingPlayer->traderAttackPortAlliance($player)) {
					$fightingPlayers[$accountID] = $alliancePlayers[$accountID];
				}
			}
		}
		if ($allEligible) {
			return $fightingPlayers;
		}
		return self::limitFightingTraders($fightingPlayers, $attackingPlayer, MAXIMUM_PORT_FLEET_SIZE);
	}

	public function getFightingTradersAgainstPlanet(AbstractSmrPlayer $attackingPlayer, SmrPlanet $defendingPlanet, bool $allEligible = false): array {
		$fightingPlayers = [];
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(), $this->getSectorID(), [$attackingPlayer->getAllianceID()]);
		if (count($alliancePlayers) > 0) {
			$planetOwner = $defendingPlanet->getOwner();
			foreach ($alliancePlayers as $accountID => $player) {
				if ($player->canFight()) {
					if ($attackingPlayer->traderAttackPlanetAlliance($player) && !$planetOwner->planetNAPAlliance($player)) {
						$fightingPlayers[$accountID] = $alliancePlayers[$accountID];
					}
				}
			}
		}
		if ($allEligible) {
			return $fightingPlayers;
		}
		return self::limitFightingTraders($fightingPlayers, $attackingPlayer, min($defendingPlanet->getMaxAttackers(), MAXIMUM_PLANET_FLEET_SIZE));
	}

	public function getFightingTraders(AbstractSmrPlayer $attackingPlayer, AbstractSmrPlayer $defendingPlayer, bool $checkForCloak = false, bool $allEligible = false): array {
		if ($attackingPlayer->traderNAPAlliance($defendingPlayer)) {
			throw new Exception('These traders are NAPed.');
		}
		$fightingPlayers = ['Attackers' => [], 'Defenders' => []];
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(), $this->getSectorID(), [$attackingPlayer->getAllianceID(), $defendingPlayer->getAllianceID()]);
		foreach ($alliancePlayers as $accountID => $player) {
			if ($player->canFight()) {
				if ($attackingPlayer->traderAttackTraderAlliance($player) && !$defendingPlayer->traderDefendTraderAlliance($player) && !$defendingPlayer->traderNAPAlliance($player)) {
					$fightingPlayers['Attackers'][$accountID] = $player;
				} elseif ($defendingPlayer->traderDefendTraderAlliance($player) && !$attackingPlayer->traderAttackTraderAlliance($player) && !$attackingPlayer->traderNAPAlliance($player) && ($checkForCloak === false || $attackingPlayer->canSee($player))) {
					$fightingPlayers['Defenders'][$accountID] = $player;
				}
			}
		}
		if ($allEligible) {
			return $fightingPlayers;
		}
		$fightingPlayers['Attackers'] = self::limitFightingTraders($fightingPlayers['Attackers'], $attackingPlayer, MAXIMUM_PVP_FLEET_SIZE);
		$fightingPlayers['Defenders'] = self::limitFightingTraders($fightingPlayers['Defenders'], $defendingPlayer, MAXIMUM_PVP_FLEET_SIZE);
		return $fightingPlayers;
	}

	public static function limitFightingTraders(array $fightingPlayers, AbstractSmrPlayer $keepPlayer, int $maximumFleetSize): array {
		// Cap fleets to the required size
		$fleet_size = count($fightingPlayers);
		if ($fleet_size > $maximumFleetSize) {
			// We use random key to stop the same people being capped all the time
			for ($j = 0; $j < $fleet_size - $maximumFleetSize; ++$j) {
				do {
					$key = array_rand($fightingPlayers);
				} while ($keepPlayer->equals($fightingPlayers[$key]));
				unset($fightingPlayers[$key]);
			}
		}
		return $fightingPlayers;
	}

	public function getPotentialFightingTraders(AbstractSmrPlayer $attackingPlayer): array {
		$fightingPlayers = ['Attackers' => [], 'Defenders' => []];
		$alliancePlayers = SmrPlayer::getSectorPlayersByAlliances($this->getGameID(), $this->getSectorID(), [$attackingPlayer->getAllianceID()]);
		foreach ($alliancePlayers as $accountID => $player) {
			if ($player->canFight()) {
				if ($attackingPlayer->traderAttackTraderAlliance($player)) {
					$fightingPlayers['Attackers'][$accountID] = $player;
				}
			}
		}
		return $fightingPlayers;
	}

	public function getBattles(): int {
		return $this->battles;
	}

	public function setBattles(int $amount): void {
		if ($this->battles == $amount) {
			return;
		}
		$this->battles = $amount;
		$this->hasChanged = true;
	}

	public function decreaseBattles(int $amount): void {
		$this->setBattles($this->battles - $amount);
	}

	public function increaseBattles(int $amount): void {
		$this->setBattles($this->battles + $amount);
	}

	public function equals(SmrSector $otherSector): bool {
		return $otherSector->getSectorID() == $this->getSectorID() && $otherSector->getGameID() == $this->getGameID();
	}

	public function isLinkedSector(SmrSector $otherSector): bool {
		return $otherSector->getGameID() == $this->getGameID() && $this->isLinked($otherSector->getSectorID());
	}

	public function isVisited(AbstractSmrPlayer $player = null): bool {
		if ($player === null) {
			return true;
		}
		if (!isset($this->visited[$player->getAccountID()])) {
			$dbResult = $this->db->read('SELECT 1 FROM player_visited_sector WHERE ' . $this->SQL . ' AND account_id=' . $this->db->escapeNumber($player->getAccountID()) . ' LIMIT 1');
			$this->visited[$player->getAccountID()] = !$dbResult->hasRecord();
		}
		return $this->visited[$player->getAccountID()];
	}

	public function getLocalMapMoveHREF(AbstractSmrPlayer $player): string {
		return Globals::getSectorMoveHREF($player, $this->getSectorID(), 'map_local.php');
	}

	public function getCurrentSectorMoveHREF(AbstractSmrPlayer $player): string {
		return Globals::getCurrentSectorMoveHREF($player, $this->getSectorID());
	}

	public function getGalaxyMapHREF(): string {
		return '?sector_id=' . $this->getSectorID();
	}

	public function getSectorScanHREF(AbstractSmrPlayer $player): string {
		return Globals::getSectorScanHREF($player, $this->getSectorID());
	}

	public function hasX(mixed $x, AbstractSmrPlayer $player = null): bool {
		if ($x instanceof SmrSector) {
			return $this->equals($x);
		}
		if ($x == 'Port') {
			return $this->hasPort();
		}
		if ($x == 'Location') {
			return $this->hasLocation();
		}
		if ($x instanceof SmrLocation) {
			return $this->hasLocation($x->getTypeID());
		}
		if ($x instanceof SmrGalaxy) {
			return $x->contains($this);
		}

		if (is_array($x) && $x['Type'] == 'Good') { //Check if it's possible for port to have X, hacky but nice performance gains
			if ($this->hasPort() && $this->getPort()->hasX($x)) {
				return true;
			}
		}

		//Check if it's possible for location to have X, hacky but nice performance gains
		if ($x instanceof SmrWeaponType || $x instanceof SmrShipType || (is_array($x) && $x['Type'] == 'Hardware') || (is_string($x) && ($x == 'Bank' || $x == 'Bar' || $x == 'Fed' || $x == 'SafeFed' || $x == 'HQ' || $x == 'UG' || $x == 'Hardware' || $x == 'Ship' || $x == 'Weapon'))) {
			foreach ($this->getLocations() as $loc) {
				if ($loc->hasX($x, $player)) {
					return true;
				}
			}
		}
		return false;
	}

}
