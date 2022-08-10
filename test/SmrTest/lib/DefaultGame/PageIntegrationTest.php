<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Page;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Session;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest
 * since we are not (or should not be!) writing any data.
 *
 * @covers Page
 */
class PageIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Reset the DI container for each test to ensure independence.
		DiContainer::initialize(false);
	}

	public function test_href(): void {
		// Create an arbitrary Page
		$page = new Page();

		// Pre-initialize the Smr\Session, since it uses 'rand', and we don't
		// want it to interfere with our rand seed when we call `href`, which
		// internally requires an Smr\Session.
		Session::getInstance();

		// The Page should not be modified when href() is called
		$expected = clone $page;
		srand(0); // for a deterministic SN
		$href = $page->href();
		self::assertSame(LOADER_URI . '?sn=qpbqzr', $href);
		self::assertEquals($expected, $page);
	}

}
