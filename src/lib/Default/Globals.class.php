<?php declare(strict_types=1);

class Globals {
	protected static array $HIDDEN_PLAYERS;
	protected static array $LEVEL_REQUIREMENTS;
	protected static array $RACES;
	protected static array $GOODS;
	protected static array $HARDWARE_TYPES;
	protected static bool $FEATURE_REQUEST_OPEN;
	protected static array $RACE_RELATIONS;
	protected static array $AVAILABLE_LINKS = [];
	protected static Smr\Database $db;

	protected static function initialiseDatabase() : void {
		if (!isset(self::$db)) {
			self::$db = Smr\Database::getInstance();
		}
	}

	public static function getAvailableLinks() : array {
		return self::$AVAILABLE_LINKS;
	}

	public static function canAccessPage(string $pageName, AbstractSmrPlayer $player, array $extraInfo) : void {
		switch ($pageName) {
			case 'AllianceMOTD':
				if ($player->getAllianceID() != $extraInfo['AllianceID']) {
					logException(new Exception('Tried to access page without permission.'));
					create_error('You cannot access this page.');
				}
			break;
		}
	}

	public static function getHiddenPlayers() : array {
		if (!isset(self::$HIDDEN_PLAYERS)) {
			self::initialiseDatabase();
			$dbResult = self::$db->read('SELECT account_id FROM hidden_players');
			self::$HIDDEN_PLAYERS = array(0); //stop errors
			foreach ($dbResult->records() as $dbRecord) {
				self::$HIDDEN_PLAYERS[] = $dbRecord->getInt('account_id');
			}
		}
		return self::$HIDDEN_PLAYERS;
	}

	public static function getGalacticPostEditorIDs(int $gameID) : array {
		self::initialiseDatabase();
		$editorIDs = [];
		$dbResult = self::$db->read('SELECT account_id FROM galactic_post_writer WHERE position=\'editor\' AND game_id=' . self::$db->escapeNumber($gameID));
		foreach ($dbResult->records() as $dbRecord) {
			$editorIDs[] = $dbRecord->getInt('account_id');
		}
		return $editorIDs;
	}

	public static function getLevelRequirements() : array {
		if (!isset(self::$LEVEL_REQUIREMENTS)) {
			self::initialiseDatabase();
			self::$LEVEL_REQUIREMENTS = array();

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM level ORDER BY level_id ASC');
			foreach ($dbResult->records() as $dbRecord) {
				self::$LEVEL_REQUIREMENTS[$dbRecord->getInt('level_id')] = array(
					'Name' => $dbRecord->getField('level_name'),
					'Requirement' => $dbRecord->getInt('requirement'),
				);
			}
		}
		return self::$LEVEL_REQUIREMENTS;
	}

	public static function getRaces() : array {
		if (!isset(self::$RACES)) {
			self::initialiseDatabase();
			self::$RACES = array();

			// determine user level
			$dbResult = self::$db->read('SELECT race_id,race_name,race_description FROM race ORDER BY race_id');
			foreach ($dbResult->records() as $dbRecord) {
				self::$RACES[$dbRecord->getInt('race_id')] = array(
					'Race ID' => $dbRecord->getInt('race_id'),
					'Race Name' => $dbRecord->getField('race_name'),
					'Description' => $dbRecord->getField('race_description'),
					'ImageLink' => 'images/race/race' . $dbRecord->getInt('race_id') . '.jpg',
					'ImageHeadLink' => 'images/race/head/race' . $dbRecord->getInt('race_id') . '.jpg',
				);
			}
		}
		return self::$RACES;
	}

	public static function getRaceName(int $raceID) : string {
		return Globals::getRaces()[$raceID]['Race Name'];
	}

	public static function getRaceImage(int $raceID) : string {
		return Globals::getRaces()[$raceID]['ImageLink'];
	}

	public static function getRaceHeadImage(int $raceID) : string {
		return Globals::getRaces()[$raceID]['ImageHeadLink'];
	}

	public static function getColouredRaceNameForRace(int $raceID, int $gameID, int $fromRaceID, bool $linked = true) : string {
		$raceRelations = Globals::getRaceRelations($gameID, $fromRaceID);
		return self::getColouredRaceName($raceID, $raceRelations[$raceID], $linked);
	}

