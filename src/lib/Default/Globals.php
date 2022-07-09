<?php declare(strict_types=1);

class Globals {

	protected static array $HIDDEN_PLAYERS;
	protected static array $LEVEL_REQUIREMENTS;
	protected static array $GOODS;
	protected static array $HARDWARE_TYPES;
	protected static bool $FEATURE_REQUEST_OPEN;
	protected static array $RACE_RELATIONS;
	protected static array $AVAILABLE_LINKS = [];
	protected static Smr\Database $db;

	protected static function initialiseDatabase(): void {
		if (!isset(self::$db)) {
			self::$db = Smr\Database::getInstance();
		}
	}

	public static function getAvailableLinks(): array {
		return self::$AVAILABLE_LINKS;
	}

	public static function canAccessPage(string $pageName, AbstractSmrPlayer $player, array $extraInfo): void {
		switch ($pageName) {
			case 'AllianceMOTD':
				if ($player->getAllianceID() != $extraInfo['AllianceID']) {
					logException(new Exception('Tried to access page without permission.'));
					throw new Smr\Exceptions\UserError('You cannot access this page.');
				}
				break;
		}
	}

	public static function getHiddenPlayers(): array {
		if (!isset(self::$HIDDEN_PLAYERS)) {
			self::initialiseDatabase();
			$dbResult = self::$db->read('SELECT account_id FROM hidden_players');
			self::$HIDDEN_PLAYERS = [0]; //stop errors
			foreach ($dbResult->records() as $dbRecord) {
				self::$HIDDEN_PLAYERS[] = $dbRecord->getInt('account_id');
			}
		}
		return self::$HIDDEN_PLAYERS;
	}

	public static function getGalacticPostEditorIDs(int $gameID): array {
		self::initialiseDatabase();
		$editorIDs = [];
		$dbResult = self::$db->read('SELECT account_id FROM galactic_post_writer WHERE position=\'editor\' AND game_id=' . self::$db->escapeNumber($gameID));
		foreach ($dbResult->records() as $dbRecord) {
			$editorIDs[] = $dbRecord->getInt('account_id');
		}
		return $editorIDs;
	}

	public static function getLevelRequirements(): array {
		if (!isset(self::$LEVEL_REQUIREMENTS)) {
			self::initialiseDatabase();
			self::$LEVEL_REQUIREMENTS = [];

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM level ORDER BY level_id ASC');
			foreach ($dbResult->records() as $dbRecord) {
				self::$LEVEL_REQUIREMENTS[$dbRecord->getInt('level_id')] = [
					'Name' => $dbRecord->getString('level_name'),
					'Requirement' => $dbRecord->getInt('requirement'),
				];
			}
		}
		return self::$LEVEL_REQUIREMENTS;
	}

	public static function getColouredRaceNameForRace(int $raceID, int $gameID, int $fromRaceID, bool $linked = true): string {
		$raceRelations = self::getRaceRelations($gameID, $fromRaceID);
		return self::getColouredRaceName($raceID, $raceRelations[$raceID], $linked);
	}

	public static function getColouredRaceName(int $raceID, int $relations, bool $linked = true): string {
		$raceName = get_colored_text($relations, Smr\Race::getName($raceID));
		if ($linked === true) {
			$container = Page::create('council_list.php', ['race_id' => $raceID]);
			$raceName = create_link($container, $raceName);
		}
		return $raceName;
	}

	public static function getGoods(): array {
		if (!isset(self::$GOODS)) {
			self::initialiseDatabase();
			self::$GOODS = [];

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM good ORDER BY good_id');
			foreach ($dbResult->records() as $dbRecord) {
				self::$GOODS[$dbRecord->getInt('good_id')] = [
					'Type' => 'Good',
					'ID' => $dbRecord->getInt('good_id'),
					'Name' => $dbRecord->getString('good_name'),
					'Max' => $dbRecord->getInt('max_amount'),
					'BasePrice' => $dbRecord->getInt('base_price'),
					'Class' => $dbRecord->getInt('good_class'),
					'ImageLink' => 'images/port/' . $dbRecord->getInt('good_id') . '.png',
					'AlignRestriction' => $dbRecord->getInt('align_restriction'),
				];
			}
		}
		return self::$GOODS;
	}

	public static function getGood(int $goodID): array {
		return self::getGoods()[$goodID];
	}

	public static function getGoodName(int $goodID): string {
		if ($goodID == GOODS_NOTHING) {
			return 'Nothing';
		}
		return self::getGoods()[$goodID]['Name'];
	}

	public static function getHardwareTypes(int $hardwareTypeID = null): array {
		if (!isset(self::$HARDWARE_TYPES)) {
			self::initialiseDatabase();
			self::$HARDWARE_TYPES = [];

			// determine user level
			$dbResult = self::$db->read('SELECT * FROM hardware_type ORDER BY hardware_type_id');
			foreach ($dbResult->records() as $dbRecord) {
				self::$HARDWARE_TYPES[$dbRecord->getInt('hardware_type_id')] = [
					'Type' => 'Hardware',
					'ID' => $dbRecord->getInt('hardware_type_id'),
					'Name' => $dbRecord->getString('hardware_name'),
					'Cost' => $dbRecord->getInt('cost'),
				];
			}
		}
		if ($hardwareTypeID === null) {
			return self::$HARDWARE_TYPES;
		}
		return self::$HARDWARE_TYPES[$hardwareTypeID];
	}

