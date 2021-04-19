<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Page;
use Smr\Container\DiContainer;
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
		DiContainer::initializeContainer();
		$this->session = Session::getInstance();
	}

	protected function tearDown() : void {
		parent::tearDown();
		// Clear superglobals to avoid impacting other tests
		$_REQUEST = [];
		$_COOKIE = [];
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

	public function test_getSN() {
		// If there is no 'sn' parameter of the $_REQUEST superglobal,
		// then we get an empty SN.
		self::assertSame('', $this->session->getSN());

		// Now create a new Session with a specific 'sn' parameter set.
		$sn = 'some_sn';
		$_REQUEST['sn'] = $sn;
		$session = DiContainer::make(Session::class);
		self::assertSame($sn, $session->getSN());
	}

	public function test_getSessionID() {
		// The default Session ID is a random 32-length string
		self::assertSame(32, strlen($this->session->getSessionID()));

		// Create a Session with a specific ID
		$sessionID = md5('hello');
		$_COOKIE['session_id'] = $sessionID;
		$session = DiContainer::make(Session::class);
		self::assertSame($sessionID, $session->getSessionID());

		// If we try to use a session ID with fewer than 32 chars,
		// we get a random ID instead
		$sessionID = 'hello';
		$_COOKIE['session_id'] = $sessionID;
		$session = DiContainer::make(Session::class);
		self::assertNotEquals($sessionID, $session->getSessionID());
	}

	public function test_current_var() {
		// With an empty session, there should be no current var
		self::assertFalse($this->session->hasCurrentVar());

		// Add a page to the session so that we can find it later.
		// (This mimics Page::href but with better access to the SN.)
		$page = Page::create('some_page');
		$page['CommonID'] = 'abc';
		$page['RemainingPageLoads'] = 1;
		$sn = $this->session->addLink($page);
		$sessionID = $this->session->getSessionID(); // needed for later
		$this->session->update();

		// Create a new Session, requesting the SN we just made
		$_REQUEST['sn'] = $sn;
		$_COOKIE['session_id'] = $sessionID;
		$session = DiContainer::make(Session::class);

		// Now we should be able to find this sn in the var
		self::assertTrue($session->hasCurrentVar());

		// The current var should now be accessible
		$var = $session->getCurrentVar();
		self::assertSame('some_page', $var['url']);

		// The CommonID metadata should not be stripped
		self::assertTrue(isset($var['CommonID']));
		// The RemainingPageLoads should still be 1 because we effectively
		// reloaded the page by creating a new Session.
		self::assertSame(1, $var['RemainingPageLoads']);

		// We can now change the current var
		$page2 = Page::create('another_page');
		$session->setCurrentVar($page2);
		// And $var should be updated automatically
		self::assertSame('another_page', $var['url']);

		// If we destroy the Session, then the current var should no longer
		// be accessible to a new Session.
		$session->destroy();
		$session = DiContainer::make(Session::class);
		self::assertFalse($session->hasCurrentVar());
	}

}
