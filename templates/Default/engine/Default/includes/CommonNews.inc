<?php
if(isset($BreakingNews)) {
	?><b>MAJOR NEWS! - <?php echo date(DATE_FULL_SHORT, $BreakingNews['Time']); ?></b><br />
	<table class="standard">
		<tr>
			<th><span class="lgreen">Time</span></th>
			<th><span class="lgreen">Breaking News</span></th>
		</tr>
		<tr>
			<td class="center"><?php echo date(DATE_FULL_SHORT, $BreakingNews['Time']); ?></td>
			<td class="left"><?php echo $BreakingNews['Message']; ?></td>
		</tr>
	</table>
	<br /><br /><?php
}

if(isset($LottoNews)) { ?>
	<b>Lotto News</b><br />
	<table class="standard">
		<tr>
		    <th><span class="lgreen">Time</span></th>
		    <th><span class="lgreen">Message</span></th>
	    </tr>
	    <tr>
		    <td class="center"><?php echo date(DATE_FULL_SHORT, $LottoNews['Time']); ?></td>
		    <td class="left"><?php echo $LottoNews['Message']; ?></td>
	    </tr>
    </table>
	<br /><br /><?php
} ?>
