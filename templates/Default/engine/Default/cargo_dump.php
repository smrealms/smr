Enter the amount of cargo you wish to jettison.<br />
Please keep in mind that you will lose experience and one turn!<br /><br />

<?php
if (empty($Goods)) { ?>
	You have no cargo to dump!<?php
} else { ?>
	<table class="standard">
		<tr>
			<th>Good</th>
			<th>Amount to Drop</th>
			<th>Action</th>
		</tr><?php

		foreach ($Goods as $good) { ?>
			<form name="DumpForm" method="POST" action="<?php echo $good['dump_href']; ?>">
				<tr>
					<td><img src="<?php echo $good['image']; ?>" width="13" height="16" title="<?php echo $good['name']; ?>" />&nbsp;<?php echo $good['name']; ?></td>
					<td class="center">
						<input type="number" name="amount" value="<?php echo $good['amount']; ?>" maxlength="5" size="5" id="InputFields" class="center" />
					</td>
					<td class="center">
						<input type="submit" name="action" value="Dump (1)" id="InputFields" />
					</td>
				</tr>
			</form><?php
		} ?>
	</table><?php
}

?>
