<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use AbstractSmrAccount;
use AccountNotFoundException;
use Mockery as m;
use SmrTest\BaseIntegrationTest;
use SocialLogin;
use SocialLogins\Facebook;

class Record {
	public int $account_id = 1;
	public string $login = "login";
	public string $password = "password";
	public string $email = "email";
	public string $last_login = "last login";
	public string $validation_code = "validation code";
	public string $offset = "offset";
	public string $images = "Yes";
	public string $fontsize = "font size";
	public string $password_reset = "password reset";
	public int $mail_banned = 0;
	public string $friendly_colour = "colour";
	public string $neutral_colour = "colour";
	public string $enemy_colour = "colour";
	public string $css_link = "css link";
	public string $referral_id = 'referral_id';
	public string $max_rank_achieved = 'max_rank_achieved';
	public string $hof_name = 'hof_name';
	public string $discord_id = 'discord_id';
	public string $irc_nick = 'irc_nick';
	public string $date_short = 'date_short';
	public string $time_short = 'time_short';
	public string $template = 'template';
	public string $colour_scheme = 'colour_scheme';
}

/**
 * Class AbstractSmrAccountTest
 * @covers AbstractSmrAccount
 */
class AbstractSmrAccountIntegrationTest extends BaseIntegrationTest {
	public function test_account_creation() {
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
		$orignal = AbstractSmrAccount::createAccount("test", "test", "test@test.com", 9, 0);
		$orignal->addAuthMethod("facebook", $orignal->getAccountID());
		// And a valid social login
		$socialLogin = m::mock(Facebook::class)->shouldIgnoreMissing();
		$socialLogin
			->expects()
			->isValid()
			->andReturns(true);
		$socialLogin
			->expects()
			->getLoginType()
			->andReturns("facebook");
		$socialLogin
			->expects()
			->getUserId()
			->andReturns($orignal->getAccountID());
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin, true);
		// Then the record is found
		$this->assertEquals($orignal->getAccountID(), $account->getAccountID());
	}

	public function test_get_account_by_social_returns_null_when_social_invalid() {
		// Given an invalid social login
		$socialLogin = m::mock(SocialLogin::class)->shouldIgnoreMissing();
		$socialLogin
			->expects()
			->isValid()
			->andReturns(false);
		// When retrieving account by null social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin);
		// Then the record is null
		$this->assertNull($account);
	}

	public function test_get_account_by_social_returns_null_when_no_record_found() {
		// Given no record exists
		// And a valid social login
		$socialLogin = m::mock(SocialLogin::class)->shouldIgnoreMissing();
		$socialLogin
			->expects()
			->isValid()
			->andReturns(true);
		$socialLogin
			->expects()
			->getLoginType()
			->andReturns("facebook");
		$socialLogin
			->expects()
			->getUserId()
			->andReturns(123);
		// When retrieving account by social
		$account = AbstractSmrAccount::getAccountBySocialLogin($socialLogin);
		// Then the record is null
		$this->assertNull($account);
	}
}
