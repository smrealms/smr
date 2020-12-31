<table width="50%" class="standard center">
	<tr>
		<th width="31%">Race</th>
		<th width="23%">Political Relations</th>
		<th width="23%">Personal Relations</th>
		<th width="23%">Total</th>
	</tr><?php
	foreach (array_keys($PoliticalRelations) as $Race) { ?>
		<tr>
			<td><?php echo $Race; ?></td>
			<td><?php echo get_colored_text($PoliticalRelations[$Race]); ?></td>
			<td><?php echo get_colored_text($PersonalRelations[$Race]); ?></td>
			<td><?php echo get_colored_text($PoliticalRelations[$Race] + $PersonalRelations[$Race]); ?></td>
		</tr><?php
	} ?>
</table>