	public static function getColouredRaceName(int $raceID, int $relations, bool $linked = true) : string {
		$raceName = get_colored_text($relations, Globals::getRaceName($raceID));
		if ($linked === true) {
			$container = Page::create('skeleton.php', 'council_list.php', array('race_id' => $raceID));
			$raceName = create_link($container, $raceName);
		}
		return $raceName;
	}

	public static function getGoods() : array {
		if (!isset(self::$GOODS)) {
			self::initialiseDatabase();
			self::$GOODS = array();

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM good ORDER BY good_id');
			foreach ($dbResult->records() as $dbRecord) {
				self::$GOODS[$dbRecord->getInt('good_id')] = array(
					'Type' => 'Good',
					'ID' => $dbRecord->getInt('good_id'),
					'Name' => $dbRecord->getField('good_name'),
					'Max' => $dbRecord->getInt('max_amount'),
					'BasePrice' => $dbRecord->getInt('base_price'),
					'Class' => $dbRecord->getInt('good_class'),
					'ImageLink' => 'images/port/' . $dbRecord->getInt('good_id') . '.png',
					'AlignRestriction' => $dbRecord->getInt('align_restriction'),
				);
			}
		}
		return self::$GOODS;
	}

	public static function getGood(int $goodID) : array {
		return Globals::getGoods()[$goodID];
	}

	public static function getGoodName(int $goodID) : string {
		if ($goodID == GOODS_NOTHING) {
			return 'Nothing';
		}
		return Globals::getGoods()[$goodID]['Name'];
	}

	public static function getHardwareTypes(int $hardwareTypeID = null) : array {
		if (!isset(self::$HARDWARE_TYPES)) {
			self::initialiseDatabase();
			self::$HARDWARE_TYPES = array();

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM hardware_type ORDER BY hardware_type_id');
			foreach ($dbResult->records() as $dbRecord) {
				self::$HARDWARE_TYPES[$dbRecord->getInt('hardware_type_id')] = array(
					'Type' => 'Hardware',
					'ID' => $dbRecord->getInt('hardware_type_id'),
					'Name' => $dbRecord->getField('hardware_name'),
					'Cost' => $dbRecord->getInt('cost'),
				);
			}
		}
		if ($hardwareTypeID === null) {
			return self::$HARDWARE_TYPES;
		}
		return self::$HARDWARE_TYPES[$hardwareTypeID];
	}

	public static function getHardwareName(int $hardwareTypeID) : string {
		return Globals::getHardwareTypes()[$hardwareTypeID]['Name'];
	}

	public static function getHardwareCost(int $hardwareTypeID) : int {
		return Globals::getHardwareTypes()[$hardwareTypeID]['Cost'];
	}

	public static function isValidGame(int $gameID) : bool {
		try {
			SmrGame::getGame($gameID);
			return true;
		} catch (GameNotFoundException $e) {
			return false;
		}
	}

	public static function getGameType(int $gameID) : string {
		return SmrGame::getGame($gameID)->getGameType();
	}

	public static function isFeatureRequestOpen() : bool {
		if (!isset(self::$FEATURE_REQUEST_OPEN)) {
			self::initialiseDatabase();
			$dbResult = self::$db->read('SELECT open FROM open_forms WHERE type=\'FEATURE\'');

			self::$FEATURE_REQUEST_OPEN = $dbResult->record()->getBoolean('open');
		}
		return self::$FEATURE_REQUEST_OPEN;
	}

	public static function getRaceRelations(int $gameID, int $raceID) : array {
		if (!isset(self::$RACE_RELATIONS[$gameID][$raceID])) {
			self::initialiseDatabase();
			//get relations
			$RACES = Globals::getRaces();
			self::$RACE_RELATIONS[$gameID][$raceID] = array();
			foreach ($RACES as $otherRaceID => $raceArray) {
				self::$RACE_RELATIONS[$gameID][$raceID][$otherRaceID] = 0;
			}
			$dbResult = self::$db->read('SELECT race_id_2,relation FROM race_has_relation WHERE race_id_1=' . self::$db->escapeNumber($raceID) . ' AND game_id=' . self::$db->escapeNumber($gameID) . ' LIMIT ' . count($RACES));
			foreach ($dbResult->records() as $dbRecord) {
				self::$RACE_RELATIONS[$gameID][$raceID][$dbRecord->getInt('race_id_2')] = $dbRecord->getInt('relation');
			}
		}
		return self::$RACE_RELATIONS[$gameID][$raceID];
	}

