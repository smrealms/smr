<?php declare(strict_types=1);

use Smr\Database;
use Smr\Exceptions\UserError;
use Smr\Pages\Account\BugReportProcessor;
use Smr\Pages\Account\BuyMessageNotifications;
use Smr\Pages\Account\FeatureRequest;
use Smr\Pages\Account\NewsReadAdvanced;
use Smr\Pages\Player\AllianceBroadcast;
use Smr\Pages\Player\AllianceForces;
use Smr\Pages\Player\AllianceList;
use Smr\Pages\Player\AllianceMessageBoard;
use Smr\Pages\Player\AllianceMotd;
use Smr\Pages\Player\AllianceOptions;
use Smr\Pages\Player\AllianceRoster;
use Smr\Pages\Player\AttackPlayerProcessor;
use Smr\Pages\Player\Bank\AllianceBank;
use Smr\Pages\Player\BetaFunctions\BetaFunctions;
use Smr\Pages\Player\BuyShipName;
use Smr\Pages\Player\Chess\MatchList;
use Smr\Pages\Player\Chess\MatchStartProcessor;
use Smr\Pages\Player\Council\MessageCouncil;
use Smr\Pages\Player\Council\ViewCouncil;
use Smr\Pages\Player\CurrentPlayers;
use Smr\Pages\Player\CurrentSector;
use Smr\Pages\Player\ListPlanetDefense;
use Smr\Pages\Player\ListPlanetFinancial;
use Smr\Pages\Player\LocalMap;
use Smr\Pages\Player\MessageBlacklist;
use Smr\Pages\Player\MessageBox;
use Smr\Pages\Player\MessageSend;
use Smr\Pages\Player\PlotCourse;
use Smr\Pages\Player\PlotCourseConventionalProcessor;
use Smr\Pages\Player\SectorMoveProcessor;
use Smr\Pages\Player\SectorScan;
use Smr\Pages\Player\SectorsFileDownloadProcessor;
use Smr\Pages\Player\ShopGoods;
use Smr\Pages\Player\TraderBounties;
use Smr\Pages\Player\TraderRelations;
use Smr\Pages\Player\TraderStatus;
use Smr\Pages\Player\WeaponReorderProcessor;
use Smr\Race;

class Globals {

	/** @var array<int> */
	protected static array $HIDDEN_PLAYERS;
	/** @var array<int, array<string, string|int>> */
	protected static array $LEVEL_REQUIREMENTS;
	protected static bool $FEATURE_REQUEST_OPEN;
	/** @var array<int, array<int, array<int, int>>> */
	protected static array $RACE_RELATIONS;
	/** @var array<string, string> */
	protected static array $AVAILABLE_LINKS = [];
	protected static Database $db;

	protected static function initialiseDatabase(): void {
		if (!isset(self::$db)) {
			self::$db = Database::getInstance();
		}
	}

	/**
	 * @return array<string, string>
	 */
	public static function getAvailableLinks(): array {
		return self::$AVAILABLE_LINKS;
	}

	/**
	 * @param array<string, int> $extraInfo
	 */
	public static function canAccessPage(string $pageName, AbstractSmrPlayer $player, array $extraInfo): void {
		switch ($pageName) {
			case 'AllianceMOTD':
				if ($player->getAllianceID() != $extraInfo['AllianceID']) {
					logException(new Exception('Tried to access page without permission.'));
					throw new UserError('You cannot access this page.');
				}
				break;
		}
	}

	/**
	 * @return array<int>
	 */
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

	/**
	 * @return array<int>
	 */
	public static function getGalacticPostEditorIDs(int $gameID): array {
		self::initialiseDatabase();
		$editorIDs = [];
		$dbResult = self::$db->read('SELECT account_id FROM galactic_post_writer WHERE position=\'editor\' AND game_id=' . self::$db->escapeNumber($gameID));
		foreach ($dbResult->records() as $dbRecord) {
			$editorIDs[] = $dbRecord->getInt('account_id');
		}
		return $editorIDs;
	}

