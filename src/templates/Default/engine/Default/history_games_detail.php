<?php declare(strict_types=1);

/**
 * @var string $SelfHREF
 * @var ?array<string> $Headers
 */

?>
<div class="center"><?php
	if (empty($Rankings)) { ?>
		Click a link to view those stats.<br /><br />
		<form method="POST" action="<?php echo $SelfHREF; ?>">
			<p><input type="submit" name="action" value="Most Dangerous Sectors" /></p>
			<p><input type="submit" name="action" value="Top Mined Sectors" /></p>
			<p><input type="submit" name="action" value="Top Planets" /></p>
			<p><input type="submit" name="action" value="Top Alliance Kills" /></p>
			<p><input type="submit" name="action" value="Top Alliance Deaths" /></p>
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
			foreach ($Rankings as $index => $row) { ?>
				<tr <?php echo $row['bold']; ?>>
					<td><?php echo $index + 1; ?></td><?php
					foreach ($row['data'] as $value) { ?>
						<td><?php echo $value; ?></td><?php
					} ?>
				</tr><?php
			} ?>
		</table><?php
	} ?>
</div>
