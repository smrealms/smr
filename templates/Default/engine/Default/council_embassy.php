<a href="<?php echo WIKI_URL; ?>/game-guide/politics" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
<div class="center bold">Diplomatic Treaties</div><br />
<div class="center standard">
	Welcome President <?php echo $ThisPlayer->getDisplayName(); ?>,<br /><br />
	Below you may decide to declare War or make Peace with other races within the Universe.<br />Remember that Peace votes are subject to veto by corresponding Racial President.<br />Choose wisely, for the fate of your race may lie with your decision.
</div><br /><br />

<table class="standard center" width="50%">
	<tr>
		<th>Race</th>
		<th>Treaty</th>
	</tr><?php

	foreach ($VoteRaceHrefs as $RaceID => $FormHref) { ?>
		<tr>
			<td><img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="60" height="64" /><br /><?php echo $ThisPlayer->getColouredRaceName($RaceID, true); ?></td>
			<td>
				<form method="POST" action="<?php echo $FormHref; ?>">
					<input type="submit" name="action" value="Peace" class="InputFields" />
					&nbsp;
					<input type="submit" name="action" value="War" class="InputFields" />
				</form>
			</td>
		</tr><?php
	} ?>
</table>
