<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrPlayer;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Page\Page;
use Smr\Session;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest
 * since we are not (or should not be!) writing any data.
 *
 * @covers Smr\Page\Page
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

	/**
	 * Tests the showUnderAttack method in a variety of scenarios. We test
	 * consecutive page loads to ensure that a positive result persists
	 * across page loads (both ajax and non-ajax).
	 *
	 * @testWith [true, true, null, false, true, true]
	 *           [true, false, null, true, true, true]
	 *           [true, true, null, true, true, true]
	 *           [false, false, true, true, false, true]
	 *           [false, true, true, true, false, true]
	 */
	public function test_showUnderAttack(bool $underAttack1, bool $ajax1, ?bool $underAttack2, bool $ajax2, bool $expected1, bool $expected2): void {
		$getPlayer = function(bool $underAttack, bool $ajax): AbstractSmrPlayer {
			$mockPlayer = $this->createMock(AbstractSmrPlayer::class);
			$mockPlayer
				->method('isUnderAttack')
				->willReturn($underAttack);
			$mockPlayer
				->expects(self::exactly($ajax ? 0 : 1))
				->method('setUnderAttack')
				->with(false);
			return $mockPlayer;
		};

		// Simulated player state for the first page load
		$mockPlayerPage1 = $getPlayer($underAttack1, $ajax1);

		// Simulated player state for the second page load
		if ($underAttack2 === null) {
			// This condition implies that there was no external factor changing the
			// player state between page loads, so the expected return value of its
			// isUnderAttack method depends on if setUnderAttack was called before.
			$underAttack2 = $ajax1 ? $underAttack1 : false;
		}
		$mockPlayerPage2 = $getPlayer($underAttack2, $ajax2);

		$page1 = new Page();
		$result1 = $page1->showUnderAttack($mockPlayerPage1, $ajax1);
		self::assertSame($expected1, $result1);

		// If the second page is ajax, it reuses the previous Page object
		$page2 = $ajax2 ? $page1 : new Page();
		$result2 = $page2->showUnderAttack($mockPlayerPage2, $ajax2);
		self::assertSame($expected2, $result2);
	}

}
