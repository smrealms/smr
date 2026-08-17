<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Pages\Shared\Admin\Unigen\GameDetailsRenderer;

class EditGameRenderer {

	/**
	 * @param array{name: string, description: string, speed: float, maxTurns: int, startTurnHours: int, maxPlayers: int, joinDate: string, startDate: string, endDate: string, smrCredits: int, gameType: string, allianceMax: int, allianceMaxVets: int, startCredits: int, ignoreStats: bool, relations: int, destroyPorts: bool} $Game
	 */
	public static function render(array $Game, string $ProcessingHREF, string $SubmitValue, string $CancelHREF): void {
		GameDetailsRenderer::render(Game: $Game, ProcessingHREF: $ProcessingHREF, SubmitValue: $SubmitValue); ?>

		<p><span class="red">WARNING: </span>Modifying the "Game Type" may put the game in an inconsistent state!</p>

		<a href="<?php echo $CancelHREF; ?>" class="submitStyle">&lt;&lt; Back to Map</a>

		<?php
	}

}