	public static function getHardwareName(int $hardwareTypeID): string {
		return self::getHardwareTypes()[$hardwareTypeID]['Name'];
	}

	public static function getHardwareCost(int $hardwareTypeID): int {
		return self::getHardwareTypes()[$hardwareTypeID]['Cost'];
	}

	public static function isFeatureRequestOpen(): bool {
		if (!isset(self::$FEATURE_REQUEST_OPEN)) {
			self::initialiseDatabase();
			$dbResult = self::$db->read('SELECT open FROM open_forms WHERE type=\'FEATURE\'');

			self::$FEATURE_REQUEST_OPEN = $dbResult->record()->getBoolean('open');
		}
		return self::$FEATURE_REQUEST_OPEN;
	}

	public static function getRaceRelations(int $gameID, int $raceID): array {
		if (!isset(self::$RACE_RELATIONS[$gameID][$raceID])) {
			self::initialiseDatabase();
			//get relations
			self::$RACE_RELATIONS[$gameID][$raceID] = [];
			foreach (Smr\Race::getAllIDs() as $otherRaceID) {
				self::$RACE_RELATIONS[$gameID][$raceID][$otherRaceID] = 0;
			}
			$dbResult = self::$db->read('SELECT race_id_2,relation FROM race_has_relation WHERE race_id_1=' . self::$db->escapeNumber($raceID) . ' AND game_id=' . self::$db->escapeNumber($gameID));
			foreach ($dbResult->records() as $dbRecord) {
				self::$RACE_RELATIONS[$gameID][$raceID][$dbRecord->getInt('race_id_2')] = $dbRecord->getInt('relation');
			}
		}
		return self::$RACE_RELATIONS[$gameID][$raceID];
	}

	public static function getFeatureRequestHREF(): string {
		return Page::create('feature_request.php')->href();
	}

	public static function getCurrentSectorHREF(): string {
		return self::$AVAILABLE_LINKS['CurrentSector'] = Page::create('current_sector.php')->href();
	}

	public static function getLocalMapHREF(): string {
		return self::$AVAILABLE_LINKS['LocalMap'] = Page::create('map_local.php')->href();
	}

	public static function getCurrentPlayersHREF(): string {
		return self::$AVAILABLE_LINKS['CurrentPlayers'] = Page::create('current_players.php')->href();
	}

	public static function getTradeHREF(): string {
		return self::$AVAILABLE_LINKS['EnterPort'] = Page::create('shop_goods.php')->href();
	}

	public static function getAttackTraderHREF(int $accountID): string {
		$container = Page::create('trader_attack_processing.php');
		$container['target'] = $accountID;
		return self::$AVAILABLE_LINKS['AttackTrader'] = $container->href();
	}

	public static function getBetaFunctionsHREF(): string { //BETA
		return Page::create('beta_functions.php')->href();
	}

	public static function getBugReportProcessingHREF(): string {
		return Page::create('bug_report_processing.php')->href();
	}

	public static function getWeaponReorderHREF(int $weaponOrderID, string $direction): string {
		$container = Page::create('weapon_reorder_processing.php');
		$container[$direction] = $weaponOrderID;
		return $container->href();
	}

	public static function getSmrFileCreateHREF(int $adminCreateGameID = null): string {
		$container = Page::create('smr_file_create.php');
		$container['AdminCreateGameID'] = $adminCreateGameID;
		return $container->href();
	}

	public static function getCurrentSectorMoveHREF(AbstractSmrPlayer $player, int $toSector): string {
		return self::getSectorMoveHREF($player, $toSector, 'current_sector.php');
	}

