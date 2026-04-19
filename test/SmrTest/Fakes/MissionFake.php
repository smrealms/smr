<?php declare(strict_types=1);

namespace SmrTest\Fakes;

use Smr\Mission;
use Smr\MissionActions\EnterSector;
use Smr\MissionStep;
use Smr\Player;

/**
 * Fake Mission class used for testing only.
 */
readonly class MissionFake extends Mission {

	public function __construct(?Player $player = null) {
		assert($player === null); // avoid PHPStan unused argument warning
	}

	public static function isAvailableToPlayer(?Player $player = null): bool {
		return true;
	}

	public function reward(?Player $player = null): string {
		return 'reward';
	}

	public function getStep(int $step): MissionStep {
		return new MissionStep(new EnterSector(1), 'message', 'task');
	}

}