	/**
	 * @return array<int, array<string, string|int>>
	 */
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
		$raceName = get_colored_text($relations, Race::getName($raceID));
		if ($linked === true) {
			$container = new ViewCouncil($raceID);
			$raceName = create_link($container, $raceName);
		}
		return $raceName;
	}

	public static function isFeatureRequestOpen(): bool {
		if (!isset(self::$FEATURE_REQUEST_OPEN)) {
			self::initialiseDatabase();
			$dbResult = self::$db->read('SELECT open FROM open_forms WHERE type=\'FEATURE\'');

			self::$FEATURE_REQUEST_OPEN = $dbResult->record()->getBoolean('open');
		}
		return self::$FEATURE_REQUEST_OPEN;
	}

	/**
	 * @return array<int, int>
	 */
	public static function getRaceRelations(int $gameID, int $raceID): array {
		if (!isset(self::$RACE_RELATIONS[$gameID][$raceID])) {
			self::initialiseDatabase();
			//get relations
			self::$RACE_RELATIONS[$gameID][$raceID] = [];
			foreach (Race::getAllIDs() as $otherRaceID) {
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
		return (new FeatureRequest())->href();
	}

	public static function getCurrentSectorHREF(): string {
		return self::$AVAILABLE_LINKS['CurrentSector'] = (new CurrentSector())->href();
	}

	public static function getLocalMapHREF(): string {
		return self::$AVAILABLE_LINKS['LocalMap'] = (new LocalMap())->href();
	}

	public static function getCurrentPlayersHREF(): string {
		return self::$AVAILABLE_LINKS['CurrentPlayers'] = (new CurrentPlayers())->href();
	}

	public static function getTradeHREF(): string {
		return self::$AVAILABLE_LINKS['EnterPort'] = (new ShopGoods())->href();
	}

	public static function getAttackTraderHREF(int $accountID): string {
		$container = new AttackPlayerProcessor($accountID);
		return self::$AVAILABLE_LINKS['AttackTrader'] = $container->href();
	}

	public static function getBetaFunctionsHREF(): string { //BETA
		return (new BetaFunctions())->href();
	}

	public static function getBugReportProcessingHREF(): string {
		return (new BugReportProcessor())->href();
	}

	public static function getWeaponReorderHREF(int $weaponOrderID, string $direction): string {
		$container = new WeaponReorderProcessor($weaponOrderID, $direction);
		return $container->href();
	}

	public static function getSmrFileCreateHREF(): string {
		$container = new SectorsFileDownloadProcessor();
		return $container->href();
	}

	public static function getCurrentSectorMoveHREF(AbstractSmrPlayer $player, int $toSector): string {
		return self::getSectorMoveHREF($player, $toSector, new CurrentSector());
	}

	public static function getSectorMoveHREF(AbstractSmrPlayer $player, int $toSector, CurrentSector|LocalMap $targetPage): string {
		$container = new SectorMoveProcessor($toSector, $targetPage);
		return self::$AVAILABLE_LINKS['Move' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getSectorScanHREF(AbstractSmrPlayer $player, int $toSector): string {
		$container = new SectorScan($toSector);
		return self::$AVAILABLE_LINKS['Scan' . $player->getSector()->getSectorDirection($toSector)] = $container->href();
	}

	public static function getPlotCourseHREF(int $fromSector = null, int $toSector = null): string {
		if ($fromSector === null && $toSector === null) {
			return self::$AVAILABLE_LINKS['PlotCourse'] = (new PlotCourse())->href();
		}
		return (new PlotCourseConventionalProcessor(from: $fromSector, to: $toSector))->href();
	}

	public static function getAllianceHREF(int $allianceID = null): string {
		if ($allianceID > 0) {
			return self::getAllianceMotdHREF($allianceID);
		}
		return self::getAllianceListHREF();
	}

	public static function getAllianceBankHREF(int $allianceID): string {
		$container = new AllianceBank($allianceID);
		return $container->href();
	}

	public static function getAllianceRosterHREF(int $allianceID = null): string {
		return (new AllianceRoster($allianceID))->href();
	}

	public static function getAllianceListHREF(): string {
		return (new AllianceList())->href();
	}

	public static function getAllianceNewsHREF(int $gameID, int $allianceID): string {
		return (new NewsReadAdvanced(gameID: $gameID, submit: 'Search For Alliance', allianceIDs: [$allianceID]))->href();
	}

	public static function getAllianceMotdHREF(int $allianceID): string {
		return (new AllianceMotd($allianceID))->href();
	}

	public static function getAllianceMessageHREF(int $allianceID): string {
		return (new AllianceBroadcast($allianceID))->href();
	}

	public static function getAllianceMessageBoardHREF(int $allianceID): string {
		return (new AllianceMessageBoard($allianceID))->href();
	}

	public static function getAllianceForcesHREF(int $allianceID): string {
		return (new AllianceForces($allianceID))->href();
	}

	public static function getAllianceOptionsHREF(): string {
		return (new AllianceOptions())->href();
	}

	public static function getPlanetListHREF(int $allianceID): string {
		return (new ListPlanetDefense($allianceID))->href();
	}

	public static function getPlanetListFinancialHREF(int $allianceID): string {
		return (new ListPlanetFinancial($allianceID))->href();
	}

	public static function getViewMessageBoxesHREF(): string {
		return (new MessageBox())->href();
	}

	public static function getSendGlobalMessageHREF(): string {
		return (new MessageSend())->href();
	}

	public static function getManageBlacklistHREF(): string {
		return (new MessageBlacklist())->href();
	}

	public static function getSendCouncilMessageHREF(int $raceID): string {
		$container = new MessageCouncil($raceID);
		return $container->href();
	}

	public static function getTraderStatusHREF(): string {
		return (new TraderStatus())->href();
	}

	public static function getCouncilHREF(int $raceID): string {
		$container = new ViewCouncil($raceID);
		return $container->href();
	}

	public static function getTraderRelationsHREF(): string {
		return (new TraderRelations())->href();
	}

	public static function getTraderBountiesHREF(): string {
		return (new TraderBounties())->href();
	}

	public static function getCasinoHREF(): string {
		return (new MatchList())->href();
	}

	public static function getChessHREF(): string {
		return (new MatchList())->href();
	}

	public static function getChessCreateHREF(): string {
		return (new MatchStartProcessor())->href();
	}

	public static function getBuyMessageNotificationsHREF(): string {
		return (new BuyMessageNotifications())->href();
	}

	public static function getBuyShipNameHREF(): string {
		return (new BuyShipName())->href();
	}

	/**
	 * @return array<string, int>
	 */
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

	/**
	 * @return array<string>
	 */
	public static function getAvailableTemplates(): array {
		return array_keys(CSS_URLS);
	}

	/**
	 * @return array<string>
	 */
	public static function getAvailableColourSchemes(string $templateName): array {
		return array_keys(CSS_COLOUR_URLS[$templateName]);
	}

	/**
	 * Returns an array of history databases for which we have ancient saved
	 * game data. Array keys are database names and values are the columns in
	 * the `account` table with the linked historical account ID's.
	 *
	 * @return array<string, string>
	 */
	public static function getHistoryDatabases(): array {
		if (!defined('HISTORY_DATABASES')) {
			return [];
		}
		return HISTORY_DATABASES;
	}

}
