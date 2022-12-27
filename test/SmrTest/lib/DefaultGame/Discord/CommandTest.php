<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame\Discord;

use Discord\CommandClient\Command as DiscordCommand;
use Discord\DiscordCommandClient;
use Discord\Parts\Channel\Message;
use Exception;
use PHPUnit\Framework\TestCase;
use React\Promise\ExtendedPromiseInterface;
use Smr\Discord\Commands\MagicEightBall;
use Smr\Exceptions\UserError;

/**
 * @covers Smr\Discord\Command
 */
class CommandTest extends TestCase {

	public function test_callback_happy_path(): void {
		// Stub the response method of an arbitrary Command to return normally
		$mockCommand = $this->createPartialMock(MagicEightBall::class, ['response']);
		$mockCommand->method('response')->willReturn(['A', 'B']);

		// Mock the DiscordPHP Message, and make sure we call reply on it
		$mockPromise = $this->createMock(ExtendedPromiseInterface::class);
		$mockPromise
			->expects(self::once())
			->method('done');
		$mockMessage = $this->createMock(Message::class);
		$mockMessage
			->expects(self::once())
			->method('reply')
			->with("A\nB")
			->willReturn($mockPromise);

		$mockCommand->callback($mockMessage, []);
	}

	public function test_callback_catches_UserError(): void {
		// Stub the response method of an arbitrary Command to throw a UserError exception
		$mockCommand = $this->createPartialMock(MagicEightBall::class, ['response']);
		$msg = 'This is a test';
		$mockCommand->method('response')->willThrowException(new UserError($msg));

		// Mock the DiscordPHP Message, and make sure we call reply on it
		$mockPromise = $this->createMock(ExtendedPromiseInterface::class);
		$mockPromise
			->expects(self::once())
			->method('done');
		$mockMessage = $this->createMock(Message::class);
		$mockMessage
			->expects(self::once())
			->method('reply')
			->with($msg)
			->willReturn($mockPromise);

		$mockCommand->callback($mockMessage, []);
	}

	public function test_callback_catches_Exception(): void {
		// Stub the response method of an arbitrary Command to throw an Exception
		$mockCommand = $this->createPartialMock(MagicEightBall::class, ['response', 'logException']);
		$err = new Exception(__METHOD__);
		$mockCommand
			->method('response')
			->willThrowException($err);
		$mockCommand
			->expects(self::once())
			->method('logException')
			->with($err);

		// Mock the DiscordPHP Message, and make sure we call reply on it
		$mockPromise = $this->createMock(ExtendedPromiseInterface::class);
		$mockPromise
			->expects(self::once())
			->method('done');
		$mockMessage = $this->createMock(Message::class);
		$mockMessage
			->expects(self::once())
			->method('reply')
			->with('I encountered an error. Please report this to an admin!')
			->willReturn($mockPromise);

		$mockCommand->callback($mockMessage, []);
	}

	public function test_callback_returns_empty(): void {
		// Stub the response method of an arbitrary Command to return empty
		$mockCommand = $this->createPartialMock(MagicEightBall::class, ['response']);
		$mockCommand->method('response')->willReturn([]);

		// Mock the DiscordPHP Message, and make sure we do NOT call reply on it
		$mockMessage = $this->createMock(Message::class);
		$mockMessage
			->expects(self::never())
			->method('reply');

		$mockCommand->callback($mockMessage, []);
	}

	public function test_register_command(): void {
		// Register a command through the client
		$command = new MagicEightBall();
		$mockClient = $this->createMock(DiscordCommandClient::class);
		$mockClient
			->expects(self::once())
			->method('registerCommand')
			->with(
				'8ball',
				$command->callback(...),
				['description' => $command->description(), 'usage' => '.8ball [question]'],
			);
		$command->register($mockClient);
	}

	public function test_register_subcommand(): void {
		// Register a subcommand through its parent command
		$command = new MagicEightBall();
		$mockClient = $this->createMock(DiscordCommand::class);
		$mockClient
			->expects(self::once())
			->method('registerSubCommand');
		$command->register($mockClient);
	}

}
