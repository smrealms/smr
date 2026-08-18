<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\FullCombatResults;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Player\Planet\Main as PlanetMain;
use Smr\Player;
use Smr\Template;
use Smr\TurnsLevel;

class CurrentSector extends PlayerPage {

	use ReusableTrait;
	private ?string $attackMessage = null;

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
		private readonly ?string $missionMessage = null,
		private readonly ?string $tradeMessage = null,
		private readonly bool $showForceRefreshMessage = false,
	) {}

	public function build(Player $player, Template $template): void {
		$sector = $player->getSector();

		// If on a planet, forward to planet_main.php
		if ($player->isLandedOnPlanet()) {
			(new PlanetMain($this->message, $this->errorMessage))->go();
		}

		$template->spaceView = true;

		$template->pageTopic = 'Current Sector: ' . $player->getSectorID() . ' (' . $sector->getGalaxy()->getDisplayName() . ')';

		Menu::navigation($player);

		// *******************************************
		// *
		// * Sector List
		// *
		// *******************************************

		// Sector links
		$linkSectorIDs = [
			'Up' => $sector->getLinkUp(),
			'Right' => $sector->getLinkRight(),
			'Down' => $sector->getLinkDown(),
			'Left' => $sector->getLinkLeft(),
			'Warp' => $sector->getWarp(),
		];

		$unvisited = [];

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (:sector_ids) AND ' . Player::SQL, [
			...$player->SQLID,
			'sector_ids' => $db->escapeArray($linkSectorIDs),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$unvisited[$dbRecord->getInt('sector_id')] = true;
		}

		$links = [];
		foreach ($linkSectorIDs as $dir => $linkSectorID) {
			$links[$dir] = ['ID' => $linkSectorID, 'Class' => ''];
			if ($linkSectorID > 0 && $linkSectorID !== $player->getSectorID()) {
				if ($player->getLastSectorID() === $linkSectorID) {
					$class = 'lastVisited';
				} elseif (isset($unvisited[$linkSectorID])) {
					$class = 'unvisited';
				} else {
					$class = 'visited';
				}
				$links[$dir]['Class'] = $class;
			}
		}

		$ticker = getDisplayTickers($template, $player, $db);

		$unreadMissions = [];
		foreach ($player->getActiveMissionStates() as $missionID => $missionState) {
			$unreadMissions[$missionID] = $missionState->getUnreadMessage();
		}

		// *******************************************
		// *
		// * Force and other Results
		// *
		// *******************************************
		$game = Game::getGame($player->getGameID());
		if (!$game->hasStarted()) {
			$turnsMessage = 'The game will start in ' . format_time($game->getStartTime() - Epoch::time()) . '!';
		} else {
			$turnsMessage = $player->getTurnsLevel()->message();
			if ($turnsMessage !== null && $player->getTurnsLevel() === TurnsLevel::None) {
				$turnsMessage .= ' You will gain another turn in ' . format_time($player->getTimeUntilNextTurn()) . '.';
			}
		}

		$protectionMessage = null;
		if ($player->getNewbieTurns() > 0) {
			if ($player->getNewbieTurns() < 25) {
				$protectionMessage = '<span class="blue">PROTECTION</span>: You are almost out of <span class="green">NEWBIE</span> protection.';
			} else {
				$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="green">NEWBIE</span> protection.';
			}
		} elseif ($player->hasFederalProtection()) {
			$protectionMessage = '<span class="blue">PROTECTION</span>: You are under <span class="blue">FEDERAL</span> protection.';
		} elseif ($sector->offersFederalProtection()) {
			$protectionMessage = '<span class="blue">PROTECTION</span>: You are <span class="red">NOT</span> under protection.';
		}

		//enableProtectionDependantRefresh($template,$player);

		// Do we have an unseen attack message to store in this var?
		$dbResult = $db->select('sector_message', $player->SQLID);
		if ($dbResult->hasRecord()) {
			$this->attackMessage = $dbResult->record()->getString('message');
			$db->delete('sector_message', $player->SQLID);
		}

		// *******************************************
		// *
		// * Ports
		// *
		// *******************************************

		if ($sector->hasPort()) {
			$port = $sector->getPort();
			$portIsAtWar = $player->getRelation($port->getRaceID()) < RELATIONS_WAR;
		} else {
			$portIsAtWar = null;
		}

		// *******************************************
		// *
		// * Ships
		// *
		// *******************************************
		$otherPlayers = $sector->getOtherTraders($player);
		$visiblePlayers = [];
		$cloakedPlayers = [];
		foreach ($otherPlayers as $accountID => $otherPlayer) {
			if ($player->canSee($otherPlayer)) {
				$visiblePlayers[$accountID] = $otherPlayer;
			} else {
				$cloakedPlayers[$accountID] = $otherPlayer;
			}
		}

		$template->pageRenderer = fn() => CurrentSectorRenderer::render(
			template: $template,
			Sectors: $links,
			UnreadMissions: $unreadMissions,
			TurnsMessage: $turnsMessage,
			ProtectionMessage: $protectionMessage,
			ForceRefreshMessage: getForceRefreshMessage($this->showForceRefreshMessage, $player),
			MissionMessage: $this->missionMessage,
			VarMessage: $this->message,
			ErrorMessage: $this->errorMessage,
			TradeMessage: $this->tradeMessage,
			PortIsAtWar: $portIsAtWar,
			VisiblePlayers: $visiblePlayers,
			CloakedPlayers: $cloakedPlayers,
			SectorPlayersLabel: 'Ships',
			AttackResults: checkForAttackMessage($this->attackMessage, $player),
			ThisAccount: $player->getAccount(),
			ThisPlanet: $sector->hasPlanet() ? $sector->getPlanet() : null,
			ThisPlayer: $player,
			ThisSector: $sector,
			ThisShip: $player->getShip(),
			Ticker: $ticker,
		);
	}

}

function getForceRefreshMessage(bool $showMessage, Player $player): ?string {
	if (!$showMessage) {
		return null;
	}
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT refresh_at FROM sector_has_forces WHERE refresh_at > :now AND sector_id = :sector_id AND game_id = :game_id AND refresher = :account_id ORDER BY refresh_at DESC LIMIT 1', [
		'now' => $db->escapeNumber(Epoch::time()),
		'sector_id' => $db->escapeNumber($player->getSectorID()),
		...$player->SQLID,
	]);
	if ($dbResult->hasRecord()) {
		$remainingTime = $dbResult->record()->getInt('refresh_at') - Epoch::time();
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in ' . $remainingTime . ' seconds.';
	} else {
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
	}
	return $forceRefreshMessage;
}

/**
 * @return ?array{Results: FullCombatResults, Link: string}
 */
function checkForAttackMessage(?string $msg, Player $player): ?array {
	if ($msg === null) {
		return null;
	}
	$contains = 0;
	$msg = str_replace('[ATTACK_RESULTS]', '', $msg, $contains);
	if ($contains > 0) {
		// $msg now contains only the log_id, if there is one
		$logID = str2int($msg);

		$db = Database::getInstance();
		$dbResult = $db->select('combat_logs', ['log_id' => $logID], ['sector_id', 'result']);
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			if ($player->getSectorID() === $dbRecord->getInt('sector_id')) {
				return [
					'Results' => $dbRecord->getClass('result', FullCombatResults::class, true),
					'Link' => linkCombatLog($logID),
				];
			}
		}
	}
	return null;
}
