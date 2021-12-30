<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrAlliance;
use SmrTest\BaseIntegrationSpec;

require_once(LIB . 'Default/nha.inc.php');

/**
 * @covers ::createNHA
 */
class createNHATest extends BaseIntegrationSpec {

	public function test_createNHA() : void {
		// Create the NHA
		$gameID = 1;
		createNHA($gameID);

		// Reload NHA and make sure relevant properties are set
		$alliance = SmrAlliance::getAllianceByName(NHA_ALLIANCE_NAME, $gameID, true);
		self::assertSame(ACCOUNT_ID_NHL, $alliance->getLeaderID());
		self::assertSame('Newbie Help Alliance', $alliance->getAllianceName());
		self::assertSame(DISCORD_SERVER_ID, $alliance->getDiscordServer());
		self::assertSame('Alliance message board includes tips and FAQs.', $alliance->getMotD());
		self::assertSame('Newbie Help Alliance', $alliance->getDescription());
		self::assertFalse($alliance->isRecruiting());
	}

}
