<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Page;
use Smr\Session;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\Session
 */
class SessionIntegrationTest extends BaseIntegrationSpec {

	private Session $session;

	protected function setUp() : void {
		// Start each test with a fresh container (and Smr\Session).
		// This ensures the independence of each test.
		\Smr\Container\DiContainer::initializeContainer();
		$this->session = Session::getInstance();
	}

	public function test_game() {
		// Sessions are initialized with no game
		self::assertFalse($this->session->hasGame());
		self::assertSame(0, $this->session->getGameID());

		// Now update the game
		$gameID = 3;
		$this->session->updateGame($gameID);
		self::assertTrue($this->session->hasGame());
		self::assertSame($gameID, $this->session->getGameID());
	}

	public function test_account() {
		// Sessions are initialized with no account
		self::assertFalse($this->session->hasAccount());
		self::assertSame(0, $this->session->getAccountID());

		// Now update the account
		$account = $this->createMock(\AbstractSmrAccount::class);
		$account
			->method('getAccountID')
			->willReturn(7);
		$this->session->setAccount($account);
		self::assertTrue($this->session->hasAccount());
		self::assertSame(7, $this->session->getAccountID());
	}

	public function test_current_var() {
		// With an empty session, there should be no current var
		self::assertFalse($this->session->findCurrentVar());

		// Add a page to the session so that we can find it later.
		// (This mimics Page::href but with better access to the SN.)
		$page = Page::create('some_page');
		$page['CommonID'] = 'abc';
		$page['RemainingPageLoads'] = 1;
		$sn = $this->session->addLink($page);

		// Now we should be able to find this sn in the var
		self::assertTrue($this->session->findCurrentVar($sn));

		// The current var should now be accessible
		$var = $this->session->getCurrentVar();
		self::assertSame('some_page', $var['url']);

		// The CommonID metadata should be stripped
		self::assertFalse(isset($var['CommonID']));
		// The RemainingPageLoads metadata should be incremented
		self::assertSame(2, $var['RemainingPageLoads']);

		// We can now change the current var
		$page2 = Page::create('another_page');
		$this->session->setCurrentVar($page2);
		// And $var should be updated automatically
		self::assertSame('another_page', $var['url']);
	}

}