	public static function getSectorMoveHREF(AbstractSmrPlayer $player, int $toSector, string $targetPage): string {
		$container = Page::create('sector_move_processing.php');
		$container['target_page'] = $targetPage;
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Move' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getSectorScanHREF(AbstractSmrPlayer $player, int $toSector): string {
		$container = Page::create('sector_scan.php');
		$container['target_sector'] = $toSector;
		return self::$AVAILABLE_LINKS['Scan' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getPlotCourseHREF(int $fromSector = null, int $toSector = null): string {
		if ($fromSector === null && $toSector === null) {
			return self::$AVAILABLE_LINKS['PlotCourse'] = Page::create('course_plot.php')->href();
		}
		return Page::create('course_plot_processing.php', ['from' => $fromSector, 'to' => $toSector])->href();
	}

	public static function getPlanetMainHREF(): string {
		return Page::create('planet_main.php')->href();
	}

	public static function getPlanetConstructionHREF(): string {
		return Page::create('planet_construction.php')->href();
	}

	public static function getPlanetDefensesHREF(): string {
		return Page::create('planet_defense.php')->href();
	}

	public static function getPlanetOwnershipHREF(): string {
		return Page::create('planet_ownership.php')->href();
	}

	public static function getPlanetStockpileHREF(): string {
		return Page::create('planet_stockpile.php')->href();
	}

	public static function getPlanetFinancesHREF(): string {
		return Page::create('planet_financial.php')->href();
	}

	public static function getAllianceHREF(int $allianceID = null): string {
		if ($allianceID > 0) {
			return self::getAllianceMotdHREF($allianceID);
		}
		return self::getAllianceListHREF();
	}

	public static function getAllianceBankHREF(int $allianceID = null): string {
		$container = Page::create('bank_alliance.php');
		$container['alliance_id'] = $allianceID;
		return $container->href();
	}

	public static function getAllianceRosterHREF(int $allianceID = null): string {
		$container = Page::create('alliance_roster.php');
		$container['alliance_id'] = $allianceID;
		return $container->href();
	}

	public static function getAllianceListHREF(): string {
		return Page::create('alliance_list.php')->href();
	}

	public static function getAllianceNewsHREF(int $allianceID): string {
		return Page::create('news_read_advanced.php', ['allianceID' => $allianceID, 'submit' => 'Search For Alliance'])->href();
	}

	public static function getAllianceMotdHREF(int $allianceID): string {
		return Page::create('alliance_mod.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getAllianceMessageHREF(int $allianceID): string {
		return Page::create('alliance_broadcast.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getAllianceMessageBoardHREF(int $allianceID): string {
		return Page::create('alliance_message.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getAllianceForcesHREF(int $allianceID): string {
		return Page::create('alliance_forces.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getAllianceOptionsHREF(int $allianceID): string {
		return Page::create('alliance_option.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getPlanetListHREF(int $allianceID): string {
		return Page::create('planet_list.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getPlanetListFinancialHREF(int $allianceID): string {
		return Page::create('planet_list_financial.php', ['alliance_id' => $allianceID])->href();
	}

	public static function getViewMessageBoxesHREF(): string {
		return Page::create('message_box.php')->href();
	}

	public static function getSendGlobalMessageHREF(): string {
		return Page::create('message_send.php')->href();
	}

	public static function getManageBlacklistHREF(): string {
		return Page::create('message_blacklist.php')->href();
	}

	public static function getSendCouncilMessageHREF(int $raceID): string {
		$container = Page::create('council_send_message.php');
		$container['race_id'] = $raceID;
		$container['folder_id'] = MSG_POLITICAL;
		return $container->href();
	}

	public static function getTraderStatusHREF(): string {
		return Page::create('trader_status.php')->href();
	}

	public static function getCouncilHREF(int $raceID = null): string {
		$container = Page::create('council_list.php');
		$container['race_id'] = $raceID;
		return $container->href();
	}

	public static function getTraderRelationsHREF(): string {
		return Page::create('trader_relations.php')->href();
	}

	public static function getTraderBountiesHREF(): string {
		return Page::create('trader_bounties.php')->href();
	}

	public static function getPoliticsHREF(): string {
		return Page::create('council_list.php')->href();
	}

	public static function getCasinoHREF(): string {
		return Page::create('chess.php')->href();
	}

	public static function getChessHREF(): string {
		return Page::create('chess.php')->href();
	}

	public static function getChessCreateHREF(): string {
		return Page::create('chess_create_processing.php')->href();
	}

	public static function getBarMainHREF(): string {
		$container = Page::create('bar_main.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBarLottoPlayHREF(): string {
		$container = Page::create('bar_lotto_buy.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBarBlackjackHREF(): string {
		$container = Page::create('bar_gambling_bet.php');
		$container->addVar('LocationID');
		return $container->href();
	}

	public static function getBuyMessageNotificationsHREF(): string {
		return Page::create('buy_message_notifications.php')->href();
	}

	public static function getBuyShipNameHREF(): string {
		return Page::create('buy_ship_name.php')->href();
	}

	public static function getBuyShipNameCosts(): array {
		return [
			'text' => CREDITS_PER_TEXT_SHIP_NAME,
			'html' => CREDITS_PER_HTML_SHIP_NAME,
			'logo' => CREDITS_PER_SHIP_LOGO,
		];
	}

	public static function getSectorBBLink(int $sectorID): string {
		return '[sector=' . $sectorID . ']';
	}

	public static function getAvailableTemplates(): array {
		return array_keys(CSS_URLS);
	}

	public static function getAvailableColourSchemes(string $templateName): array {
		return array_keys(CSS_COLOUR_URLS[$templateName]);
	}

	/**
	 * Returns an array of history databases for which we have ancient saved
	 * game data. Array keys are database names and values are the columns in
	 * the `account` table with the linked historical account ID's.
	 */
	public static function getHistoryDatabases(): array {
		if (!defined('HISTORY_DATABASES')) {
			return [];
		}
		return HISTORY_DATABASES;
	}

}
