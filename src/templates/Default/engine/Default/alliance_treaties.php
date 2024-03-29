<?php declare(strict_types=1);

use Smr\Treaty;

/**
 * @var array<array{Alliance: Smr\Alliance, Terms: array<string>, AcceptHREF: string, RejectHREF: string}> $Offers
 * @var array<int, string> $Alliances
 * @var string $SendOfferHREF
 */

?>
<div class="center">
	<?php
	if (isset($Message)) {
		echo $Message . '<br /><br />';
	} ?>

	<?php
	foreach ($Offers as $Offer) { ?>
		Treaty offer from <span class="yellow"><?php echo $Offer['Alliance']->getAllianceDisplayName(true); ?></span>.
		Terms as follows:<br />
		<ul class="noWrap left" style="display: inline-block"><?php
			foreach ($Offer['Terms'] as $Term) { ?>
				<li><?php echo Treaty::TYPES[$Term][0]; ?></li><?php
			} ?>
		</ul>
		<br />
		<div class="buttonA">
			<a class="buttonA" href="<?php echo $Offer['AcceptHREF']; ?>">Accept</a>
			&nbsp;&nbsp;
			<a class="buttonA" href="<?php echo $Offer['RejectHREF']; ?>">Reject</a>
		</div>
		<br /><br /><?php
	} ?>

	<br />
	<h2>Offer A Treaty</h2>
	Select the alliance you wish to offer a treaty.<br />
	<small>Note: Treaties require 24 hours to be canceled once in effect</small><br />

	<form method="POST" action="<?php echo $SendOfferHREF; ?>">
		<select name="proposedAlliance"><?php
			foreach ($Alliances as $allId => $allName) { ?>
				<option value="<?php echo $allId; ?>"><?php echo $allName; ?></option><?php
			} ?>
		</select>
		<br /><br />
		Choose the treaty terms:<br />
		<table class="center standard"><?php
			foreach (Treaty::TYPES as $checkName => $displayInfo) { ?>
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
