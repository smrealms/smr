<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Race;

class NpcManageRenderer {

	/**
	 * @param list<array{Name: string, ID: int, Selected: bool}> $Games
	 * @param array<int, array{default_player_name: string, default_alliance: string, href: string, player: ?array{active: bool, working: bool, disable_active_toggle: bool, name: string, race: string, alliance: string, ship: string}}> $Npcs
	 * @param list<\Smr\Galaxy> $NpcGalaxyChoices
	 * @param array<int, string> $NpcGalaxyAllianceChoices
	 * @param array<int, \Smr\ShipType> $ShipTypes
	 */
	public static function render(
		string $SelectGameHREF,
		?string $Message,
		array $Games,
		int $SelectedGameID,
		string $AddAccountHREF,
		string $NextLogin,
		array $Npcs,
		array $NpcGalaxyChoices,
		array $NpcGalaxyAllianceChoices,
		string $SetupNpcGalaxyHref,
		array $ShipTypes,
	): void {
		if ($Message !== null) { ?>
			<p><?php echo $Message; ?></p><?php
		} ?>

		<form method="POST" action="<?php echo $SelectGameHREF; ?>">
			<select name="selected_game_id" onchange="this.form.submit()"><?php
				foreach ($Games as $Game) { ?>
					<option <?php if ($Game['Selected']) { ?>selected<?php } ?> value="<?php echo $Game['ID']; ?>"><?php echo $Game['Name']; ?></option><?php
				} ?>
			</select>&nbsp;
			<?php echo create_submit_display('Select'); ?>
		</form>

		<?php
		if ($SelectedGameID !== 0) { ?>
			<br />
			<div><b>Note: </b>For-hire NPCs use alliance name: <?php echo NPC_FOR_HIRE_ALLIANCE_NAME; ?></div>
			<table class="standard">
				<tr>
					<th>ID</th>
					<th>Active</th>
					<th>Player Name</th>
					<th>Race</th>
					<th>Alliance</th>
					<th>Ship</th>
					<th>Status</th>
				</tr><?php
				foreach ($Npcs as $accountID => $npc) { ?>
					<tr>
						<td><?php echo $accountID; ?></td><?php
						if ($npc['player'] === null) {
							// The form wrapping only these columns is invalid HTML, but it works for now... ?>
							<form method="POST" action="<?php echo $npc['href']; ?>">
								<td></td>
								<td><input required name="player_name" value="<?php echo $npc['default_player_name']; ?>" /></td>
								<td>
									<select name="race_id"><?php
										foreach (Race::getPlayableIDs() as $raceID) { ?>
											<option value="<?php echo $raceID; ?>"><?php echo Race::getName($raceID); ?></option><?php
										} ?>
									</select>
								</td>
								<td><input name="player_alliance" value="<?php echo $npc['default_alliance']; ?>" /></td>
								<td>
									<select name="player_ship">
										<option value="-1">&lt;default&gt;</option><?php
										foreach ($ShipTypes as $shipTypeID => $shipType) { ?>
											<option value="<?php echo $shipTypeID; ?>"><?php echo $shipType->getName(); ?></option><?php
										} ?>
									</select>
								</td>
								<td><?php echo create_submit('create_npc_player', 'Create'); ?></td>
							</form><?php
						} else {
							$npcPlayer = $npc['player']; ?>
							<td class="center">
								<form method="POST" action="<?php echo $npc['href']; ?>">
									<input name="active" type="checkbox" <?php if ($npcPlayer['active']) { ?>checked<?php } ?> onclick="this.form.submit()" <?php if ($npcPlayer['disable_active_toggle']) { ?>disabled<?php } ?> />
									<input type="hidden" name="active-submit" />
								</form>
							</td>
							<td><?php echo $npcPlayer['name']; ?></td>
							<td><?php echo $npcPlayer['race']; ?></td>
							<td><?php echo $npcPlayer['alliance']; ?></td>
							<td><?php echo $npcPlayer['ship']; ?></td>
							<td class="center"><?php echo $npcPlayer['working'] ? 'Working' : 'Idle'; ?></td><?php
						} ?>
					</tr><?php
				} ?>
			</table>

			<br /><br />
			<h2>Setup NPC Galaxy</h2>
			<p>Creates a Sentinel Outpost planet in the selected galaxy owned by the
			selected alliance and fills most sectors in the galaxy with non-expiring NPC mines.</p>
			<form method="POST" action="<?php echo $SetupNpcGalaxyHref; ?>">
				Galaxy: <select name="galaxy_id"><?php
					foreach ($NpcGalaxyChoices as $galaxy) { ?>
						<option value="<?php echo $galaxy->getGalaxyID(); ?>"><?php echo $galaxy->getDisplayName(); ?></option><?php
					} ?>
				</select>
				<br />
				Alliance: <select name="alliance_id"><?php
					foreach ($NpcGalaxyAllianceChoices as $allianceID => $allianceName) { ?>
						<option value="<?php echo $allianceID; ?>"><?php echo $allianceName; ?></option><?php
					} ?>
				</select>
				<br /><br />
				<?php echo create_submit_display('Setup Galaxy'); ?>
			</form><?php
		} ?>

		<br /><br />
		<h2>Add New NPC Login</h2>
		<form method="POST" action="<?php echo $AddAccountHREF; ?>">
			<input type="hidden" name="npc_login" value="<?php echo $NextLogin; ?>" />
			Login: <?php echo $NextLogin; ?><br />
			Default Player Name: <input required name="default_player_name" /><br />
			Default Alliance: <input required name="default_alliance" /><br />
			<?php echo create_submit_display('Submit'); ?>
		</form>

		<?php
	}

}
