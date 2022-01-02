<?php
if (!isset($Nick)) { ?>
	<p>Nothing to approve!</p><?php
	return;
} ?>

<table class="nobord">
	<tr>
		<td colspan="2" class="center">
			<span style="font-size:150%;"><?php echo $Nick; ?></span>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="center">
			<img src="<?php echo $ImgSrc; ?>">
		</td>
	</tr>
	<tr>
		<td class="right bold" width="10%">Location :</td>
		<td><?php echo $Location; ?></td>
	</tr>
	<tr>
		<td class="right bold" width="10%">E-mail :</td>
		<td><?php echo $Email; ?></td>
	</tr>
	<tr>
		<td class="right bold" width="10%">Website :</td>
		<td><a href="<?php echo $Website; ?>"><?php echo $Website; ?></a></td>
	</tr>
	<tr>
		<td class="right bold" width="10%">Birthdate :</td>
		<td><?php echo $Birthdate; ?></td>
	</tr>
	<tr>
		<td class="right top bold" width="10%">Other&nbsp;Info :<br /><small>(AIM/ICQ)&nbsp;&nbsp;</small></td>
		<td><?php echo $Other; ?></td>
	</tr>
</table>

<p>Waiting for approval for <?php echo format_time($TimePassed); ?>.</p>

<a href="<?php echo $ApproveHREF; ?>" class="submitStyle">Approve</a>
&nbsp;&nbsp;&nbsp;
<a href="<?php echo $RejectHREF; ?>" class="submitStyle">Reject</a>
