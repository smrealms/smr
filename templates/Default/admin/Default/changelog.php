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
					<td colspan="2"><input type="text" name="change_title" id="InputFields" style="width:400px;"></td>
				</tr>
				<tr>
					<td><small>Message:</small></td>
					<td><small>Affected Database:</small></td>
				</tr>
				<tr>
					<td><textarea spellcheck="true" name="change_message" id="InputFields" style="width:400px;height:50px;"></textarea></td>
					<td><textarea spellcheck="true" name="affected_db" id="InputFields" style="width:200px;height:50px;"></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td class="right">
						<input type="submit" name="action" value="Add" />
					</td>
				</tr>
			</table>
		</form>
	</li>
</ul>

<?php
$this->includeTemplate('changelog_view.php'); ?>
