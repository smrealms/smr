<?php

class Globals {
	protected static $HIDDEN_PLAYERS = null;
	protected static $LEVEL_REQUIREMENTS = null;
	protected static $RACES = null;
	protected static $GOODS = null;
	protected static $HARDWARE_TYPES = null;
	protected static $GAMES = array();
	protected static $FEATURE_REQUEST_OPEN = null;
	protected static $RACE_RELATIONS = array();
	protected static $USER_RANKINGS = null;
	protected static $SHIP_CLASSES = null;
	protected static $AVAILABLE_LINKS = array();
	protected static $db = null;

	private function __construct() {
	}

	protected static function initialiseDatabase() {
		if (self::$db == null) {
			self::$db = new SmrMySqlDatabase();
		}
	}

	public static function getAvailableLinks() {
		return self::$AVAILABLE_LINKS;
	}

	public static function canAccessPage($pageName, AbstractSmrPlayer $player, array $extraInfo) {
		switch ($pageName) {
			case 'AllianceMOTD':
				if ($player->getAllianceID() != $extraInfo['AllianceID']) {
					logException(new Exception('Tried to access page without permission.'));
					create_error('You cannot access this page.');
				}
			break;
		}
	}

	public static function &getHiddenPlayers() {
		if (self::$HIDDEN_PLAYERS == null) {
			self::initialiseDatabase();
			self::$db->query('SELECT account_id FROM hidden_players');
			self::$HIDDEN_PLAYERS = array(0); //stop errors
			while (self::$db->nextRecord()) {
				self::$HIDDEN_PLAYERS[] = self::$db->getField('account_id');
			}
		}
		return self::$HIDDEN_PLAYERS;
	}

	public static function getGalacticPostEditorIDs($gameID) {
		self::initialiseDatabase();
		$editorIDs = [];
		self::$db->query('SELECT account_id FROM galactic_post_writer WHERE position=\'editor\' AND game_id=' . self::$db->escapeNumber($gameID));
		while (self::$db->nextRecord()) {
			$editorIDs[] = self::$db->getInt('account_id');
		}
		return $editorIDs;
	}

	public static function &getLevelRequirements() {
		if (self::$LEVEL_REQUIREMENTS == null) {
			self::initialiseDatabase();
			self::$LEVEL_REQUIREMENTS = array();

			// determine user level
			self::$db->query('SELECT * FROM level ORDER BY level_id ASC');
			while (self::$db->nextRecord()) {
				self::$LEVEL_REQUIREMENTS[self::$db->getInt('level_id')] = array(
																				'Name' => self::$db->getField('level_name'),
																				'Requirement' => self::$db->getInt('requirement')
																				);
			}
		}
		return self::$LEVEL_REQUIREMENTS;
	}

	public static function &getRaces() {
		if (self::$RACES == null) {
			self::initialiseDatabase();
			self::$RACES = array();

			// determine user level
			self::$db->query('SELECT race_id,race_name,race_description FROM race ORDER BY race_id');
			while (self::$db->nextRecord()) {
				self::$RACES[self::$db->getInt('race_id')] = array(
																'Race ID' => self::$db->getInt('race_id'),
																'Race Name' => self::$db->getField('race_name'),
																'Description' => self::$db->getField('race_description'),
																'ImageLink' => 'images/race/race' . self::$db->getInt('race_id') . '.jpg',
																'ImageHeadLink' => 'images/race/head/race' . self::$db->getInt('race_id') . '.jpg',
																'ImageGraphLink' => 'images/race/graph/race' . self::$db->getInt('race_id') . '.gif',
																);
			}
		}
		return self::$RACES;
	}

	public static function getRaceName($raceID) {
		return Globals::getRaces()[$raceID]['Race Name'];
	}

	public static function getRaceImage($raceID) {
		return Globals::getRaces()[$raceID]['ImageLink'];
		}

	public static function getRaceHeadImage($raceID) {
		return Globals::getRaces()[$raceID]['ImageHeadLink'];
		}

	public static function getRaceGraphImage($raceID) {
		return Globals::getRaces()[$raceID]['ImageGraphLink'];
		}

	public static function getColouredRaceNameForRace($raceID, $gameID, $fromRaceID, $linked = true) {
		$raceRelations = Globals::getRaceRelations($gameID, $fromRaceID);
		return self::getColouredRaceName($raceID, $raceRelations[$raceID], $linked);
	}

	public static function getColouredRaceName($raceID, $relations, $linked = true) {
		$raceName = get_colored_text($relations, Globals::getRaceName($raceID));
		if ($linked === true) {
			$container = create_container('skeleton.php', 'council_list.php', array('race_id' => $raceID));
			$raceName = create_link($container, $raceName);
		}
		return $raceName;
	}

