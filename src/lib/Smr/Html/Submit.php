<?php declare(strict_types=1);

namespace Smr\Html;

/**
 * Holds data for submit element for consistency between templates and processors.
 */
readonly class Submit {

	public function __construct(
		public readonly string $name,
		public readonly string $value,
	) {}

	/**
	 * @param array<string, string | int | true> $fields
	 */
	public function html(?string $display = null, array $fields = []): string {
		return create_submit($this->name, $this->value, $display, $fields);
	}

}