	public static function getFeatureRequestHREF() : string {
		return Page::create('skeleton.php', 'feature_request.php')->href();
	}

	public static function getCurrentSectorHREF() : string {
		return self::$AVAILABLE_LINKS['CurrentSector'] = Page::create('skeleton.php', 'current_sector.php')->href();
	}

	public static function getLocalMapHREF() : string {
		return self::$AVAILABLE_LINKS['LocalMap'] = Page::create('skeleton.php', 'map_local.php')->href();
	}

	public static function getCurrentPlayersHREF() : string {
		return self::$AVAILABLE_LINKS['CurrentPlayers'] = Page::create('skeleton.php', 'current_players.php')->href();
	}

	public static function getTradeHREF() : string {
		return self::$AVAILABLE_LINKS['EnterPort'] = Page::create('skeleton.php', 'shop_goods.php')->href();
	}

	public static function getAttackTraderHREF(int $accountID) : string {
		$container = Page::create('trader_attack_processing.php');
		$container['target'] = $accountID;
		return self::$AVAILABLE_LINKS['AttackTrader'] = $container->href();
	}

	public static function getPodScreenHREF() : string {
		return Page::create('death_processing.php')->href();
	}

	public static function getBetaFunctionsHREF() : string { //BETA
		return Page::create('skeleton.php', 'beta_functions.php')->href();
	}

	public static function getBugReportProcessingHREF() : string {
		return Page::create('bug_report_processing.php')->href();
	}

	public static function getWeaponReorderHREF(int $weaponOrderID, string $direction) : string {
		$container = Page::create('weapon_reorder_processing.php');
		$container[$direction] = $weaponOrderID;
		return $container->href();
	}

	public static function getSmrFileCreateHREF(int $adminCreateGameID = null) : string {
		$container = Page::create('skeleton.php', 'smr_file_create.php');
		$container['AdminCreateGameID'] = $adminCreateGameID;
		return $container->href();
	}

	public static function getCurrentSectorMoveHREF(AbstractSmrPlayer $player, int $toSector) : string {
		return self::getSectorMoveHREF($player, $toSector, 'current_sector.php');
	}

	public static function getSectorMoveHREF(AbstractSmrPlayer $player, int $toSector, string $targetPage) : string {
		$container = Page::create('sector_move_processing.php');
		$container['target_page'] = $targetPage;
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Move' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getSectorScanHREF(AbstractSmrPlayer $player, int $toSector) : string {
		$container = Page::create('skeleton.php', 'sector_scan.php');
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Scan' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getPlotCourseHREF(int $fromSector = null, int $toSector = null) : string {
		if ($fromSector === null && $toSector === null) {
			return self::$AVAILABLE_LINKS['PlotCourse'] = Page::create('skeleton.php', 'course_plot.php')->href();
		} else {
			return Page::create('course_plot_processing.php', '', array('from'=>$fromSector, 'to'=>$toSector))->href();
		}
	}

	public static function getPlanetMainHREF() : string {
		return Page::create('skeleton.php', 'planet_main.php')->href();
	}

	public static function getPlanetConstructionHREF() : string {
		return Page::create('skeleton.php', 'planet_construction.php')->href();
	}

	public static function getPlanetDefensesHREF() : string {
		return Page::create('skeleton.php', 'planet_defense.php')->href();
	}

	public static function getPlanetOwnershipHREF() : string {
		return Page::create('skeleton.php', 'planet_ownership.php')->href();
	}

	public static function getPlanetStockpileHREF() : string {
		return Page::create('skeleton.php', 'planet_stockpile.php')->href();
	}

	public static function getPlanetFinancesHREF() : string {
		return Page::create('skeleton.php', 'planet_financial.php')->href();
	}

	public static function getAllianceHREF(int $allianceID = null) : string {
		if ($allianceID > 0) {
			return self::getAllianceMotdHREF($allianceID);
		} else {
			return self::getAllianceListHREF();
		}
	}

	public static function getAllianceBankHREF(int $allianceID = null) : string {
		$container = Page::create('skeleton.php', 'bank_alliance.php');
		$container['alliance_id'] = $allianceID;
		return $container->href();
	}

