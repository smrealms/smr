<?php declare(strict_types=1);

namespace Smr\Discord;

use Discord\CommandClient\Command as DiscordCommand;
use Discord\DiscordCommandClient;
use Discord\Parts\Channel\Message;
use Smr\Exceptions\UserError;
use Throwable;

abstract class Command {

	protected Message $message;

	/**
	 * Name to invoke this command on Discord.
	 */
	abstract public function name(): string;

	/**
	 * Help-text description of what the command does.
	 */
	abstract public function description(): string;

	/**
	 * Help-text options that can be passed to this command, if any.
	 */
	public function usage(): ?string {
		return null;
	}

	/**
	 * Constructs a textual response to a Command invocation.
	 *
	 * @return array<string>
	 */
	abstract public function response(string ...$args): array;

	protected function logException(Throwable $err): void {
		// Isolate this global function call so it can be mocked during testing.
		// A better solution is probably to switch to Monolog for logging.
		logException($err);
	}

	/**
	 * Wrapper to properly handle a Command response.
	 *
	 * @param array<string> $args Arguments passed to the command.
	 */
	final public function callback(Message $message, array $args): void {
		$this->message = $message;
		try {
			$lines = $this->response(...$args);
		} catch (UserError $err) {
			$lines = [$err->getMessage()];
		} catch (Throwable $err) {
			$this->logException($err);
			$lines = ['I encountered an error. Please report this to an admin!'];
		}
		if ($lines) {
			$message->reply(implode(EOL, $lines))->done(null, 'logException');
		}
	}

	/**
	 * Register a Command class as a command (by passing in the main Discord
	 * client) or a subcommand (by passing in the output of this method from
	 * its parent command).
	 */
	public function register(DiscordCommand|DiscordCommandClient $parent): DiscordCommand {
		// Check if we are registering a command or a subcommand
		if ($parent instanceof DiscordCommandClient) {
			$registrar = $parent->registerCommand(...);
		} else {
			$registrar = $parent->registerSubCommand(...);
		}

		// Construct a usage string from the command name and any options
		$usage = DISCORD_COMMAND_PREFIX . $this->name();
		if ($this->usage() !== null) {
			$usage .= ' ' . $this->usage();
		}

		return $registrar(
			$this->name(),
			$this->callback(...),
			[
				'description' => $this->description(),
				'usage' => $usage,
			]
		);
	}

}
