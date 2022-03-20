<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

class AridPlanet extends PlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_BUNKER => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 50000,
			'exp_gain' => 90,
		],
		PLANET_TURRET => [
			'max_amount' => 15,
			'base_time' => 21600,
			'credit_cost' => 750000,
			'exp_gain' => 180,
		],
	];
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Arid Planet';
	}
	public function imageLink(): string {
		return 'images/planet2.png';
	}
	public function description(): string {
		return 'A world mostly devoid of surface water, but capable of supporting life.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return 5;
	}
	public function menuOptions(): array {
		return ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP'];
	}

}
