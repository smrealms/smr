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
					<td align="center"><?php echo $good['name']; ?></td>
					<td align="center">
						<input type="number" name="amount" value="<?php echo $good['amount']; ?>" maxlength="5" size="5" id="InputFields" class="center" />
					</td>
					<td align="center">
						<input type="submit" name="action" value="Dump" id="InputFields" />
					</td>
				</tr>
			</form><?php
		} ?>
	</table><?php
}

?>
