<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Container\DiContainer;
use Smr\Exceptions\UserError;
use Smr\Page\Page;
use Smr\Session;
use SmrAccount;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\Session
 */
class SessionIntegrationTest extends BaseIntegrationSpec {

	private Session $session;

	protected function tablesToTruncate(): array {
		return ['debug', 'active_session'];
	}

	protected function setUp(): void {
		// Start each test with a fresh container (and Smr\Session).
		// This ensures the independence of each test.
		DiContainer::initialize(false);
		$this->session = Session::getInstance();
	}

	protected function tearDown(): void {
		// Clear superglobals to avoid impacting other tests
		$_REQUEST = [];
		$_COOKIE = [];
	}

	public function test_game(): void {
		// Sessions are initialized with no game
		self::assertFalse($this->session->hasGame());
		self::assertSame(0, $this->session->getGameID());

		// Now update the game
		$gameID = 3;
		$this->session->updateGame($gameID);
		self::assertTrue($this->session->hasGame());
		self::assertSame($gameID, $this->session->getGameID());
	}

	public function test_account(): void {
		// Sessions are initialized with no account
		self::assertFalse($this->session->hasAccount());
		self::assertSame(0, $this->session->getAccountID());

		// Now update the account
		$account = $this->createMock(SmrAccount::class);
		$account
			->method('getAccountID')
			->willReturn(7);
		$this->session->setAccount($account);
		self::assertTrue($this->session->hasAccount());
		self::assertSame(7, $this->session->getAccountID());
	}

	public function test_getSN(): void {
		// If there is no 'sn' parameter of the $_REQUEST superglobal,
		// then we get an empty SN.
		self::assertSame('', $this->session->getSN());

		// Now create a new Session with a specific 'sn' parameter set.
		$sn = 'some_sn';
		$_REQUEST['sn'] = $sn;
		$session = DiContainer::make(Session::class);
		self::assertSame($sn, $session->getSN());
	}

	public function test_getSessionID(): void {
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

	public function test_ajax(): void {
		// If $_REQUEST is empty, ajax is false
		self::assertFalse($this->session->ajax);

		// Test other values in $_REQUEST
		$_REQUEST['ajax'] = 1;
		self::assertTrue(DiContainer::make(Session::class)->ajax);
		$_REQUEST['ajax'] = 'anything other than 1';
		self::assertFalse(DiContainer::make(Session::class)->ajax);
	}

	/**
	 * @testWith [true]
	 *           [false]
	 */
	public function test_ajax_var(bool $varAjax): void {
		// Set the current var to an arbitrary page
		$page1 = new Page();
		$this->session->setCurrentVar($page1);

		// Add a link to another page
		$page2 = new Page();
		$page2->allowAjax = $varAjax;
		$sn = $this->session->addLink($page2);
		$this->session->update();

		// Now pretend we're making an ajax call with the second page
		$_REQUEST = [
			'sn' => $sn,
			'ajax' => '1',
		];
		$_COOKIE['session_id'] = $this->session->getSessionID();
		if ($varAjax) {
			// If the container allows ajax, this is a valid operation
			$session = DiContainer::make(Session::class);
			self::assertTrue($session->ajax);
			self::assertSame($sn, $session->getSN());
		} else {
			// Otherwise, this should be a page refresh, but SN changes
			$this->expectException(UserError::class);
			$this->expectExceptionMessage('The previous page failed to auto-refresh properly!');
			DiContainer::make(Session::class);
		}
	}

	public function test_current_var(): void {
		// With an empty session, there should be no current var
		self::assertFalse($this->session->hasCurrentVar());

		// Add a page to the session so that we can find it later.
		// (This mimics Page::href but with better access to the SN.)
		$page = new Page();
		$sn = $this->session->addLink($page);
		$this->session->update();

		// Create a new Session, requesting the SN we just made
		$_REQUEST['sn'] = $sn;
		$_COOKIE['session_id'] = $this->session->getSessionID();
		$session = DiContainer::make(Session::class);

		// Now we should be able to find this sn in the var
		self::assertTrue($session->hasCurrentVar());

		// The current var should now be accessible
		$var = $session->getCurrentVar();
		self::assertEquals($page, $var);

		// We can now change the current var
		$page2 = new Page();
		$page2->file = 'another file';
		$session->setCurrentVar($page2);
		// Old references to $var should not be modified
		self::assertEquals($page, $var);
		// But a new reference to $var should be updated
		$var2 = $session->getCurrentVar();
		self::assertSame($page2, $var2);

		// If we make a new session, but keep the same SN, we should still get
		// the updated var, even though it wasn't the one originally associated
		// with this SN.
		$session->update();
		$_REQUEST['ajax'] = 1; // simulate AJAX refresh
		$session = DiContainer::make(Session::class);
		self::assertEquals($var2, $session->getCurrentVar());
		$_REQUEST['ajax'] = 0; // simulate F5 refresh
		$session = DiContainer::make(Session::class);
		self::assertEquals($var2, $session->getCurrentVar());

		// If we destroy the Session, then the current var should no longer
		// be accessible to a new Session.
		$session->destroy();
		$session = DiContainer::make(Session::class);
		self::assertFalse($session->hasCurrentVar());
	}

	public function test_addLink(): void {
		// If we add two different pages, we should get different SNs.
		$page = new Page();
		$sn = $this->session->addLink($page);

		$page2 = new Page();
		$page2->file = 'another_page';
		self::assertNotEquals($sn, $this->session->addLink($page2));
	}

	public function test_addLink_page_already_added(): void {
		// If we add the same page object twice, it will give the same SN.
		$page = new Page();
		$sn = $this->session->addLink($page);
		self::assertSame($sn, $this->session->addLink($page));

		// This works if the pages are equal, but not the same object.
		$page2 = clone $page;
		self::assertNotSame($page, $page2);
		self::assertSame($sn, $this->session->addLink($page2));

		// It also works if we modify the page object (though this isn't
		// recommended, we clone when adding from Page::href to avoid this).
		$page->allowAjax = true;
		self::assertSame($sn, $this->session->addLink($page));
	}

	public function test_clearLinks(): void {
		srand(0); // seed rng to avoid getting the same random SN twice
		$page = new Page();
		$sn = $this->session->addLink($page);

		// After clearing links, the same page will return a different SN.
		$this->session->clearLinks();
		self::assertNotSame($sn, $this->session->addLink($page));
	}

	public function test_getRequestVar(): void {
		// Initialize the current var so that we can update it
		$page = new Page();
		$this->session->setCurrentVar($page);

		// Prepare request values
		$_REQUEST = [
			'str' => 'foo',
			'int' => 4,
			'arr' => [5, 6],
		];

		// Check the following conditions:
		// 1. The index is not set in the current var beforehand
		// 2. We return the expected value from getRequestVar
		// 3. The value is stored in the current var afterwards
		self::assertArrayNotHasKey('str', $this->session->getRequestData());
		self::assertSame('foo', $this->session->getRequestVar('str'));
		self::assertSame('foo', $this->session->getRequestData()['str']);

		self::assertArrayNotHasKey('int', $this->session->getRequestData());
		self::assertSame(4, $this->session->getRequestVarInt('int'));
		self::assertSame(4, $this->session->getRequestData()['int']);

		self::assertArrayNotHasKey('arr', $this->session->getRequestData());
		self::assertSame([5, 6], $this->session->getRequestVarIntArray('arr'));
		self::assertSame([5, 6], $this->session->getRequestData()['arr']);
	}

}
