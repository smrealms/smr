<?php declare(strict_types=1);

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class Record
{
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
 * @covers Globals
 */
class AbstractSmrAccountTest extends TestCase
{
    private AbstractSmrAccount $abstractSmrAccount;

    public function testGetAccountByAccountId()
    {
        //# Given the database has been set up with a user
        $record = new Record();
        self::setupMockMysqlDatabase($record);
        //# And there is no force update
        $forceUpdate = false;
        //# When the account is retrieved by its ID
        $this->abstractSmrAccount = AbstractSmrAccount::getAccount($record->account_id, $forceUpdate);
        //# Then the integrity of the user is correct
        $this->assertEquals($record->account_id, $this->abstractSmrAccount->getAccountID());
        $this->assertEquals($record->login, $this->abstractSmrAccount->getLogin());
        $this->assertEquals($record->email, $this->abstractSmrAccount->getEmail());
        $this->assertEquals($record->last_login, $this->abstractSmrAccount->getLastLogin());
        $this->assertEquals($record->validation_code, $this->abstractSmrAccount->getValidationCode());
        $this->assertEquals($record->offset, $this->abstractSmrAccount->getOffset());
        $this->assertEquals(true, $this->abstractSmrAccount->isDisplayShipImages());
        $this->assertEquals($record->fontsize, $this->abstractSmrAccount->getFontSize());
        $this->assertEquals($record->password_reset, $this->abstractSmrAccount->getPasswordReset());
        $this->assertEquals($record->mail_banned, $this->abstractSmrAccount->getMailBanned());
        $this->assertEquals($record->friendly_colour, $this->abstractSmrAccount->getFriendlyColour());
        $this->assertEquals($record->neutral_colour, $this->abstractSmrAccount->getNeutralColour());
        $this->assertEquals($record->enemy_colour, $this->abstractSmrAccount->getEnemyColour());
        $this->assertEquals($record->css_link, $this->abstractSmrAccount->getCssLink());
        $this->assertEquals($record->referral_id, $this->abstractSmrAccount->getReferrerID());
        $this->assertEquals($record->hof_name, $this->abstractSmrAccount->getHofName());
        $this->assertEquals($record->discord_id, $this->abstractSmrAccount->getDiscordId());
        $this->assertEquals($record->irc_nick, $this->abstractSmrAccount->getIrcNick());
        $this->assertEquals($record->date_short, $this->abstractSmrAccount->getShortDateFormat());
        $this->assertEquals($record->time_short, $this->abstractSmrAccount->getShortTimeFormat());
        $this->assertEquals($record->template, $this->abstractSmrAccount->getTemplate());
    }

    private static function setupMockMysqlDatabase(Record $record): MockInterface
    {
        //# Force the mock to be used in the autoloader
        $mysqlDatabase = m::mock("overload:SmrMySqlDatabase");
        $mysqlDatabase
            ->shouldReceive("escapeNumber")
            ->with($record->account_id)
            ->andReturn($record->account_id);
        $mysqlDatabase
            ->shouldReceive("query");
        $mysqlDatabase
            ->shouldReceive("nextRecord")
            ->andReturn("a record");
        $mysqlDatabase
            ->shouldReceive("getRow")
            ->andReturn((array)$record);
        $mysqlDatabase
            ->shouldReceive("getBoolean")
            ->andReturn(false);
        $mysqlDatabase
            ->shouldReceive("getObject")
            ->andReturn(array());
        return $mysqlDatabase;
    }
}
