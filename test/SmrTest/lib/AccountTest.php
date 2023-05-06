<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Account;
use Smr\Exceptions\AccountNotFound;
use Smr\SocialLogin\Facebook;
use Smr\SocialLogin\SocialIdentity;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Account::class)]
class AccountTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['account', 'account_auth'];
	}

	protected function setUp(): void {
		Account::clearCache();
	}

	public function test_createAccount(): void {
		$login = 'test';
		$password = 'pw';
		$email = 'test@test.com';
		$tz = 9;
		$referral = 0;
		$account = Account::createAccount($login, $password, $email, $tz, $referral);
		self::assertSame($login, $account->getLogin());
		self::assertSame($email, $account->getEmail());
		self::assertSame($tz, $account->getOffset());
		self::assertSame($referral, $account->getReferrerID());
	}

	public function test_createAccount_throws_if_referrer_does_not_exist(): void {
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account ID 123 does not exist');
		Account::createAccount('test', 'test', 'test@test.com', 9, 123);
	}

	public function test_get_account_by_account_id(): void {
		// Given the database has been set up with a user
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		// And there is no force update
		$forceUpdate = false;
		// When the account is retrieved by its ID
		$account = Account::getAccount($original->getAccountID(), $forceUpdate);
		// Without forceUpdate, the two objects should be the same in memory
		self::assertSame($original, $account);

		// With forceUpdate, the objects should be identical, but not the same
		$forceUpdate = true;
		$account = Account::getAccount($original->getAccountID(), $forceUpdate);
		self::assertNotSame($original, $account);
		self::assertEquals($original, $account);
	}

	public function test_get_account_by_account_id_no_account_found_throws_exception(): void {
		$this->expectException(AccountNotFound::class);
		// Given there is no account record
		// When performing an account lookup by id
		Account::getAccount(123);
	}

	public function test_get_account_by_login_happy_path(): void {
		// Given the database has been set up with a user
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by login
		$account = Account::getAccountByLogin($original->getLogin());
		// Then the record is found
		self::assertSame($original, $account);
	}

	public function test_get_account_by_login_throws_when_no_login_provided(): void {
		// When retrieving account by empty string login, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account login not found');
		Account::getAccountByLogin('');
	}

	public function test_get_account_by_login_throws_when_no_record_found(): void {
		// When retrieving account by login, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account login not found');
		Account::getAccountByLogin('does_not_exist');
	}

	public function test_get_account_by_hof_name_happy_path(): void {
		// Given the database has been set up with a user
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by HoF name
		$account = Account::getAccountByLogin($original->getHofName());
		// Then the record is found
		self::assertSame($original, $account);
	}

	public function test_get_account_by_hof_name_throws_when_no_hof_name_provided(): void {
		// When retrieving account by empty string HoF name, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account HoF name not found');
		Account::getAccountByHofName('');
	}

	public function test_get_account_by_hof_name_throws_when_no_record_found(): void {
		// When retrieving account by HoF name, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account HoF name not found');
		Account::getAccountByHofName('does_not_exist');
	}

	public function test_get_account_by_email_happy_path(): void {
		// Given a record exists
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		// When retrieving account by email
		$account = Account::getAccountByEmail($original->getEmail());
		// Then the record is found
		self::assertSame($original, $account);
	}

	public function test_get_account_by_email_throws_when_no_email_provided(): void {
		// When retrieving account by empty string email, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account email not found');
		Account::getAccountByEmail('');
	}

	public function test_get_account_by_email_throws_when_no_record_found(): void {
		// When retrieving account by email, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account email not found');
		Account::getAccountByEmail('does_not_exist');
	}

	public function test_get_account_by_discord_happy_path(): void {
		// Given a record exists
		// Given the database has been set up with a user
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setDiscordId('123');
		$original->update();
		// When retrieving account by discord
		$account = Account::getAccountByDiscordId($original->getDiscordId(), true);
		// Then the record is found
		self::assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_discord_throws_when_no_discord_provided(): void {
		// When retrieving account by empty string discord ID, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account discord ID not found');
		Account::getAccountByDiscordId('');
	}

	public function test_get_account_by_discord_throws_when_no_record_found(): void {
		// When retrieving account by discord, exception is thrown if no record exists.
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account discord ID not found');
		Account::getAccountByDiscordId('does_not_exist');
	}

	public function test_get_account_by_irc_happy_path(): void {
		// Given a record exists
		// Given the database has been set up with a user
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		$original->setIrcNick('nick');
		$original->update();
		// When retrieving account by irc
		$account = Account::getAccountByIrcNick($original->getIrcNick(), true);
		// Then the record is found
		self::assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_irc_throws_when_no_irc_provided(): void {
		// When retrieving account by empty string irc, exception is thrown
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account IRC nick not found');
		Account::getAccountByIrcNick('');
	}

	public function test_get_account_by_irc_returns_null_when_no_record_found(): void {
		// When retrieving account by irc, exception is thrown if no record exists.
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account IRC nick not found');
		Account::getAccountByIrcNick('does_not_exist');
	}

	public function test_get_account_by_social_happy_path(): void {
		// Given a record exists
		$original = Account::createAccount('test', 'test', 'test@test.com', 9, 0);
		// And a valid social identity
		$socialId = new SocialIdentity('MySocialUserID', 'dummy', Facebook::getLoginType());
		$original->addAuthMethod($socialId);
		// When retrieving account by social
		$account = Account::getAccountBySocialId($socialId, true);
		// Then the record is found
		self::assertSame($original->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_social_throws_when_no_record_found(): void {
		// Given no record exists
		// And a valid social identity
		$socialId = new SocialIdentity('does_not_exist', 'dummy', Facebook::getLoginType());
		// When retrieving account by social, exception is thrown if no record exists
		$this->expectException(AccountNotFound::class);
		$this->expectExceptionMessage('Account social login not found');
		Account::getAccountBySocialId($socialId);
	}

	public function test_date_format_methods(): void {
		$account = Account::createAccount('test', 'test', 'test@test.com', 9, 0);

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
