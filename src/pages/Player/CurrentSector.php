<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Player\Planet\Main as PlanetMain;
use Smr\Template;
use Smr\TurnsLevel;
use SmrGame;

class CurrentSector extends PlayerPage {

	use ReusableTrait;

	public string $file = 'current_sector.php';

	/** @var array<int> */
	private array $unreadMissions;
	private ?string $attackMessage = null;

	public function __construct(
		private readonly ?string $message = null,
		private readonly ?string $errorMessage = null,
		private readonly ?string $missionMessage = null,
		private readonly ?string $tradeMessage = null,
		private readonly bool $showForceRefreshMessage = false
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$sector = $player->getSector();

		// If on a planet, forward to planet_main.php
		if ($player->isLandedOnPlanet()) {
			(new PlanetMain($this->message, $this->errorMessage))->go();
		}

		$template->assign('SpaceView', true);

		$template->assign('PageTopic', 'Current Sector: ' . $player->getSectorID() . ' (' . $sector->getGalaxy()->getDisplayName() . ')');

		Menu::navigation($player);

		// *******************************************
		// *
		// * Sector List
		// *
		// *******************************************

		// Sector links
		$links = [];
		$links['Up'] = ['ID' => $sector->getLinkUp()];
		$links['Right'] = ['ID' => $sector->getLinkRight()];
		$links['Down'] = ['ID' => $sector->getLinkDown()];
		$links['Left'] = ['ID' => $sector->getLinkLeft()];
		$links['Warp'] = ['ID' => $sector->getWarp()];

		$unvisited = [];

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id FROM player_visited_sector WHERE sector_id IN (' . $db->escapeArray($links) . ') AND ' . $player->getSQL());
		foreach ($dbResult->records() as $dbRecord) {
			$unvisited[$dbRecord->getInt('sector_id')] = true;
		}

		foreach ($links as $key => $linkArray) {
			if ($linkArray['ID'] > 0 && $linkArray['ID'] != $player->getSectorID()) {
				if ($player->getLastSectorID() == $linkArray['ID']) {
					$class = 'lastVisited';
				} elseif (isset($unvisited[$linkArray['ID']])) {
					$class = 'unvisited';
				} else {
					$class = 'visited';
				}
				$links[$key]['Class'] = $class;
			}
		}

		$template->assign('Sectors', $links);

		doTickerAssigns($template, $player, $db);

		$this->unreadMissions ??= $player->markMissionsRead();
		$template->assign('UnreadMissions', $this->unreadMissions);

		// *******************************************
		// *
		// * Force and other Results
		// *
		// *******************************************
		$game = SmrGame::getGame($player->getGameID());
		if (!$game->hasStarted()) {
			$turnsMessage = 'The game will start in ' . format_time($game->getStartTime() - Epoch::time()) . '!';
		} else {
			$turnsMessage = $player->getTurnsLevel()->message();
			if ($player->getTurnsLevel() === TurnsLevel::None) {
				$turnsMessage .= ' You will gain another turn in ' . format_time($player->getTimeUntilNextTurn()) . '.';
			}
		}
		if (!empty($turnsMessage)) {
			$template->assign('TurnsMessage', $turnsMessage);
		}

		$protectionMessage = '';
		if ($player->getNewbieTurns()) {
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

		if (!empty($protectionMessage)) {
			$template->assign('ProtectionMessage', $protectionMessage);
		}

		//enableProtectionDependantRefresh($template,$player);

		// Do we have an unseen attack message to store in this var?
		$dbResult = $db->read('SELECT * FROM sector_message WHERE ' . $player->getSQL());
		if ($dbResult->hasRecord()) {
			$this->attackMessage = $dbResult->record()->getString('message');
			$db->write('DELETE FROM sector_message WHERE ' . $player->getSQL());
		}

		if ($this->attackMessage !== null) {
			checkForAttackMessage($this->attackMessage, $player);
		}
		if ($this->showForceRefreshMessage) {
			$template->assign('ForceRefreshMessage', getForceRefreshMessage($player));
		}
		if ($this->missionMessage !== null) {
			$template->assign('MissionMessage', $this->missionMessage);
		}
		if ($this->message !== null) {
			$template->assign('VarMessage', bbifyMessage($this->message));
		}

		//error msgs take precedence
		if ($this->errorMessage !== null) {
			$template->assign('ErrorMessage', $this->errorMessage);
		}

		// *******************************************
		// *
		// * Trade Result
		// *
		// *******************************************

		if ($this->tradeMessage !== null) {
			$template->assign('TradeMessage', $this->tradeMessage);
		}

		// *******************************************
		// *
		// * Ports
		// *
		// *******************************************

		if ($sector->hasPort()) {
			$port = $sector->getPort();
			$template->assign('PortIsAtWar', $player->getRelation($port->getRaceID()) < RELATIONS_WAR);
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
		$template->assign('VisiblePlayers', $visiblePlayers);
		$template->assign('CloakedPlayers', $cloakedPlayers);
		$template->assign('SectorPlayersLabel', 'Ships');
	}

}


function getForceRefreshMessage(AbstractSmrPlayer $player): string {
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT refresh_at FROM sector_has_forces WHERE refresh_at > ' . $db->escapeNumber(Epoch::time()) . ' AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND refresher = ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY refresh_at DESC LIMIT 1');
	if ($dbResult->hasRecord()) {
		$remainingTime = $dbResult->record()->getInt('refresh_at') - Epoch::time();
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces will be refreshed in ' . $remainingTime . ' seconds.';
	} else {
		$forceRefreshMessage = '<span class="green">REFRESH</span>: All forces have finished refreshing.';
	}
	return $forceRefreshMessage;
}

function checkForAttackMessage(string $msg, AbstractSmrPlayer $player): void {
	$contains = 0;
	$msg = str_replace('[ATTACK_RESULTS]', '', $msg, $contains);
	if ($contains > 0) {
		// $msg now contains only the log_id, if there is one
		$logID = str2int($msg);

		$template = Template::getInstance();
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($logID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			if ($player->getSectorID() == $dbRecord->getInt('sector_id')) {
				$results = $dbRecord->getObject('result', true);
				$template->assign('AttackResultsType', $dbRecord->getString('type'));
				$template->assign('AttackResults', $results);
				$template->assign('AttackLogLink', linkCombatLog($logID));
			}
		}
	}
}
