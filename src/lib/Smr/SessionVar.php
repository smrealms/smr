<?php declare(strict_types=1);

namespace Smr;

use Smr\Page\Page;

/**
 * Helper class for Smr\Session that holds the session_var database content.
 */
readonly class SessionVar {

	/**
	 * @param array<string, Page> $links
	 * @param ?Page $lastPage
	 * @param array<string, mixed> $lastRequestData
	 */
	public function __construct(
		public array $links,
		public ?Page $lastPage,
		public array $lastRequestData,
	) {}

}
