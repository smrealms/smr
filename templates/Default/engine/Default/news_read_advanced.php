<div align="center">
	<form name="AdvancedNewsForm" method="POST" action="<?php echo $AdvancedNewsFormHref; ?>">
		<table class="standardnobord fullwidth"><tr>
			<td class="center">
				<h2>Player Search</h2>
				<input type="text" name="playerName" size="14"><br /><br /><input type="submit" value="Search For Player" name="submit"><br />
			</td>
			<td class="center">
				<h2>Alliance Search</h2>
				<select name="allianceID" class="InputFields">
					<option value="-1">Select an alliance</option><?php
					if(isset($NewsAlliances) && count($NewsAlliances)>0) {
						foreach($NewsAlliances as $NewsAlliance) {
							?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
						}
					} ?>
				</select><br />
				<br />
				<input type="submit" value="Search For Alliance" name="submit">
			</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="center">
					<h2>Player Vs Player Search</h2>
					<input type="text" name="player1" size="14"> vs. <input type="text" name="player2" size="14"><br />
					<br />
					<input type="submit" value="Search For Players" name="submit">
				</td>
				<td class="center">
					<h2>Alliance Vs Alliance Search</h2>
					<select name="alliance1" class="InputFields">
						<option value="-1">Select an alliance</option><?php
						if(isset($NewsAlliances) && count($NewsAlliances)>0) {
							foreach($NewsAlliances as $NewsAlliance) {
								?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
							}
						} ?>
					</select>
						vs.
					<select name="alliance2" class="InputFields">
						<option value="-1">Select an alliance</option><?php
						if(isset($NewsAlliances) && count($NewsAlliances)>0) {
							foreach($NewsAlliances as $NewsAlliance) {
								?><option value="<?php echo $NewsAlliance['ID']; ?>"><?php echo $NewsAlliance['Name']; ?></option><?php
							}
						} ?>
					</select><br />
					<br />
					<input type="submit" value="Search For Alliances" name="submit">
				</td>
			</tr>
		</table>
	</form>
	<br /><br /><br /><?php
	if (isset($ResultsFor)) { ?>
		Returning results for <?php echo $ResultsFor; ?>.<br /><?php
	}

	if(isset($NewsItems) && count($NewsItems) > 0) { ?>
		Showing most recent <span class="yellow"><?php echo count($NewsItems); ?></span> news items.<br />
		<table class="standard">
			<tr>
				<th class="center">Time</th>
				<th class="center">News</th>
			</tr>
			<?php
			foreach($NewsItems as $NewsItem) { ?>
				<tr>
					<td class="center"><?php echo date(DATE_FULL_SHORT, $NewsItem['Time']); ?></td>
					<td><?php echo $NewsItem['Message']; ?></td>
				</tr><?php
			} ?>
			</table><?php
	}
	else {
		?>No news to read.<?php
	} ?>
</div>