	public static function &getGoods() {
		if (self::$GOODS == null) {
			self::initialiseDatabase();
			self::$GOODS = array();

			// determine user level
			self::$db->query('SELECT * FROM good ORDER BY good_id');
			while (self::$db->nextRecord()) {
				self::$GOODS[self::$db->getField('good_id')] = array(
																'Type' => 'Good',
																'ID' => self::$db->getInt('good_id'),
																'Name' => self::$db->getField('good_name'),
																'Max' => self::$db->getInt('max_amount'),
																'BasePrice' => self::$db->getInt('base_price'),
																'Class' => self::$db->getInt('good_class'),
																'ImageLink' => 'images/port/' . self::$db->getField('good_id') . '.png',
																'AlignRestriction' => self::$db->getInt('align_restriction')
															);
			}
		}
		return self::$GOODS;
	}
	public static function &getGood($goodID) {
		return Globals::getGoods()[$goodID];
	}
	public static function getGoodName($goodID) {
		if ($goodID == GOOD_NOTHING) {
			return 'Nothing';
		}
		return Globals::getGoods()[$goodID]['Name'];
	}

	public static function &getHardwareTypes($hardwareTypeID = false) {
		if (self::$HARDWARE_TYPES == null) {
			self::initialiseDatabase();
			self::$HARDWARE_TYPES = array();

			// determine user level
			self::$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
			while (self::$db->nextRecord()) {
				self::$HARDWARE_TYPES[self::$db->getField('hardware_type_id')] = array(
																			'Type' => 'Hardware',
																			'ID' => self::$db->getInt('hardware_type_id'),
																			'Name' => self::$db->getField('hardware_name'),
																			'Cost' => self::$db->getInt('cost')
																			);
			}
		}
		if ($hardwareTypeID === false) {
			return self::$HARDWARE_TYPES;
		}
		return self::$HARDWARE_TYPES[$hardwareTypeID];
	}

	public static function getHardwareName($hardwareTypeID) {
		return Globals::getHardwareTypes()[$hardwareTypeID]['Name'];
	}

	public static function getHardwareCost($hardwareTypeID) {
		return Globals::getHardwareTypes()[$hardwareTypeID]['Cost'];
	}

	public static function isValidGame($gameID) {
		try {
			SmrGame::getGame($gameID);
			return true;
		} catch (GameNotFoundException $e) {
			return false;
		}
	}

	public static function getGameType($gameID) {
		if (self::isValidGame($gameID)) {
			return SmrGame::getGame($gameID)->getGameType();
		}
		return 0;
	}

	public static function getGameName($gameID) {
		return SmrGame::getGame($gameID)->getName();
	}

	public static function isFeatureRequestOpen() {
		if (self::$FEATURE_REQUEST_OPEN == null) {
			self::initialiseDatabase();
			self::$db->query('SELECT open FROM open_forms WHERE type=\'FEATURE\'');
			self::$db->nextRecord();

			self::$FEATURE_REQUEST_OPEN = self::$db->getBoolean('open');
		}
		return self::$FEATURE_REQUEST_OPEN;
	}

	public static function &getRaceRelations($gameID, $raceID) {
		if (!isset(self::$RACE_RELATIONS[$gameID])) {
			self::$RACE_RELATIONS[$gameID] = array();
		}

		if (!isset(self::$RACE_RELATIONS[$gameID][$raceID])) {
			self::initialiseDatabase();
			//get relations
			$RACES = Globals::getRaces();
			self::$RACE_RELATIONS[$gameID][$raceID] = array();
			foreach ($RACES as $otherRaceID => $raceArray) {
				self::$RACE_RELATIONS[$gameID][$raceID][$otherRaceID] = 0;
			}
			self::$db->query('SELECT race_id_2,relation FROM race_has_relation WHERE race_id_1=' . self::$db->escapeNumber($raceID) . ' AND game_id=' . self::$db->escapeNumber($gameID) . ' LIMIT ' . count($RACES));
			while (self::$db->nextRecord()) {
				self::$RACE_RELATIONS[$gameID][$raceID][self::$db->getInt('race_id_2')] = self::$db->getInt('relation');
			}
		}
		return self::$RACE_RELATIONS[$gameID][$raceID];
	}

