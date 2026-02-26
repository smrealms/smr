<?php declare(strict_types=1);

namespace Smr;

readonly class MissionStep {

	public function __construct(
		public MissionAction $requirement,
		public string $message,
		public string $task,
	) {}

}
