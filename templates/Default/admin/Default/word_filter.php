<h2>Filtered Words</h2><br />

<?php
if (empty($FilteredWords)) { ?>
	No words are currently being filtered.<br /><br /><?php
} else { ?>
	<form method="POST" action="<?php echo $DelHREF; ?>">
		<table class="standard">
			<tr>
				<th>Delete</th>
				<th>Original Word</th>
				<th>Replacement</th>
			</tr><?php
			foreach ($FilteredWords as $Word) { ?>
				<tr>
					<td class="center shrink"><input type="checkbox" name="word_ids[]" value="<?php echo $Word['word_id']; ?>"></td>
					<td><?php echo $Word['word_value']; ?></td>
					<td><?php echo $Word['word_replacement']; ?></td>
				</tr><?php
			} ?>
		</table><br />
		<input type="submit" name="action" value="Remove Selected" />
	</form><br /><?php
} ?>

<h2>Add Word To Filter</h2><br />
<form method="POST" action="<?php echo $AddHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Word:&nbsp;</td>
			<td class="mb"><input type="text" name="Word" size="30"></td>
		</tr>
		<tr>
			<td class="top">Replacement:&nbsp;</td>
			<td class="mb"><input type="text" name="WordReplacement" size="30"></td>
		</tr>
	</table><br />
	<input type="submit" name="action" value="Add" />
</form>
<br />

<?php
if (isset($Message)) {
	echo $Message;
}
