<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversFunction;
use Smr\Alliance;
use Smr\Game;
use Smr\Player;
use SmrTest\BaseIntegrationSpec;

require_once(LIB . 'Default/nha.inc.php');

#[CoversFunction('createNHA')]
class CreateNHATest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return [
			'alliance',
			'alliance_has_roles',
			'alliance_thread',
			'alliance_thread_topic',
			'player',
			'player_has_alliance_role',
		];
	}

	public function test_createNHA(): void {
		// Set up a fake game (needed for Player::getHome)
		$gameID = 1;
		$game = Game::createGame($gameID);
		$game->setGameTypeID(Game::GAME_TYPE_DEFAULT);

		// Create the NHA
		createNHA($gameID);

		// Reload NHA and make sure relevant properties are set
		$alliance = Alliance::getAllianceByName(NHA_ALLIANCE_NAME, $gameID, true);
		self::assertSame(ACCOUNT_ID_NHL, $alliance->getLeaderID());
		self::assertSame('Newbie Help Alliance', $alliance->getAllianceName());
		self::assertSame(DISCORD_SERVER_ID, $alliance->getDiscordServer());
		self::assertSame('Alliance message board includes tips and FAQs.', $alliance->getMotD());
		self::assertSame('Newbie Help Alliance', $alliance->getDescription());
		self::assertFalse($alliance->isRecruiting());

		// Reload NHL and make sure it's set
		$nhl = Player::getPlayer(ACCOUNT_ID_NHL, $gameID, true);
		self::assertSame('Newbie Help Leader', $nhl->getPlayerName());
		self::assertSame($alliance->getAllianceID(), $nhl->getAllianceID());
	}

}
