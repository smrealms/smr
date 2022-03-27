<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

class DefenseWorld extends PlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 800,
			'base_time' => 2700,
			'credit_cost' => 500,
			'exp_gain' => 9,
		],
		PLANET_HANGAR => [
			'max_amount' => 3500,
			'base_time' => 5400,
			'credit_cost' => 500,
			'exp_gain' => 18,
		],
		PLANET_TURRET => [
			'max_amount' => 550,
			'base_time' => 18200,
			'credit_cost' => 5000,
			'exp_gain' => 54,
		],
		PLANET_BUNKER => [
			'max_amount' => 500,
			'base_time' => 2500,
			'credit_cost' => 250,
			'exp_gain' => 9,
		],
	];
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Defense World';
	}
	public function imageLink(): string {
		return 'images/planet4.png';
	}
	public function description(): string {
		return 'A fully armed and operational battle station loaded with excessive firepower.';
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
