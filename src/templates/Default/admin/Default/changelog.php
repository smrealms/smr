<?php
if (empty($Versions)) { ?>
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
					<td colspan="2"><input type="text" name="change_title" value="<?php echo $ChangeTitle; ?>" style="width:400px;" required></td>
				</tr>
				<tr>
					<td><small>Message (HTML):</small></td>
					<td><small>Affected Database:</small></td>
				</tr>
				<tr>
					<td><textarea spellcheck="true" name="change_message" style="width:400px;height:50px;" required><?php echo $ChangeMessage; ?></textarea></td>
					<td><textarea spellcheck="true" name="affected_db" style="width:200px;height:50px;"><?php echo $AffectedDb; ?></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td class="right">
						<input type="submit" name="action" value="Preview" />&nbsp;
						<input type="submit" name="action" value="Add" />
					</td>
				</tr>
			</table>
		</form>
	</li>
</ul>

<?php
$this->includeTemplate('changelog_view.php'); ?>
