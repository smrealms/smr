<div class="center">
	<?php
	if (isset($Message)) {
		echo $Message . '<br /><br />';
	} ?>

	<?php
	foreach ($Offers as $Offer) { ?>
		Treaty offer from <span class="yellow"><?php echo $Offer['Alliance']->getAllianceName(true); ?></span>.
		Terms as follows:<br />
		<ul class="noWrap left" style="display: inline-block"><?php
			foreach ($Offer['Terms'] as $Term) { ?>
				<li><?php echo SmrTreaty::TYPES[$Term][0]; ?></li><?php
			} ?>
		</ul>
		<br />
		<div class="buttonA">
			<a class="buttonA" href="<?php echo $Offer['AcceptHREF']; ?>">&nbsp;Accept&nbsp;</a>
			&nbsp;&nbsp;
			<a class="buttonA" href="<?php echo $Offer['RejectHREF']; ?>">&nbsp;Reject&nbsp;</a>
		</div>
		<br /><br /><?php
	} ?>

	<br />
	<h2>Offer A Treaty</h2>
	Select the alliance you wish to offer a treaty.<br />
	<small>Note: Treaties require 24 hours to be canceled once in effect</small><br />

	<form method="POST" action="<?php echo $SendOfferHREF; ?>">
		<select name="proposedAlliance" id="InputFields"><?php
			foreach ($Alliances as $allId => $allName) { ?>
				<option value="<?php echo $allId; ?>"><?php echo $allName; ?></option><?php
			} ?>
		</select>
		<br /><br />
		Choose the treaty terms:<br />
		<table class="center standard"><?php
			foreach (SmrTreaty::TYPES as $checkName => $displayInfo) { ?>
				<tr>
					<td><input type="checkbox" name="<?php echo $checkName; ?>"></td>
					<td class="left"><?php echo $displayInfo[0]; ?><br /><small><?php echo $displayInfo[1]; ?></small></td>
				</tr><?php
			} ?>
			<tr>
				<td colspan="2">
					<input type="submit" name="action" value="Send the Offer" />
				</td>
			</tr>
		</table>
	</form>
</div>
