<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

use Override;
use Smr\PlanetMenuOption;

/**
 * Highly defensive, but not overly dangerous planet intended for NPCs.
 */
class SentinelOutpost extends PlanetType {

	public const array STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 125,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
		PLANET_HANGAR => [
			'max_amount' => 25,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
		PLANET_BUNKER => [
			'max_amount' => 125,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
		PLANET_WEAPON_MOUNT => [
			'max_amount' => 10,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
		PLANET_RADAR => [
			'max_amount' => 5,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
		PLANET_TURRET => [
			'max_amount' => 10,
			'base_time' => 0,
			'credit_cost' => 0,
			'exp_gain' => 0,
		],
	];
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Sentinel Outpost';
	}
	public function imageLink(): string {
		return 'images/planet6.png';
	}
	public function description(): string {
		return 'An autonomous defense structure, not capable of supporting life.';
	}
	public function maxAttackers(): int {
		return 10;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return [PlanetMenuOption::MAIN, PlanetMenuOption::OWNERSHIP];
	}

	#[Override]
	public function hasPermanentDestruction(): bool {
		return true;
	}

}
