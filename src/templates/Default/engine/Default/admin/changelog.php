<?php declare(strict_types=1);

/**
 * @var array<array{version: string, went_live: string, changes: array<array{title: string, message: string}>}> $Versions
 */

if (count($Versions) === 0) { ?>
	Must add an initial version in the database first!<?php
	return;
}

$this->includeTemplate('changelog_view.php', ['Versions' => [$FirstVersion]]); ?>

<ul>
	<li>
		<form method="POST" action="<?php echo $AddHREF; ?>">
			<table>
				<tr>
					<td colspan="2"><small>Title:</small></td>
				</tr>
				<tr>
					<td colspan="2"><input type="text" name="change_title" value="<?php echo htmlentities($ChangeTitle); ?>" style="width:400px;" required></td>
				</tr>
				<tr>
					<td><small>Message (BBCode):</small></td>
					<td><small>Affected Database:</small></td>
				</tr>
				<tr>
					<td><textarea spellcheck="true" name="change_message" style="width:400px;height:50px;" required><?php echo htmlentities($ChangeMessage); ?></textarea></td>
					<td><textarea spellcheck="true" name="affected_db" style="width:200px;height:50px;"><?php echo htmlentities($AffectedDb); ?></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td class="right">
						<?php echo create_submit('action', 'Preview'); ?>&nbsp;
						<?php echo create_submit('action', 'Add'); ?>
					</td>
				</tr>
			</table>
		</form>
	</li>
</ul>

<?php
$this->includeTemplate('changelog_view.php');
