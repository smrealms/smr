<?php
if(isset($BreakingNews))
{
	?><b>MAJOR NEWS! - <?php echo date(DATE_FULL_SHORT, $BreakingNews['Time']); ?></b><br />
	<table class="standard">
		<tr>
			<th align="center"><span style="color:#80C870;">Time</span></th>
			<th align="center"><span style="color:#80C870;">Breaking News</span></th>
		</tr>
		<tr>
			<td align="center"><?php echo date(DATE_FULL_SHORT, $BreakingNews['Time']); ?></td>
			<td align="left"><?php echo $BreakingNews['Message']; ?></td>
		</tr>
	</table>
	<br /><br /><?php
}

if(isset($LottoNews))
{ ?>
	<b>Lotto News</b><br />
	<table class="standard">
		<tr>
		    <th align="center"><span style="color:#80C870;">Time</span></th>
		    <th align="center"><span style="color:#80C870;">Message</span></th>
	    </tr>
	    <tr>
		    <td align="center"><?php echo date(DATE_FULL_SHORT, $LottoNews['Time']); ?></td>
		    <td align="left"><?php echo $LottoNews['Message']; ?></td>
	    </tr>
    </table>
	<br /><br /><?php
} ?>