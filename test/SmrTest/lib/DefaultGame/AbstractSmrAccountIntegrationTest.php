<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrAccount;
use AccountNotFoundException;
use SmrTest\BaseIntegrationSpec;
use Smr\SocialLogin\Facebook;

/**
 * @covers AbstractSmrAccount
 */
class AbstractSmrAccountIntegrationTest extends BaseIntegrationSpec {

	protected function setUp() : void {
		AbstractSmrAccount::clearCache();
	}

	public function test_createAccount() {
		$login = 'test';
		$password = 'pw';
		$email = 'test@test.com';
		$tz = 9;
		$referral = 0;
		$account = AbstractSmrAccount::createAccount($login, $password, $email, $tz, $referral);
		$this->assertSame($login, $account->getLogin());
		$this->assertSame($email, $account->getEmail());
		$this->assertSame($tz, $account->getOffset());
		$this->assertSame($referral, $account->getReferrerID());
	}

	public function test_createAccount_throws_if_referrer_does_not_exist() {
		$this->expectException(AccountNotFoundException::class);
		$this->expectExceptionMessage('Account ID 123 does not exist');
		AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 123);
	}

	public function test_get_account_by_account_id() {
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// And there is no force update
		$forceUpdate = false;
		// When the account is retrieved by its ID
		$account = AbstractSmrAccount::getAccount($original->getAccountID(), $forceUpdate);
		// Without forceUpdate, the two objects should be the same in memory
		$this->assertSame($original, $account);

		// With forceUpdate, the objects should be identical, but not the same
		$forceUpdate = true;
		$account = AbstractSmrAccount::getAccount($original->getAccountID(), $forceUpdate);
		$this->assertNotSame($original, $account);
		$this->assertEquals($original, $account);
	}

	public function test_get_account_by_account_id_no_account_found_throws_exception() {
		$this->expectException(AccountNotFoundException::class);
		// Given there is no account record
		// When performing an account lookup by id
		AbstractSmrAccount::getAccount(123);
	}

	public function test_get_account_by_name_happy_path() {
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by name
		$account = AbstractSmrAccount::getAccountByName($original->getLogin());
		// Then the record is found
		$this->assertSame($original, $account);
	}

	public function test_get_account_by_name_returns_null_when_no_account_name_provided() {
		// When retrieving account by empty string name
		$account = AbstractSmrAccount::getAccountByName('');
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_name_returns_null_when_no_record_found() {
		// Given no record exists
		// When retrieving account by name
		$account = AbstractSmrAccount::getAccountByName('any');
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_email_happy_path() {
		// Given a record exists
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by email
		$account = AbstractSmrAccount::getAccountByEmail($original->getEmail());
		// Then the record is found
		$this->assertSame($original, $account);
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
		$account = AbstractSmrAccount::getAccountByEmail('any');
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_discord_happy_path() {
		// Given a record exists
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setDiscordId('123');
		$original->update();
		// When retrieving account by discord
		$account = AbstractSmrAccount::getAccountByDiscordId($original->getDiscordId(), true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
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
		$account = AbstractSmrAccount::getAccountByDiscordId('any');
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_irc_happy_path() {
		// Given a record exists
		// Given the database has been set up with a user
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setIrcNick('nick');
		$original->update();
		// When retrieving account by irc
		$account = AbstractSmrAccount::getAccountByIrcNick($original->getIrcNick(), true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
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
		$account = AbstractSmrAccount::getAccountByIrcNick('any');
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_social_happy_path() {

		// Given a record exists
		$original = AbstractSmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		$authUserID = 'MySocialUserID';
		$original->addAuthMethod(Facebook::getLoginType(), $authUserID);
		// And a valid social login
		/*
		 * Unfortunately we cannot use the simple createMock() method, because the SocialLogin class uses
		 * a static method for getLoginType(). PHPUnit cannot operate on static methods, so it throws a warning.
		 * Instead we create a partial mock, and all other methods will call their default method, and prevent the warning
		 * that causes PHPUnit to fail due to "warnings" or "risky" tests.
		 */
		$isValid = 'isValid';
		$getUserId = 'getUserId';
		$socialLogin = $this->createPartialMock(Facebook::class, array($isValid, $getUserId));
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn($authUserID);
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin, true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_social_returns_null_when_social_invalid() {
		// Given an invalid social login
		$socialLogin = $this->createMock(Facebook::class);
		$socialLogin
			->expects(self::once())
			->method('isValid')
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
		$isValid = 'isValid';
		$getUserId = 'getUserId';
		$socialLogin = $this->createPartialMock(Facebook::class, array($isValid, $getUserId));
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn('123');
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin);
		// Then the record is null
		$this->assertNull($account);
	}

}
