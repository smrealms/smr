<div class="center">
	Welcome to the Hall of Fame, <?php echo $ThisAccount->getHofName(); ?>!<br />
	The Hall of Fame is a comprehensive list of player accomplishments.
	Here you can view how players rank in many different aspects of the game.<?php
	if (isset($PersonalHofHREF)) { ?>
		<a href="<?php echo $PersonalHofHREF; ?>">You can also view your Personal Hall of Fame here.</a><?php
	} ?>
	<br /><br />
	<?php echo $Breadcrumb; ?>
</div>

<?php
if (isset($Categories)) { ?>
	<table class="standard center" width="75%">
		<tr>
			<th>Category</th>
			<th width="70%">Subcategory</th>
		</tr><?php
		foreach ($Categories as $Category) { ?>
			<tr>
				<td><?php echo $Category['link']; ?></td>
				<td valign="middle"><?php echo $Category['subcategories']; ?></td>
			</tr><?php
		} ?>
	</table><?php
} else { ?>
	<table class="standard center">
		<tr>
			<th>Rank</th>
			<th>Player</th>
			<th>Total</th>
		</tr><?php
		foreach ($Rows as $Row) {
			echo $Row;
		} ?>
	</table><?php
} ?>
