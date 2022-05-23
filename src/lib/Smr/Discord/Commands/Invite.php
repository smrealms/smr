<?php declare(strict_types=1);

namespace Smr\Discord\Commands;

use Discord\DiscordCommandClient;
use Smr\Discord\Command;

class Invite extends Command {

	public function __construct(protected DiscordCommandClient $client) {}

	public function name(): string {
		return 'invite';
	}

	public function description(): string {
		return 'Invite Autopilot to join your server!';
	}

	public function response(string ...$args): array {
		return [
			$this->client->username . ' can be invited to join your server! Just click this link and select your server:',
			'<https://discordapp.com/oauth2/authorize?&client_id=' . $this->client->id . '&scope=bot&permissions=0>',
			'',
			'NOTE: you must have manager permissions to perform this action.',
		];
	}

}
