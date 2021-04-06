<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

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

}
