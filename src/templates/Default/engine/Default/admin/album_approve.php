<?php declare(strict_types=1);

if (!isset($Nick)) { ?>
	<p>Nothing to approve!</p><?php
	return;
} ?>

<div class="center">
	<p style="font-size:150%;"><?php echo $Nick; ?></p>
	<img src="<?php echo $ImgSrc; ?>">
</div>
<br />
<table class="nobord">
	<tr>
		<td class="right bold">Location :</td>
		<td><?php echo $Location; ?></td>
	</tr>
	<tr>
		<td class="right bold">E-mail :</td>
		<td><?php echo $Email; ?></td>
	</tr>
	<tr>
		<td class="right bold">Website :</td>
		<td><?php echo $Website; ?></td>
	</tr>
	<tr>
		<td class="right bold">Birthdate :</td>
		<td><?php echo $Birthdate; ?></td>
	</tr>
	<tr>
		<td class="right top bold">Other&nbsp;Info :</td>
		<td><?php echo $Other; ?></td>
	</tr>
</table>

<p>Waiting for approval for <?php echo format_time($TimePassed); ?>.</p>

<a href="<?php echo $ApproveHREF; ?>" class="submitStyle">Approve</a>
&nbsp;&nbsp;&nbsp;
<a href="<?php echo $RejectHREF; ?>" class="submitStyle">Reject</a>
