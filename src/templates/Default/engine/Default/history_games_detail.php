<div class="center"><?php
	if (empty($Rankings)) { ?>
		Click a link to view those stats.<br /><br />
		<form method="POST" action="<?php echo $SelfHREF; ?>">
			<input type="submit" name="action" value="Most Dangerous Sectors" /><br />
			<input type="submit" name="action" value="Top Mined Sectors" /><br />
			<input type="submit" name="action" value="Top Planets" /><br />
			<input type="submit" name="action" value="Top Alliance Kills" /><br />
			<input type="submit" name="action" value="Top Alliance Deaths" /><br />
		</form><?php
	} else { ?>
		<a href="<?php echo $SelfHREF; ?>"><b>&lt;&lt;Back</b></a>
		<table class="center standard">
			<tr>
				<th>Rank</th><?php
				foreach ($Headers as $Header) { ?>
					<th><?php echo $Header; ?></th><?php
				} ?>
			</tr><?php
			foreach ($Rankings as $index => $values) { ?>
				<tr>
					<td><?php echo $index + 1; ?></td><?php
					foreach ($values as $value) { ?>
						<td><?php echo $value; ?></td><?php
					} ?>
				</tr><?php
			} ?>
		</table><?php
	} ?>
</div>
