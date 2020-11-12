<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrAccount;
use AccountNotFoundException;
use MySqlDatabase;
use mysqli;
use SmrTest\BaseIntegrationSpec;
use SocialLogins\Facebook;

/**
 * Class AbstractSmrAccountTest
 * @covers AbstractSmrAccount
 */
class AbstractSmrAccountIntegrationTest extends BaseIntegrationSpec {
	public function test_account_creation() {
		global $phpDiContainer;
		$phpDiContainer->set(MySqlDatabase::class, $this->createMock(MySqlDatabase::class));
		$account = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		$this->assertEquals("test", $account->getLogin());
		$this->assertEquals("test@test.com", $account->getEmail());
	}

	/*
	 * Can easily add forcerefresh tests by asserting original == account for forcerefresh = false
	 * then can tests force refresh true by comparing account ids since the account detauls may get populated with
	 * other stuff on refresh
	 */
	public function test_get_account_by_account_id() {
		// Given the database has been set up with a user
		$account = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		// And there is no force update
		$forceUpdate = false;
		// When the account is retrieved by its ID
		$abstractSmrAccount = AbstractSmrAccount::getAccount($account->getAccountID(), $forceUpdate);
		// Then the integrity of the user is correct
		$this->assertEquals($account, $abstractSmrAccount);
	}

	public function test_get_account_by_account_id_no_account_found_throws_exception() {
		$this->expectException(AccountNotFoundException::class);
		// Given there is no account record
		// When performing an account lookup by id
		AbstractSmrAccount::getAccount(123);
	}

	public function test_get_account_by_name_happy_path() {
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		// When retrieving account by name
		$account = AbstractSmrAccount::getAccountByName($original->getLogin());
		// Then the record is found
		$this->assertEquals($original, $account);
	}

	public function test_get_account_by_name_returns_null_when_no_account_name_provided() {
		// When retrieving account by null name
		$account = AbstractSmrAccount::getAccountByName(null);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_name_returns_null_when_no_record_found() {
		// Given no record exists
		// When retrieving account by name
		$account = AbstractSmrAccount::getAccountByName("any");
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_email_happy_path() {
		// Given a record exists
		$original = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		// When retrieving account by email
		$account = AbstractSmrAccount::getAccountByEmail($original->getEmail());
		// Then the record is found
		$this->assertEquals($original, $account);
	}

	public function test_get_account_by_email_returns_null_when_no_email_provided() {
		// When retrieving account by null email
		$account = AbstractSmrAccount::getAccountByEmail(null);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_email_returns_null_when_no_record_found() {
		// Given no record exists
		// When retrieving account by email
		$account = AbstractSmrAccount::getAccountByEmail("any");
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_discord_happy_path() {
		// Given a record exists
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		$original->setDiscordId("123");
		$original->update();
		// When retrieving account by discord
		$account = AbstractSmrAccount::getAccountByDiscordId($original->getDiscordId(), true);
		// Then the record is found
		$this->assertEquals($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_discord_returns_null_when_no_discord_provided() {
		// When retrieving account by null discord
		$account = AbstractSmrAccount::getAccountByDiscordId(null);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_discord_returns_null_when_no_record_found() {
		// Given no record exists
		// When retrieving account by discord
		$account = AbstractSmrAccount::getAccountByDiscordId("any");
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_irc_happy_path() {
		// Given a record exists
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		$original->setIrcNick("nick");
		$original->update();
		// When retrieving account by irc
		$account = AbstractSmrAccount::getAccountByIrcNick($original->getIrcNick(), true);
		// Then the record is found
		$this->assertEquals($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_irc_returns_null_when_no_irc_provided() {
		// When retrieving account by null irc
		$account = AbstractSmrAccount::getAccountByIrcNick(null);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_irc_returns_null_when_no_record_found() {
		// Given no record exists
		// When retrieving account by irc
		$account = AbstractSmrAccount::getAccountByIrcNick("any");
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_social_happy_path() {

		// Given a record exists
		$original = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		$original->addAuthMethod(Facebook::getLoginType(), $original->getAccountID());
		// And a valid social login
		/*
		 * Unfortunately we cannot use the simple createMock() method, because the SocialLogin class uses
		 * a static method for getLoginType(). PHPUnit cannot operate on static methods, so it throws a warning.
		 * Instead we create a partial mock, and all other methods will call their default method, and prevent the warning
		 * that causes PHPUnit to fail due to "warnings" or "risky" tests.
		 */
		$isValid = "isValid";
		$getUserId = "getUserId";
		$socialLogin = $this->createPartialMock(Facebook::class, array($isValid, $getUserId));
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn($original->getAccountID());
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin, true);
		// Then the record is found
		$this->assertEquals($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_social_returns_null_when_social_invalid() {
		// Given an invalid social login
		$socialLogin = $this->createMock(Facebook::class);
		$socialLogin
			->expects(self::once())
			->method("isValid")
			->willReturn(false);
		// When retrieving account by null social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_social_returns_null_when_no_record_found() {
		// Given no record exists
		// And a valid social login
		/*
		 * Unfortunately we cannot use the simple createMock() method, because the SocialLogin class uses
		 * a static method for getLoginType(). PHPUnit cannot operate on static methods, so it throws a warning.
		 * Instead we create a partial mock, and all other methods will call their default method, and prevent the warning
		 * that causes PHPUnit to fail due to "warnings" or "risky" tests.
		 */
		$isValid = "isValid";
		$getUserId = "getUserId";
		$socialLogin = $this->createPartialMock(Facebook::class, array($isValid, $getUserId));
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn("123");
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin);
		// Then the record is null
		$this->assertNull($account);
	}
}
