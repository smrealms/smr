<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractShip;
use Smr\Account;
use Smr\Combat\Results\Full\ForceFullCombatResults;
use Smr\Combat\Results\Full\PlanetFullCombatResults;
use Smr\Combat\Results\Full\PortFullCombatResults;
use Smr\Combat\Results\Full\TraderFullCombatResults;
use Smr\Pages\Shared\ForceFullCombatResultsRenderer;
use Smr\Pages\Shared\MissionsRenderer;
use Smr\Pages\Shared\PlanetFullCombatResultsRenderer;
use Smr\Pages\Shared\PlottedCourseRenderer;
use Smr\Pages\Shared\PortFullCombatResultsRenderer;
use Smr\Pages\Shared\SectorForcesRenderer;
use Smr\Pages\Shared\SectorLocationsRenderer;
use Smr\Pages\Shared\SectorNavigationRenderer;
use Smr\Pages\Shared\SectorPlanetRenderer;
use Smr\Pages\Shared\SectorPlayersRenderer;
use Smr\Pages\Shared\SectorPortRenderer;
use Smr\Pages\Shared\TickerRenderer;
use Smr\Pages\Shared\TraderFullCombatResultsRenderer;
use Smr\Planet;
use Smr\Player;
use Smr\Sector;
use Smr\Template;

class CurrentSectorRenderer {

	/**
	 * @param array<string, array{ID: int, Class: string}> $Sectors
	 * @param array<int, ?string> $UnreadMissions
	 * @param array<int, Player> $VisiblePlayers
	 * @param array<int, Player> $CloakedPlayers
	 * @param ?array{Results: \Smr\Combat\Results\Full\FullCombatResults, Link: string} $AttackResults
	 * @param ?array<array{Time: string, Message: string}> $Ticker
	 */
	public static function render(
		Template $template,
		array $Sectors,
		array $UnreadMissions,
		?string $TurnsMessage,
		?string $ProtectionMessage,
		?string $ForceRefreshMessage,
		?string $MissionMessage,
		?string $VarMessage,
		?string $ErrorMessage,
		?string $TradeMessage,
		?bool $PortIsAtWar,
		array $VisiblePlayers,
		array $CloakedPlayers,
		string $SectorPlayersLabel,
		?array $AttackResults,
		Player $ThisPlayer,
		Sector $ThisSector,
		AbstractShip $ThisShip,
		Account $ThisAccount,
		?Planet $ThisPlanet,
		?array $Ticker,
	): void {
		?>
		<table class="fullwidth" style="border:none">
			<tr>
				<td class="top nopad"><?php
					SectorNavigationRenderer::render(
						ThisPlayer: $ThisPlayer,
						ThisSector: $ThisSector,
						ThisShip: $ThisShip,
						Sectors: $Sectors,
					); ?>
				</td>
				<td class="top nopad" style="width:32em;"><?php
					PlottedCourseRenderer::render(
						ThisPlayer: $ThisPlayer,
						ThisSector: $ThisSector,
						ThisShip: $ThisShip,
					);
					TickerRenderer::render($Ticker);
					MissionsRenderer::render(
						ThisPlayer: $ThisPlayer,
						UnreadMissions: $UnreadMissions,
						MissionMessage: $MissionMessage,
					); ?>
					<span id="secmess"><?php
						if ($ErrorMessage !== null) {
							echo $ErrorMessage; ?><br /><?php
						}
						if ($ProtectionMessage !== null) {
							echo $ProtectionMessage; ?><br /><?php
						}
						if ($TurnsMessage !== null) {
							echo $TurnsMessage; ?><br /><?php
						}
						if ($TradeMessage !== null) {
							echo $TradeMessage; ?><br /><?php
						}
						if ($ForceRefreshMessage !== null) {
							echo $ForceRefreshMessage; ?><br /><?php
						}
						if ($AttackResults !== null) {
							$results = $AttackResults['Results'];
							if ($results instanceof TraderFullCombatResults) {
								TraderFullCombatResultsRenderer::render(
									template: $template,
									TraderCombatResults: $results,
									MinimalDisplay: true,
									AttackLogLink: $AttackResults['Link'],
									ThisPlayer: $ThisPlayer,
								);
							} elseif ($results instanceof ForceFullCombatResults) {
								ForceFullCombatResultsRenderer::render(
									template: $template,
									FullForceCombatResults: $results,
								);
							} elseif ($results instanceof PortFullCombatResults) {
								PortFullCombatResultsRenderer::render(
									template: $template,
									FullPortCombatResults: $results,
									MinimalDisplay: true,
									AlreadyDestroyed: false,
									ThisPlayer: $ThisPlayer,
									AttackLogLink: $AttackResults['Link'],
								);
							} elseif ($results instanceof PlanetFullCombatResults) {
								PlanetFullCombatResultsRenderer::render(
									template: $template,
									FullPlanetCombatResults: $results,
									MinimalDisplay: true,
									ThisPlayer: $ThisPlayer,
									AttackLogLink: $AttackResults['Link'],
								);
							} else {
								throw new Exception('Unknown combat result type');
							} ?><br /><?php
						}
						if ($VarMessage !== null) {
							echo bbify($VarMessage); ?><br /><?php
						} ?>
					</span>
				</td>
			</tr>
		</table><br /><?php
		SectorPlanetRenderer::render(ThisSector: $ThisSector);
		SectorPortRenderer::render(
			ThisPlayer: $ThisPlayer,
			ThisSector: $ThisSector,
			PortIsAtWar: $PortIsAtWar,
		);
		SectorLocationsRenderer::render(ThisSector: $ThisSector);
		SectorPlayersRenderer::render(
			ThisAccount: $ThisAccount,
			ThisPlanet: $ThisPlanet,
			ThisPlayer: $ThisPlayer,
			VisiblePlayers: $VisiblePlayers,
			CloakedPlayers: $CloakedPlayers,
			SectorPlayersLabel: $SectorPlayersLabel,
		);
		SectorForcesRenderer::render(
			ThisAccount: $ThisAccount,
			ThisPlayer: $ThisPlayer,
			ThisSector: $ThisSector,
			ThisShip: $ThisShip,
		); ?>
		<br />

		<?php
	}

}