	/**
	 * If specified, returns the Ship Class Name associated with the given ID.
	 * Otherwise, returns an array of all Ship Class Names.
	 */
	public static function getShipClass($shipClassID = null) {
		if (is_null(self::$SHIP_CLASSES)) {
			self::initialiseDatabase();
			self::$db->query('SELECT * FROM ship_class');
			while (self::$db->nextRecord()) {
				self::$SHIP_CLASSES[self::$db->getInt('ship_class_id')] = self::$db->getField('ship_class_name');
			}
		}
		if (is_null($shipClassID)) {
			return self::$SHIP_CLASSES;
		}
		return self::$SHIP_CLASSES[$shipClassID];
	}

	public static function getUserRanking() {
		if (!isset(self::$USER_RANKINGS)) {
			self::initialiseDatabase();
			self::$USER_RANKINGS = array();
			self::$db->query('SELECT rank, rank_name FROM user_rankings ORDER BY rank');
			while (self::$db->nextRecord()) {
				self::$USER_RANKINGS[self::$db->getInt('rank')] = self::$db->getField('rank_name');
			}
		}
		return self::$USER_RANKINGS;
	}

	public static function getFeatureRequestHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'feature_request.php'));
	}

	public static function getCurrentSectorHREF() {
		return self::$AVAILABLE_LINKS['CurrentSector'] = SmrSession::getNewHREF(create_container('skeleton.php', 'current_sector.php'));
	}

	public static function getLocalMapHREF() {
		return self::$AVAILABLE_LINKS['LocalMap'] = SmrSession::getNewHREF(create_container('skeleton.php', 'map_local.php'));
	}

	public static function getCurrentPlayersHREF() {
		return self::$AVAILABLE_LINKS['CurrentPlayers'] = SmrSession::getNewHREF(create_container('skeleton.php', 'current_players.php'));
	}

	public static function getTradeHREF() {
		return self::$AVAILABLE_LINKS['EnterPort'] = SmrSession::getNewHREF(create_container('skeleton.php', 'shop_goods.php'));
	}

	public static function getAttackTraderHREF($accountID) {
		$container = create_container('skeleton.php', 'trader_attack_processing.php');
		$container['target'] = $accountID;
		return self::$AVAILABLE_LINKS['AttackTrader'] = SmrSession::getNewHREF($container);
	}

	public static function getPodScreenHREF() {
		return SmrSession::getNewHREF(create_container('death_processing.php'));
	}

	public static function getBetaFunctionsHREF() { //BETA
		return SmrSession::getNewHREF(create_container('skeleton.php', 'beta_functions.php'));
	}

	public static function getBugReportProcessingHREF() {
		return SmrSession::getNewHREF(create_container('bug_report_processing.php'));
	}

	public static function getWeaponReorderHREF($weaponOrderID, $direction) {
		$container = create_container('skeleton.php', 'weapon_reorder.php');
		$container[$direction] = $weaponOrderID;
		return SmrSession::getNewHREF($container);
	}

	public static function getSmrFileCreateHREF($adminCreateGameID = false) {
		$container = create_container('skeleton.php', 'smr_file_create.php');
		$container['AdminCreateGameID'] = $adminCreateGameID;
		return SmrSession::getNewHREF($container);
	}

	public static function getCurrentSectorMoveHREF($toSector) {
		return self::getSectorMoveHREF($toSector, 'current_sector.php');
	}

	public static function getSectorMoveHREF($toSector, $targetPage) {
		global $player;
		$container = create_container('sector_move_processing.php');
		$container['target_page'] = $targetPage;
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Move' . $player->getSector()->getSectorDirection($toSector)] = SmrSession::getNewHREF($container);
	}

	public static function getSectorScanHREF($toSector) {
		global $player;
		$container = create_container('skeleton.php', 'sector_scan.php');
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Scan' . $player->getSector()->getSectorDirection($toSector)] = SmrSession::getNewHREF($container);
	}

	public static function getPlotCourseHREF($fromSector = false, $toSector = false) {
		if ($fromSector === false && $toSector === false) {
			return self::$AVAILABLE_LINKS['PlotCourse'] = SmrSession::getNewHREF(create_container('skeleton.php', 'course_plot.php'));
		} else {
			return SmrSession::getNewHREF(create_container('course_plot_processing.php', '', array('from'=>$fromSector, 'to'=>$toSector)));
		}
	}

	public static function getPlanetMainHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_main.php'));
	}

	public static function getPlanetConstructionHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_construction.php'));
	}

	public static function getPlanetDefensesHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_defense.php'));
	}

	public static function getPlanetOwnershipHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_ownership.php'));
	}

	public static function getPlanetStockpileHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_stockpile.php'));
	}

	public static function getPlanetFinancesHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_financial.php'));
	}

	public static function getAllianceHREF($allianceID = null) {
		if ($allianceID > 0) {
			return self::getAllianceMotdHREF($allianceID);
		} else {
			return self::getAllianceListHREF();
		}
	}

	public static function getAllianceBankHREF($allianceID = null) {
		$container = create_container('skeleton.php', 'bank_alliance.php');
		if ($allianceID != null) {
			$container['alliance_id'] = $allianceID;
		}
		return SmrSession::getNewHREF($container);
	}

	public static function getAllianceRosterHREF($allianceID = null) {
		$container = create_container('skeleton.php', 'alliance_roster.php');
		if ($allianceID != null) {
			$container['alliance_id'] = $allianceID;
		}
		return SmrSession::getNewHREF($container);
	}

	public static function getAllianceListHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_list.php'));
	}

	public static function getAllianceNewsHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'news_read_advanced.php', array('AllianceID'=>$allianceID, 'submit' => 'Search For Alliance')));
	}

	public static function getAllianceMotdHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_mod.php', array('alliance_id'=>$allianceID)));
	}

	public static function getAllianceMessageHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_broadcast.php', array('alliance_id'=>$allianceID)));
	}

	public static function getAllianceMessageBoardHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_message.php', array('alliance_id'=>$allianceID)));
	}

	public static function getAllianceForcesHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_forces.php', array('alliance_id'=>$allianceID)));
	}

	public static function getAllianceOptionsHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'alliance_option.php', array('alliance_id'=>$allianceID)));
	}

	public static function getPlanetListHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_list.php', array('alliance_id'=>$allianceID)));
	}

	public static function getPlanetListFinancialHREF($allianceID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_list_financial.php', array('alliance_id'=>$allianceID)));
	}

	public static function getViewMessagesHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'message_view.php'));
	}

	public static function getSendGlobalMessageHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'message_send.php'));
	}

	public static function getManageBlacklistHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'message_blacklist.php'));
	}

	public static function getSendCouncilMessageHREF($raceID) {
		$container = create_container('skeleton.php', 'council_send_message.php');
		$container['race_id'] = $raceID;
		$container['folder_id'] = MSG_POLITICAL;
		return SmrSession::getNewHREF($container);
	}

	public static function getTraderStatusHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'trader_status.php'));
	}

	public static function getCouncilHREF($raceID = false) {
		$container = create_container('skeleton.php', 'council_list.php');
		if ($raceID !== false) {
			$container['race_id'] = $raceID;
		}
		return SmrSession::getNewHREF($container);
	}

	public static function getTraderRelationsHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'trader_relations.php'));
	}

	public static function getTraderBountiesHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'trader_bounties.php'));
	}

	public static function getPoliticsHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'council_list.php'));
	}

	public static function getCasinoHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'chess.php'));
	}

	public static function getChessHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'chess.php'));
	}

	public static function getChessCreateHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'chess_create_processing.php'));
	}

	public static function getBarMainHREF() {
		global $var;
		$container = create_container('skeleton.php', 'bar_main.php');
		$container['LocationID'] = $var['LocationID'];
		return SmrSession::getNewHREF($container);
	}

	public static function getBarLottoPlayHREF() {
		global $var;
		$container = create_container('skeleton.php', 'bar_lotto_buy.php');
		$container['LocationID'] = $var['LocationID'];
		return SmrSession::getNewHREF($container);
	}

	public static function getBarBlackjackHREF() {
		global $var;
		$container = create_container('skeleton.php', 'bar_gambling_bet.php');
		$container['LocationID'] = $var['LocationID'];
		return SmrSession::getNewHREF($container);
	}

	public static function getBuyMessageNotificationsHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'buy_message_notifications.php'));
	}

	public static function getBuyShipNameHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'buy_ship_name.php'));
	}

	public static function getSectorBBLink($sectorID) {
		return '[sector=' . $sectorID . ']';
	}

	public static function getAvailableTemplates() {
		return array('Default' => array('Default'),
			'Freon22' => array('Default', 'Blue', 'ClassicGreen', 'None'));
	}

	public static function getAvailableColourSchemes($templateName) {
		$templates = self::getAvailableTemplates();
		return $templates[$templateName];
	}

	/**
	 * Returns an array of history databases for which we have ancient saved
	 * game data. Array keys are database class names and values are the
	 * columns in the `account` table with the linked historical account ID's.
	 */
	public static function getHistoryDatabases() {
		if (defined('HISTORY_DATABASES')) {
			return HISTORY_DATABASES;
		} else {
			return array();
		}
	}

}
