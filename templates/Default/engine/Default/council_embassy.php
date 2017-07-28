<a href="<?php echo WIKI_URL; ?>/index.php?title=Game_Guide:_Politics_and_the_Ruling_Council" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: Politics"/></a>
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

	foreach($VoteRaceHrefs as $RaceID => $FormHref) { ?>
		<tr>
			<td align="center"><img src="<?php echo Globals::getRaceHeadImage($RaceID); ?>" width="60" height="64" /><br /><a href="<?php echo Globals::getCouncilHREF($RaceID); ?>"><?php echo $ThisPlayer->getColouredRaceName($RaceID); ?></a></td>
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
