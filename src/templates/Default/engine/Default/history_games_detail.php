<div class="center"><?php
	if (empty($Rankings)) { ?>
		Click a link to view those stats.<br /><br />
		<form method="POST" action="<?php echo $SelfHREF; ?>">
			<input type="submit" name="action" value="Top Mined Sectors" /><br />
			<input type="submit" name="action" value="Sectors with most Forces" /><br />
			<input type="submit" name="action" value="Top Killing Sectors" /><br />
			<input type="submit" name="action" value="Top Planets" /><br />
			<input type="submit" name="action" value="Top Alliance Kills" /><br />
			<input type="submit" name="action" value="Top Alliance Deaths" /><br />
		</form><?php
	} else { ?>
		<a href="<?php echo $SelfHREF; ?>"><b>&lt;&lt;Back</b></a>
		<table class="center standard">
			<tr>
				<th>Rank</th>
				<th><?php echo $Name; ?></th>
				<th><?php echo $Description; ?></th>
			</tr><?php
			foreach ($Rankings as $index => $ranking) { ?>
				<tr>
					<td><?php echo $index + 1; ?></td>
					<td><?php echo $ranking['name']; ?></td>
					<td><?php echo $ranking['value']; ?></td>
				</tr><?php
			} ?>
		</table><?php
	} ?>
</div>
