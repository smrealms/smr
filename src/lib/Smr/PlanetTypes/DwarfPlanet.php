<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

use Smr\PlanetMenuOption;

class DwarfPlanet extends PlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 10,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 85,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_TURRET => [
			'max_amount' => 5,
			'base_time' => 64800,
			'credit_cost' => 1000000,
			'exp_gain' => 540,
		],
	];
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Dwarf Planet';
	}
	public function imageLink(): string {
		return 'images/planet3.png';
	}
	public function description(): string {
		return 'A smaller than usual planet, with no native life present.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return PlanetMenuOption::cases();
	}

}
