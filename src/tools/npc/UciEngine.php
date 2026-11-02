<?php declare(strict_types=1);

namespace Smr\Npc;

use Exception;

final readonly class UciEngine {

	/**
	 * @param resource $toEngine
	 * @param resource $fromEngine
	 */
	private function __construct(
		private mixed $toEngine,
		private mixed $fromEngine,
		private bool $debug,
	) {}

	public static function start(string $executable, bool $debug = false): self {
		$descriptorSpec = [
			0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
			1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
		];
		$process = proc_open($executable, $descriptorSpec, $pipes);
		if (!is_resource($process)
			|| !isset($pipes[0], $pipes[1])
			|| !is_resource($pipes[0])
			|| !is_resource($pipes[1])) {
			throw new Exception('Failed to start UCI chess engine');
		}

		$engine = new self(
			toEngine: $pipes[0],
			fromEngine: $pipes[1],
			debug: $debug,
		);
		$engine->readResponses(true);

		return $engine;
	}

	public function send(
		string $command,
		bool $block = true,
		bool $read = true,
	): void {
		$this->log('--> ' . $command);
		fwrite($this->toEngine, $command . EOL);
		if ($read) {
			$this->readResponses($block);
		}
	}

	private function readResponses(bool $block): void {
		stream_set_blocking($this->fromEngine, $block);
		while (($response = fgets($this->fromEngine)) !== false) {
			$this->log('<-- ' . trim($response));
			stream_set_blocking($this->fromEngine, false);
		}
	}

	public function readResponse(bool $block = true): ?string {
		stream_set_blocking($this->fromEngine, $block);
		$response = fgets($this->fromEngine);
		if ($response === false) {
			return null;
		}

		$response = trim($response);
		$this->log('<-- ' . $response);

		return $response;
	}

	private function log(string $message): void {
		if ($this->debug) {
			debug($message);
		}
	}

}
