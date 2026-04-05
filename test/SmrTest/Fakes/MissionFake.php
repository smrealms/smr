<?php declare(strict_types=1);

namespace SmrTest\Fakes;

use Smr\AbstractPlayer;
use Smr\Mission;
use Smr\MissionActions\EnterSector;
use Smr\MissionStep;

/**
 * Fake Mission class used for testing only.
 */
readonly class MissionFake extends Mission {

	public function __construct(?AbstractPlayer $player = null) {
		assert($player === null); // avoid PHPStan unused argument warning
	}

	public static function isAvailableToPlayer(?AbstractPlayer $player = null): bool {
		return true;
	}

	public function reward(?AbstractPlayer $player = null): string {
		return 'reward';
	}

	public function getStep(int $step): MissionStep {
		return new MissionStep(new EnterSector(1), 'message', 'task');
	}

}
