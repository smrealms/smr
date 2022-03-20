<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

class TerranPlanet extends PlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 100,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_TURRET => [
			'max_amount' => 10,
			'base_time' => 64800,
			'credit_cost' => 1000000,
			'exp_gain' => 540,
		],
	];
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Terran Planet';
	}
	public function imageLink(): string {
		return 'images/planet1.png';
	}
	public function description(): string {
		return 'A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms.';
	}
	public function maxAttackers(): int {
		return 10;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return self::DEFAULT_MENU_OPTIONS;
	}

}
