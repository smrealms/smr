<?php declare(strict_types=1);

namespace Smr\Missions;

use Smr\BarDrink;
use Smr\Exceptions\MissionNotPossible;
use Smr\Exceptions\MissionStepNotFound;
use Smr\Exceptions\PathNotFound;
use Smr\Mission;
use Smr\MissionActions\BuyDrink;
use Smr\MissionActions\ClaimReward;
use Smr\MissionActions\EnterSector;
use Smr\MissionStep;
use Smr\Player;
use Smr\PlotGroup;
use Smr\Plotter;
use Smr\Race;
use Smr\Sector;

readonly class DrunkGuy extends Mission {

	private const int REWARD_CREDITS = 500_000;
	private const int REWARD_EXP = 1_000;

	private int $gameID;
	private int $startSectorID;
	private string $raceName;
	private string $drinkName;
	private int $hqSectorID;
	private int $barSectorID;

	public static function isAvailableToPlayer(Player $player): bool {
		return $player->getSector()->hasX('Bar');
	}

	public function __construct(
		Player $player,
	) {
		$this->gameID = $player->getGameID();
		$this->startSectorID = $player->getSectorID();

		// Get racial drink based on current sector (so that all players at a
		// given Bar get the same quasi-random race)
		$races = Race::getPlayableNames();
		unset($races[$player->getRaceID()]);
		$raceID = array_keys($races)[$this->startSectorID % count($races)];
		$this->raceName = Race::getName($raceID);
		$this->drinkName = BarDrink::getRacialDrink($raceID);

		// Get sector ID for mission locations
		$hqID = LOCATION_GROUP_RACIAL_HQS + $raceID;
		$this->hqSectorID = $this->pickSector($hqID, $this->startSectorID);
		// Don't go back to the bar we started at (that would be silly)
		$this->barSectorID = $this->pickSector('Bar', $this->hqSectorID, [$this->startSectorID]);
	}

	/**
	 * @param array<int> $excludeSectorIDs Sectors to exclude from picking.
	 */
	private function pickSector(int|string $loc, int $fromSectorID, ?array $excludeSectorIDs = null): int {
		$toFind = Plotter::getX(PlotGroup::Locations, $loc, $this->gameID);
		$fromSector = Sector::getSector($this->gameID, $fromSectorID);
		try {
			$path = Plotter::findDistanceToX(
				x: $toFind,
				sector: $fromSector,
				useFirst: true,
				excludeSectorIDs: $excludeSectorIDs,
			);
		} catch (PathNotFound) {
			throw new MissionNotPossible();
		}
		return $path->getEndSectorID();
	}

	public function getStep(int $step): MissionStep {
		return match ($step) {
			0 => new MissionStep(
				message: '<i>*Hiccup*</i> Hey! I need you to...<i>*Hiccup*</i> do me a favor. All the ' . $this->drinkName . ' in this bar is awful! Go to the ' . trim(substr($this->raceName, 0, 3)) . '...<i>*Hiccup*</i>...the ' . $this->raceName . ' HQ, they\'ll know a good bar.',
				task: 'Go to the ' . $this->raceName . ' HQ at [sector=' . $this->hqSectorID . ']',
				requirement: new EnterSector($this->hqSectorID),
			),
			1 => new MissionStep(
				message: 'Here we are! The ' . $this->raceName . ' HQ! You ask around a bit and find that the bar in [sector=' . $this->barSectorID . '] does the best ' . $this->drinkName . ' around!',
				task: 'Go to the bar at [sector=' . $this->barSectorID . '] and buy a ' . $this->drinkName . ' from the bartender. This may take many tries.',
				requirement: new EnterSector($this->barSectorID),
			),
			2 => new MissionStep(
				message: 'This is the bar! Now let\'s get this ' . $this->drinkName . '.',
				task: 'Enter the bar at [sector=' . $this->barSectorID . '] and buy a ' . $this->drinkName . ' from the bartender. This may take many tries.',
				requirement: new BuyDrink($this->barSectorID, $this->drinkName),
			),
			3 => new MissionStep(
				message: 'Finally! A true ' . $this->drinkName . ', let\'s return to that drunk!',
				task: 'Return to [sector=' . $this->startSectorID . '] to deliver the ' . $this->drinkName . ' and claim your reward.',
				requirement: new EnterSector($this->startSectorID),
			),
			4 => new MissionStep(
				message: 'You hand the ' . $this->drinkName . ' to the drunk!',
				task: 'Claim your reward in sector [sector=' . $this->startSectorID . ']',
				requirement: new ClaimReward($this->startSectorID),
			),
			default => throw new MissionStepNotFound(),
		};
	}

	public function reward(Player $player): string {
		$credits = self::REWARD_CREDITS;
		$player->increaseCredits($credits);
		$exp = self::REWARD_EXP;
		$player->increaseExperience($exp);
		return '<i>*Hiccup*</i> For your...service <i>*Hiccup*</i> to me, take this as a reward <i>*Hiccup*</i> ...<br /><br />They trail off incoherently.<br /><br />You gain <span class="creds">' . number_format($credits) . '</span> ' . pluralise($credits, 'credit', false) . ' and <span class="exp">' . number_format($exp) . '</span> ' . pluralise($exp, 'experience point', false) . '!';
	}

}
