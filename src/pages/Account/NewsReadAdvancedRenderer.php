<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Pages\Shared\NewsTableRenderer;

class NewsReadAdvancedRenderer {

/**
 * @param array<int, string> $NewsAlliances
 * @param array<array{Date: string, Message: string}> $NewsItems
 */
public static function render(
	array $NewsAlliances,
	NewsReadAdvancedProcessor $AdvancedNewsForm,
	?string $ResultsFor,
	array $NewsItems,
): void {
?>
<div class="center">
	<table class="standardnobord fullwidth">
		<tr>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsForm->href(); ?>">
					<h2>Player Search</h2>
					<input type="text" name="playerName" required size="14"><br /><br />
					<?php echo $AdvancedNewsForm->actionSearchPlayer->html('Search For Player'); ?><br />
				</form>
			</td>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsForm->href(); ?>">
					<h2>Alliance Search</h2>
					<select name="allianceID" required>
						<option value="" disabled selected>Select an alliance</option><?php
						foreach ($NewsAlliances as $NewsAllianceID => $NewsAllianceName) {
							?><option value="<?php echo $NewsAllianceID; ?>"><?php echo $NewsAllianceName; ?></option><?php
						} ?>
					</select><br />
					<br />
					<?php echo $AdvancedNewsForm->actionSearchAlliance->html('Search For Alliance'); ?>
				</form>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsForm->href(); ?>">
					<h2>Player Vs Player Search</h2>
					<input type="text" name="player1" required size="14"> vs. <input type="text" name="player2" required size="14"><br />
					<br />
					<?php echo $AdvancedNewsForm->actionSearchPlayers->html('Search For Players'); ?>
				</form>
			</td>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsForm->href(); ?>">
					<h2>Alliance Vs Alliance Search</h2>
					<select name="alliance1" required>
						<option value="" disabled selected>Select an alliance</option><?php
						foreach ($NewsAlliances as $NewsAllianceID => $NewsAllianceName) {
							?><option value="<?php echo $NewsAllianceID; ?>"><?php echo $NewsAllianceName; ?></option><?php
						} ?>
					</select>
						vs.
					<select name="alliance2" required>
						<option value="" disabled selected>Select an alliance</option><?php
						foreach ($NewsAlliances as $NewsAllianceID => $NewsAllianceName) {
							?><option value="<?php echo $NewsAllianceID; ?>"><?php echo $NewsAllianceName; ?></option><?php
						} ?>
					</select><br />
					<br />
					<?php echo $AdvancedNewsForm->actionSearchAlliances->html('Search For Alliances'); ?>
				</form>
			</td>
		</tr>
	</table>
	<br /><br /><?php
	if (isset($ResultsFor)) { ?>
		Returning results for <?php echo htmlentities($ResultsFor); ?>.<br /><?php
	} ?>
</div>

<?php
if (count($NewsItems) > 0) { ?>
	<div class="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	NewsTableRenderer::render(NewsItems: $NewsItems);
} else {
	?>No news to read.<?php
}

}

}
