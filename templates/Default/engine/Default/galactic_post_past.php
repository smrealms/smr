Select Game:&nbsp;
<form class="standard" id="SelectGameForm" method="POST" action="">
	<select name="game_id" class="InputFields" onchange="this.form.submit()"><?php
		foreach($PublishedGames as $Game) {
			$id = $Game['game_id'];
			$name = $Game['game_name'];
			$selected = ($SelectedGame == $id ? 'selected="selected"' : '');
			echo "<option value='$id' $selected>$name ($id)</option>";
		} ?>
	</select>
</form><br />

<?php
if (empty($PastEditions)) { ?>
	<p>There are no Galactic Post editions for this game!</p><?php
} else { ?>
	<p>Choose a Galactic Post edition to view:</p>

	<ul>
	<?php
		foreach ($PastEditions as $edition) { ?>
			<li><a href="<?php echo $edition['href']; ?>"><?php echo date('Y/m/d', $edition['online_since']) . ' - ' . $edition['title']; ?></a></li><?php
		} ?>
	</ul><?php
} ?>