	public static function getAllianceRosterHREF($allianceID = null) : string {
		$container = Page::create('skeleton.php', 'alliance_roster.php');
		$container['alliance_id'] = $allianceID;
		return $container->href();
	}

	public static function getAllianceListHREF() : string {
		return Page::create('skeleton.php', 'alliance_list.php')->href();
	}

	public static function getAllianceNewsHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'news_read_advanced.php', array('allianceID'=>$allianceID, 'submit' => 'Search For Alliance'))->href();
	}

	public static function getAllianceMotdHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'alliance_mod.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getAllianceMessageHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'alliance_broadcast.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getAllianceMessageBoardHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'alliance_message.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getAllianceForcesHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'alliance_forces.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getAllianceOptionsHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'alliance_option.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getPlanetListHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'planet_list.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getPlanetListFinancialHREF(int $allianceID) : string {
		return Page::create('skeleton.php', 'planet_list_financial.php', array('alliance_id'=>$allianceID))->href();
	}

	public static function getViewMessageBoxesHREF() : string {
		return Page::create('skeleton.php', 'message_box.php')->href();
	}

	public static function getSendGlobalMessageHREF() : string {
		return Page::create('skeleton.php', 'message_send.php')->href();
	}

	public static function getManageBlacklistHREF() : string {
		return Page::create('skeleton.php', 'message_blacklist.php')->href();
	}

	public static function getSendCouncilMessageHREF(int $raceID) : string {
		$container = Page::create('skeleton.php', 'council_send_message.php');
		$container['race_id'] = $raceID;
		$container['folder_id'] = MSG_POLITICAL;
		return $container->href();
	}

	public static function getTraderStatusHREF() : string {
		return Page::create('skeleton.php', 'trader_status.php')->href();
	}

	public static function getCouncilHREF(int $raceID = null) : string {
		$container = Page::create('skeleton.php', 'council_list.php');
		$container['race_id'] = $raceID;
		return $container->href();
	}

	public static function getTraderRelationsHREF() : string {
		return Page::create('skeleton.php', 'trader_relations.php')->href();
	}

	public static function getTraderBountiesHREF() : string {
		return Page::create('skeleton.php', 'trader_bounties.php')->href();
	}

	public static function getPoliticsHREF() : string {
		return Page::create('skeleton.php', 'council_list.php')->href();
	}

	public static function getCasinoHREF() : string {
		return Page::create('skeleton.php', 'chess.php')->href();
	}

	public static function getChessHREF() : string {
		return Page::create('skeleton.php', 'chess.php')->href();
	}

	public static function getChessCreateHREF() : string {
		return Page::create('chess_create_processing.php')->href();
	}

	public static function getBarMainHREF() : string {
		$container = Page::create('skeleton.php', 'bar_main.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBarLottoPlayHREF() : string {
		$container = Page::create('skeleton.php', 'bar_lotto_buy.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBarBlackjackHREF() : string {
		$container = Page::create('skeleton.php', 'bar_gambling_bet.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBuyMessageNotificationsHREF() : string {
		return Page::create('skeleton.php', 'buy_message_notifications.php')->href();
	}

	public static function getBuyShipNameHREF() : string {
		return Page::create('skeleton.php', 'buy_ship_name.php')->href();
	}

	public static function getBuyShipNameCosts() : array {
		return [
			'text' => CREDITS_PER_TEXT_SHIP_NAME,
			'html' => CREDITS_PER_HTML_SHIP_NAME,
			'logo' => CREDITS_PER_SHIP_LOGO,
		];
	}

	public static function getSectorBBLink(int $sectorID) : string {
		return '[sector=' . $sectorID . ']';
	}

	public static function getAvailableTemplates() : array {
		return array_keys(CSS_URLS);
	}

	public static function getAvailableColourSchemes(string $templateName) : array {
		return array_keys(CSS_COLOUR_URLS[$templateName]);
	}

	/**
	 * Returns an array of history databases for which we have ancient saved
	 * game data. Array keys are database names and values are the columns in
	 * the `account` table with the linked historical account ID's.
	 */
	public static function getHistoryDatabases() : array {
		if (defined('HISTORY_DATABASES')) {
			return HISTORY_DATABASES;
		} else {
			return array();
		}
	}

}
