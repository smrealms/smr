<table class="standard center" width="50%">
	<tr>
		<th>Race</th>
		<th>Treaty</th>
	</tr><?php

	foreach($VoteRaceHrefs as $RaceID => $FormHref) { ?>
		<tr>
			<td align="center"><a href="<?php echo Globals::getCouncilHREF($RaceID); ?>"><?php echo $ThisPlayer->getColouredRaceName($RaceID); ?></a></td>
			<td align="center">
				<form method="POST" action="<?php echo $FormHref; ?>">
					<input type="submit" name="action" value="Peace" id="InputFields" />
					&nbsp;
					<input type="submit" name="action" value="War" id="InputFields" />
				</form>
			</td>
		</tr><?php
	} ?>
</table>
