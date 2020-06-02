<div class="center">
	<table class="standardnobord fullwidth">
		<tr>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsFormHref; ?>">
					<h2>Player Search</h2>
					<input type="text" name="playerName" required size="14"><br /><br />
					<input type="submit" value="Search For Player" name="submit"><br />
				</form>
			</td>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsFormHref; ?>">
					<h2>Alliance Search</h2>
					<select name="allianceID" required class="InputFields">
						<option value="" disabled selected>Select an alliance</option><?php
						if (isset($NewsAlliances) && count($NewsAlliances) > 0) {
							foreach ($NewsAlliances as $NewsAlliance) {
								?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
							}
						} ?>
					</select><br />
					<br />
					<input type="submit" value="Search For Alliance" name="submit">
				</form>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsFormHref; ?>">
					<h2>Player Vs Player Search</h2>
					<input type="text" name="player1" required size="14"> vs. <input type="text" name="player2" required size="14"><br />
					<br />
					<input type="submit" value="Search For Players" name="submit">
				</form>
			</td>
			<td class="center">
				<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsFormHref; ?>">
					<h2>Alliance Vs Alliance Search</h2>
					<select name="alliance1" required class="InputFields">
						<option value="" disabled selected>Select an alliance</option><?php
						if (isset($NewsAlliances) && count($NewsAlliances) > 0) {
							foreach ($NewsAlliances as $NewsAlliance) {
								?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
							}
						} ?>
					</select>
						vs.
					<select name="alliance2" required class="InputFields">
						<option value="" disabled selected>Select an alliance</option><?php
						if (isset($NewsAlliances) && count($NewsAlliances) > 0) {
							foreach ($NewsAlliances as $NewsAlliance) {
								?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
							}
						} ?>
					</select><br />
					<br />
					<input type="submit" value="Search For Alliances" name="submit">
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
if (!empty($NewsItems)) { ?>
	<div class="center">
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
	</div><?php
	$this->includeTemplate('includes/NewsTable.inc');
} else {
	?>No news to read.<?php
} ?>
