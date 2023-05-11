<?php declare(strict_types=1);

/**
 * @var int $SelectedGame
 * @var string $SelectGameHREF
 * @var array<array{game_name: string, game_id: int}> $PublishedGames
 * @var array<array{title: string, online_since: int, href: string}> $PastEditions
 */

?>
Select Game:&nbsp;
<form class="standard" id="SelectGameForm" method="POST" action="<?php echo $SelectGameHREF; ?>">
	<select name="selected_game_id" onchange="this.form.submit()"><?php
		foreach ($PublishedGames as $Game) {
			$id = $Game['game_id'];
			$name = $Game['game_name'];
			$selected = ($SelectedGame === $id ? 'selected="selected"' : '');
			echo "<option value='$id' $selected>$name ($id)</option>";
		} ?>
	</select>
</form><br />

<?php
if (count($PastEditions) === 0) { ?>
	<p>There are no Galactic Post editions for this game!</p><?php
} else { ?>
	<p>Choose a Galactic Post edition to view:</p>

	<ul>
	<?php
		foreach ($PastEditions as $edition) { ?>
			<li><a href="<?php echo $edition['href']; ?>"><?php echo date('Y/m/d', $edition['online_since']) . ' - ' . $edition['title']; ?></a></li><?php
		} ?>
	</ul><?php
}
