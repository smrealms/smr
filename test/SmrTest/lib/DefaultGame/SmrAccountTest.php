<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Exceptions\AccountNotFound;
use Smr\SocialLogin\Facebook;
use SmrAccount;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrAccount
 */
class SmrAccountTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['account', 'account_auth'];
	}

	protected function setUp(): void {
		SmrAccount::clearCache();
	}

	public function test_createAccount(): void {
		$login = 'test';
		$password = 'pw';
		$email = 'test@test.com';
		$tz = 9;
		$referral = 0;
		$account = SmrAccount::createAccount($login, $password, $email, $tz, $referral);
		$this->assertSame($login, $account->getLogin());
		$this->assertSame($email, $account->getEmail());
		$this->assertSame($tz, $account->getOffset());
		$this->assertSame($referral, $account->getReferrerID());
	}

	public function test_createAccount_throws_if_referrer_does_not_exist(): void {
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account ID 123 does not exist');
		SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 123);
	}

	public function test_get_account_by_account_id(): void {
		// Given the database has been set up with a user
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// And there is no force update
		$forceUpdate = false;
		// When the account is retrieved by its ID
		$account = SmrAccount::getAccount($original->getAccountID(), $forceUpdate);
		// Without forceUpdate, the two objects should be the same in memory
		$this->assertSame($original, $account);

		// With forceUpdate, the objects should be identical, but not the same
		$forceUpdate = true;
		$account = SmrAccount::getAccount($original->getAccountID(), $forceUpdate);
		$this->assertNotSame($original, $account);
		$this->assertEquals($original, $account);
	}

	public function test_get_account_by_account_id_no_account_found_throws_exception(): void {
		$this->expectException(AccountNotFound::class);
		// Given there is no account record
		// When performing an account lookup by id
		SmrAccount::getAccount(123);
	}

	public function test_get_account_by_login_happy_path(): void {
		// Given the database has been set up with a user
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by login
		$account = SmrAccount::getAccountByLogin($original->getLogin());
		// Then the record is found
		$this->assertSame($original, $account);
	}

	public function test_get_account_by_login_throws_when_no_login_provided(): void {
		// When retrieving account by empty string login, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account login not found');
		SmrAccount::getAccountByLogin('');
	}

	public function test_get_account_by_login_throws_when_no_record_found(): void {
		// When retrieving account by login, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account login not found');
		SmrAccount::getAccountByLogin('does_not_exist');
	}

	public function test_get_account_by_hof_name_happy_path(): void {
		// Given the database has been set up with a user
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by HoF name
		$account = SmrAccount::getAccountByLogin($original->getHofName());
		// Then the record is found
		$this->assertSame($original, $account);
	}

	public function test_get_account_by_hof_name_throws_when_no_hof_name_provided(): void {
		// When retrieving account by empty string HoF name, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account HoF name not found');
		SmrAccount::getAccountByHofName('');
	}

	public function test_get_account_by_hof_name_throws_when_no_record_found(): void {
		// When retrieving account by HoF name, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account HoF name not found');
		SmrAccount::getAccountByHofName('does_not_exist');
	}

	public function test_get_account_by_email_happy_path(): void {
		// Given a record exists
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by email
		$account = SmrAccount::getAccountByEmail($original->getEmail());
		// Then the record is found
		$this->assertSame($original, $account);
	}

	public function test_get_account_by_email_throws_when_no_email_provided(): void {
		// When retrieving account by empty string email, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account email not found');
		SmrAccount::getAccountByEmail('');
	}

	public function test_get_account_by_email_throws_when_no_record_found(): void {
		// When retrieving account by email, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account email not found');
		SmrAccount::getAccountByEmail('does_not_exist');
	}

	public function test_get_account_by_discord_happy_path(): void {
		// Given a record exists
		// Given the database has been set up with a user
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setDiscordId('123');
		$original->update();
		// When retrieving account by discord
		$account = SmrAccount::getAccountByDiscordId($original->getDiscordId(), true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_discord_throws_when_no_discord_provided(): void {
		// When retrieving account by empty string discord ID, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account discord ID not found');
		SmrAccount::getAccountByDiscordId('');
	}

	public function test_get_account_by_discord_throws_when_no_record_found(): void {
		// When retrieving account by discord, exception is thrown if no record exists.
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account discord ID not found');
		SmrAccount::getAccountByDiscordId('does_not_exist');
	}

	public function test_get_account_by_irc_happy_path(): void {
		// Given a record exists
		// Given the database has been set up with a user
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setIrcNick('nick');
		$original->update();
		// When retrieving account by irc
		$account = SmrAccount::getAccountByIrcNick($original->getIrcNick(), true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_irc_throws_when_no_irc_provided(): void {
		// When retrieving account by empty string irc, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account IRC nick not found');
		SmrAccount::getAccountByIrcNick('');
	}

	public function test_get_account_by_irc_returns_null_when_no_record_found(): void {
		// When retrieving account by irc, exception is thrown if no record exists.
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account IRC nick not found');
		SmrAccount::getAccountByIrcNick('does_not_exist');
	}

	public function test_get_account_by_social_happy_path(): void {

		// Given a record exists
		$original = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);
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
		$socialLogin = $this->createPartialMock(Facebook::class, [$isValid, $getUserId]);
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn($authUserID);
		// When retrieving account by social
		$account = SmrAccount::getAccountBySocialLogin($socialLogin, true);
		// Then the record is found
		$this->assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_social_throws_when_social_invalid(): void {
		// Given an invalid social login
		$socialLogin = $this->createMock(Facebook::class);
		$socialLogin
			->expects(self::once())
			->method('isValid')
			->willReturn(false);
		// When retrieving account by invalid social, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account social login not found');
		SmrAccount::getAccountBySocialLogin($socialLogin);
	}

	public function test_get_account_by_social_throws_when_no_record_found(): void {
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
		$socialLogin = $this->createPartialMock(Facebook::class, [$isValid, $getUserId]);
		$socialLogin
			->expects(self::once())
			->method($isValid)
			->willReturn(true);
		$socialLogin
			->expects(self::once())
			->method($getUserId)
			->willReturn('123');
		// When retrieving account by social, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account social login not found');
		SmrAccount::getAccountBySocialLogin($socialLogin);
	}

	public function test_date_format_methods(): void {
		$account = SmrAccount::createAccount('test', 'test', 'test@test.com', 9, 0);

		// Arbitrary epoch for testing (Sat, 24 Apr 2021 01:39:51 GMT)
		$epoch = 1619228391;

		// Test default formats
		self::assertSame('2021-04-24', date($account->getDateFormat(), $epoch));
		self::assertSame('1:39:51 AM', date($account->getTimeFormat(), $epoch));

		// Test combined formats
		self::assertSame('2021-04-24 1:39:51 AM', date($account->getDateTimeFormat(), $epoch));
		self::assertSame('2021-04-24<br />1:39:51 AM', date($account->getDateTimeFormatSplit(), $epoch));

		// Now modify the formats
		$account->setDateFormat('Y M D');
		$account->setTimeFormat('H i s');

		// Test the modified formats
		self::assertSame('2021 Apr Sat', date($account->getDateFormat(), $epoch));
		self::assertSame('01 39 51', date($account->getTimeFormat(), $epoch));
	}

}
