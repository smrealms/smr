<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

use Smr\PlanetMenuOption;

class ProtoPlanet extends PlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 5,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 50,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_BUNKER => [
			'max_amount' => 15,
			'base_time' => 10800,
			'credit_cost' => 50000,
			'exp_gain' => 90,
		],
		PLANET_WEAPON_MOUNT => [
			'max_amount' => 20,
			'base_time' => 32400,
			'credit_cost' => 300000,
			'exp_gain' => 270,
		],
		PLANET_RADAR => [
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
		return 'Protoplanet';
	}
	public function imageLink(): string {
		return 'images/planet5.png';
	}
	public function description(): string {
		return 'A developing planet, not yet able to support the infrastructure of advanced technologies.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return 5;
	}
	public function menuOptions(): array {
		return PlanetMenuOption::cases();
	}

}
